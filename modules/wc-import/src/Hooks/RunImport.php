<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport\Hooks;

use DigitalSilk\DummyJson\Command\ListProductsCommandInterface;
use DigitalSilk\WcImport\ProductImporterInterface;
use Exception;
use Psr\Log\LoggerInterface;
use DateTimeInterface;

/**
 * Runs an import.
 *
 * @psalm-type ScheduleHook = callable(int, ?DateTimeInterface): void
 */
class RunImport
{
    protected bool $isDebug;
    protected ListProductsCommandInterface $listCommand;
    protected ProductImporterInterface $importer;
    protected int $batchSize;
    protected int $importLimit;
    protected LoggerInterface $logger;
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
        int $importLimit,
        LoggerInterface $logger,
        callable $scheduleHook
    ) {
        $this->isDebug = $isDebug;
        $this->listCommand = $listCommand;
        $this->importer = $importer;
        $this->batchSize = $batchSize;
        $this->importLimit = $importLimit;
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
        $importLimit = $this->importLimit;
        $offset = $processedCount;

        if (!$processedCount) {
            $logger->info(sprintf('Starting import of up to %1$s products', $importLimit ?: '∞'));
        }
        $logger->info(sprintf('Starting batch of up to %1$s products', $batchSize ?: '∞'));

        try {
            $products = $listCommand->listProducts(null, $limit, $offset);
        } catch (Exception $e) {
            $logger->error($isDebug ? (string) $e : $e->getMessage());
            throw $e;
        }

        $i = 0; // Processed in this batch
        $successfulCount = 0; // Imported successfully
        foreach ($products as $product) {

            try {
                $wcProduct = $importer->importProduct($product);
            } catch (Exception $e) {
                $logger->error($isDebug ? (string) $e : $e->getMessage());
                continue;
            }

            ++$i;
            ++$processedCount;
            ++$successfulCount;

            $logger->notice(
                sprintf('Imported product "%1$s" with ID #%2$d', $wcProduct->get_name(), $wcProduct->get_id())
            );

            if ($processedCount === $importLimit) {
                $logger->warning(sprintf('Import limit of %1$d items reached; stopping', $importLimit));
                return;
            }
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
            $hook = $this->scheduleHook;
            /** @psalm-suppress TooFewArguments */
            $hook($processedCount);

            return;
        }

        $logger->info('Nothing more to do');

    }
}
