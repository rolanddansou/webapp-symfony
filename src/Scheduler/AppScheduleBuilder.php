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

        $schedule->addCommand('app:process-async-messages')
            ->description('Process async messages')
            ->everyMinute()
            ->withoutOverlapping()
        ;
    }
}
