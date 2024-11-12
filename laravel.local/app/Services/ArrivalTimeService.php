<?php

namespace App\Services;

use Carbon\Carbon;

class ArrivalTimeService
{

    /**
     * @param $fromStop
     * @param $route
     * @return array|string
     */
    public function getArrivalsTime($fromStop, $route): array|string
    {
        $timeZone = 'Europe/Moscow';
        $currentTime = Carbon::now($timeZone);
        $schedule = $route->schedules->first();
        $startTime = Carbon::parse($schedule->start_time, $timeZone);
        $endTime = Carbon::parse($schedule->end_time, $timeZone);
        $frequency = $schedule->frequency;
        $nextArrivals = [];
        $arrivalTime = $startTime->copy();

        if ($currentTime->greaterThanOrEqualTo($endTime)
            || $currentTime->lessThanOrEqualTo($startTime)) {
            while (count($nextArrivals) < 3) {
                $nextArrivals[] = $arrivalTime->format('H:i');
                $arrivalTime->addMinutes($frequency);
            }
            return $nextArrivals;
        }

        if ($fromStop->stop_order != 0) {
            $interval = $fromStop->stop_order * 5;
            $arrivalTime->addMinutes($interval);
        }

        while ($arrivalTime < $currentTime) {
            $arrivalTime->addMinutes($frequency);
        }

        $nextArrivals[] = $arrivalTime->format('H:i');

        while (count($nextArrivals) < 3) {
            $arrivalTime->addMinutes($frequency);
            if ($arrivalTime->greaterThanOrEqualTo($endTime)) {
                $arrivalTime = $startTime;
                $nextArrivals[] = $arrivalTime->format('H:i');
                continue;
            }
            $nextArrivals[] = $arrivalTime->format('H:i');
        }

        return $nextArrivals;
    }

}
