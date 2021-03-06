<?php namespace Mreschke\Helpers;

use Carbon\Carbon;

/**
 * Date helpers.
 * @copyright 2014 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
 */
class Date
{

    /**
     * Get date format that allows milliseconds formatting.
     * A "u" format will be converted to 3 character milliseconds
     * Example udate('Y-m-d H:i:s.u')
     * @return datetime formated string
     */
    public static function date($format, $utimestamp = null)
    {
        #$utimestamp is microtime() which is in the form of "usec sec"
        #usec is microseconds (u=million) expressed in seconds (so a decimal)
        #sec is number if seconds in unix epoch
        if (is_null($utimestamp)) {
            $utimestamp = microtime();
        }
        list($usec, $epoch) = explode(" ", microtime());

        # Get microseconds (millionths) and guarantee 6 characters
        $usec = $usec * 1000000;
        $usec = str_pad($usec, 6, "0", STR_PAD_LEFT);

        # Find milliseconds (thousands) and guarantee 3 characters
        $msec = round($usec / 1000); #milliseconds (thousands)
        $msec = str_pad($msec, 3, "0", STR_PAD_LEFT);
        if ($msec == 1000) {
            $msec = 999;
        }

        $format = preg_replace('`(?<!\\\\)u`', $msec, $format);

        return date($format, $epoch);
    }

    /**
     * Split date range into chunks by $days (inclusive enddates 23:59:59)
     * @param  Carbon\Carbon $startDate
     * @param  Carbon\Carbon $endDate
     * @param  int $days = 30
     * @return array
     */
    public static function splitDays($startDate, $endDate, $days = 30)
    {
        $dates = [];
        $maxDate = $endDate; $endDate = $startDate;
        while ($endDate < $maxDate) {
            $endDate = (new Carbon($startDate))->addDays($days-1)->endOfDay();
            if ($endDate > $maxDate) $endDate = $maxDate->copy()->endOfDay();
            $dates[] = ['start' => $startDate->copy(), 'end' => $endDate->copy()];
            $startDate = $endDate->copy()->addSecond();
        }
        return $dates;
    }


}

