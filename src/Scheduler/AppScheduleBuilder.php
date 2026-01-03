<?php

namespace App\Scheduler;

use App\Feature\Helper\Timezones;
use Zenstruck\ScheduleBundle\Schedule;
use Zenstruck\ScheduleBundle\Schedule\ScheduleBuilder;

class AppScheduleBuilder implements ScheduleBuilder
{
    public function buildSchedule(Schedule $schedule): void
    {
        $schedule->timezone(new \DateTimeZone(Timezones::UTC));

        /*
        $schedule->addCommand('app:consume-domain-event')
            ->description('Consume domain events from the sqlite database and dispatch them to the event bus.')
            ->everyMinute() // every minute
            ->withoutOverlapping();

        $schedule->addCommand('app:voucher:expire')
            ->description('Expire vouchers that have reached their expiration date.')
            ->cron('0 * * * *') // every hour at minute 0
            ->withoutOverlapping();
        */
    }
}
