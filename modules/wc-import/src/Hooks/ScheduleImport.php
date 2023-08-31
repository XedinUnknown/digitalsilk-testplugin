<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport\Hooks;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use DigitalSilk\WcImport\RefreshOrDieTrait;
use RuntimeException;
use WP_Error;

/**
 * Schedules an import action.
 */
class ScheduleImport
{
    use RefreshOrDieTrait;

    public function __invoke(int $processedThisImport = 0, ?DateTimeInterface $when = null): void
    {
        $scheduleTime = $when ?? new DateTimeImmutable("now", new DateTimeZone('UTC'));
        /** @var true|WP_Error $isScheduled */
        $isScheduled = wp_schedule_single_event(
            $scheduleTime->getTimestamp(),
            'digitalsilk_testplugin_run_import',
            [$processedThisImport],
            true
        );
        if ($isScheduled instanceof WP_Error) {
            throw new RuntimeException(sprintf(
                'Could not schedule import at "%1$s"',
                $scheduleTime->format('Y-m-d H:i:s')
            ));
        }

        $this->refreshOrDie('Import scheduled!', 'Success!');
    }
}
