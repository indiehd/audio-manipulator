<?php

namespace IndieHD\AudioManipulator;

class Utility
{
    /*
     * Convert seconds to hours:minutes:seconds format.
     * @see: http://www.laughing-buddha.net/jon/php/sec2hms/
     */
    public static function sec2hms($sec, $hideHoursIfNone = true, $padValues = false, $useUnits = false)
    {
        // Holds formatted string.

        $hms = '';

        // There are 3600 seconds in an hour, so dividing the total seconds by
        // 3600 and discarding the remainder yields the number of hours.

        $hours = intval(intval($sec) / 3600);

        if ($hours > 0 || ($hours == 0 && $hideHoursIfNone !== true)) {
            // Append to $hms, with a leading 0, if the optional parameter is TRUE.

            $hms .= ($padValues)
                ? str_pad($hours, 2, '0', STR_PAD_LEFT)
                : $hours;

            if ($useUnits === true) {
                $hms .= 'h ';
            } else {
                $hms .= ':';
            }
        }

        // Dividing the total seconds by 60 will give us the number of minutes,
        // but we're interested in minutes past the hour; to calculate those, we
        // need to divide by 60 again and keep the remainder.

        $minutes = intval(($sec / 60) % 60);

        // Append to $hms, with a leading 0, if the optional parameter is TRUE.

        $hms .= ($padValues)
            ? str_pad($minutes, 2, '0', STR_PAD_LEFT)
            : $minutes;

        if ($useUnits === true) {
            $hms .= 'm ';
        } else {
            $hms .= ':';
        }

        // Seconds are simple; just divide the total seconds by 60 and keep the remainder.

        $seconds = intval($sec % 60);

        // Append to $hms, with a leading 0, if the optional parameter is TRUE.

        $hms .= ($padValues)
            ? str_pad($seconds, 2, '0', STR_PAD_LEFT)
            : $seconds;

        // Append any fractions of a second that were contained in the input.

        $secondsDecimal = substr($sec, strrpos($sec, '.'));

        if ($sec != $secondsDecimal) {
            //This line will only be executed if fractions of a second were
            //contained in the input value.

            $hms .= $secondsDecimal;
        }

        if ($useUnits === true) {
            $hms .= 's';
        }

        return $hms;
    }
}
