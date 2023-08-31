<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport\Hooks;

use DigitalSilk\DummyJson\Command\ListProductsCommandInterface;
use DigitalSilk\WcImport\ProductImporterInterface;
use Exception;
use WC_Logger_Interface;

/**
 * Runs an import.
 *
 * @psalm-type ScheduleHook = callable(int, ?DateTimeInterface)
 */
class RunImport
{
    protected bool $isDebug;
    protected ListProductsCommandInterface $listCommand;
    protected ProductImporterInterface $importer;
    protected int $batchSize;
    protected WC_Logger_Interface $logger;
    /** @var ScheduleHook */
    protected $scheduleHook;

    /**
     * @param ScheduleHook $scheduleHook Will be invoked after this batch if API reports more items.
     */
    public function __construct(
        bool $isDebug,
        ListProductsCommandInterface $listCommand,
        ProductImporterInterface $importer,
        int $batchSize,
        WC_Logger_Interface $logger,
        callable $scheduleHook
    ) {
        $this->isDebug = $isDebug;
        $this->listCommand = $listCommand;
        $this->importer = $importer;
        $this->batchSize = $batchSize;
        $this->logger = $logger;
        $this->scheduleHook = $scheduleHook;
    }

    /**
     * Imports a batch of products, and schedules another if necessary.
     *
     * Logs progress, including individual product import failure, in which case it moves to next product.
     *
     * @param int $processedCount How many already processed.
     *
     * @throws Exception If problem importing.
     */
    public function __invoke(int $processedCount)
    {
        $isDebug = $this->isDebug;
        $listCommand = $this->listCommand;
        $importer = $this->importer;
        $batchSize = $this->batchSize;
        $logger = $this->logger;
        $limit = $batchSize;
        $offset = $processedCount;

        $logger->info(sprintf('Starting import of up to %1$d products', $batchSize));

        try {
            $products = $listCommand->listProducts(null, $limit, $offset);
        } catch (Exception $e) {
            $logger->error($isDebug ? (string) $e : $e->getMessage());
            throw $e;
        }

        $i = 0; // Processed in this batch
        $successfulCount = 0; // Imported successfully
        foreach ($products as $product) {
            ++$i;
            ++$processedCount;

            try {
                $wcProduct = $importer->importProduct($product);
            } catch (Exception $e) {
                $logger->error($isDebug ? (string) $e : $e->getMessage());
                continue;
            }

            ++$successfulCount;
            $logger->notice(
                sprintf('Imported product "%1$s" with ID #%2$d', $wcProduct->get_name(), $wcProduct->get_id())
            );
        }

        // Only available after iteration
        $totalFound = $products->getFoundRowsCount();
        $logger->info(sprintf(
            'Finished batch: %1$d/%2$d successful, %3$d processed, %4$s to go',
            $successfulCount,
            $i,
            $processedCount,
            $totalFound !== null
                ? $totalFound - $processedCount
                : 'unknown amount'
        ));

        // Schedule another import, if necessary
        if ($processedCount < $totalFound) {
            $logger->info('Scheduling another import');
            ($this->scheduleHook)($processedCount);

            return;
        }

        $logger->info('Nothing more to do');

    }
}
