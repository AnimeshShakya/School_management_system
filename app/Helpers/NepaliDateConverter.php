<?php

//  namespace App\Helpers;
use App\Helpers\NepaliCalendarConverter;

function convertAdToBs($y, $m, $d)
{
    $calendar = new NepaliCalendarConverter;
    $output = [];

    // $date = validateDate($y, $m, $d, 'ad', $calendar);

    // if (!empty($date)) {
    $output = $calendar->convertEnglishToNepali($y, $m, $d);

    return $output;
    // } else {
    //     return null;
    // }
}

function convertAdToBsWithdatenew($request_date)
{
    $calendar = new NepaliCalendarConverter;
    $output = [];
    $y = date('Y', strtotime($request_date));
    $m = date('m', strtotime($request_date));
    $d = date('d', strtotime($request_date));
    $date = validateDate($y, $m, $d, 'ad', $calendar);

    if (! empty($date)) {
        $output = $calendar->convertEnglishToNepali($y, $m, $d);

        return $output;
    } else {
        return null;
    }
}

function convertBsToAd($y, $m, $d)
{
    $calendar = new NepaliCalendarConverter;
    $output = [];

    // $date = validateDate($y, $m, $d, 'bs', $calendar);

    // if (!empty($date)) {
    $output = $calendar->convertNepaliToEnglish($y, $m, $d);
    // }

    return $output;
}

function convertBsToAdWithdatenew($request_date)
{
    $calendar = new NepaliCalendarConverter;
    $output = [];
    $y = date('Y', strtotime($request_date));
    $m = date('m', strtotime($request_date));
    $d = date('d', strtotime($request_date));
    $date = validateDate($y, $m, $d, 'bs', $calendar);

    if (! empty($date)) {
        $output = $calendar->convertNepaliToEnglish($y, $m, $d);
    }

    return $output;
}

// Add other helper functions as needed...

function validateDate($y, $m, $d, $type, $calendar)
{
    $output = [];

    if ($type === 'bs') {
        $new_date = $calendar->convertNepaliToEnglish($y, $m, $d);

        if (is_array($new_date) && ! empty($new_date)) {
            $temp_date = $calendar->convertEnglishToNepali(
                $new_date['year'],
                $new_date['month'],
                $new_date['day']
            );

            if (is_array($temp_date) && ! empty($temp_date)) {
                if (intval($y) === intval($temp_date['year'])
                    && intval($m) === intval($temp_date['month'])
                    && intval($d) === intval($temp_date['day'])) {
                    $output = $temp_date;
                }
            }
        }
    } else {
        $new_date = $calendar->convertEnglishToNepali($y, $m, $d);

        if (is_array($new_date) && ! empty($new_date)) {
            $temp_date = $calendar->convertNepaliToEnglish(
                $new_date['year'],
                $new_date['month'],
                $new_date['day']
            );

            if (is_array($temp_date) && ! empty($temp_date)) {
                if (intval($y) === intval($temp_date['year'])
                    && intval($m) === intval($temp_date['month'])
                    && intval($d) === intval($temp_date['day'])) {
                    $output = $temp_date;
                }
            }
        }
    }

    return $output;
}

function fiscalYearArray()
{
    $calendar = new NepaliCalendarConverter;
    $output = $calendar->fiscalYearArray();

    return $output;
    // $lastDate = $bs[$year - 2000][$month];
}

function Fiscalnumber($input)
{
    $calendar = new NepaliCalendarConverter;
    $output = $calendar->Fiscalnumber($input);

    return $output;
    // $lastDate = $bs[$year - 2000][$month];
}function fiscalYear($year, $month)
{
    $calendar = new NepaliCalendarConverter;
    $output = $calendar->fiscalYear($year, $month);

    return $output;
    // $lastDate = $bs[$year - 2000][$month];
}
function first_last_date_year($year, $month)
{
    $calendar = new NepaliCalendarConverter;
    $output = $calendar->first_last_date_year($year, $month);

    return $output;
    // $lastDate = $bs[$year - 2000][$month];
}
