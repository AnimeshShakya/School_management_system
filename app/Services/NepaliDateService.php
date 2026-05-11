<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\NepaliCalendarConverter;
use Illuminate\Support\Collection;

/**
 * NepaliDateService
 *
 * Converts AD (Gregorian) dates to BS (Bikram Sambat / Nepali calendar) dates.
 * All methods are safe: invalid or null inputs return null without throwing.
 *
 * Usage:
 *   app(NepaliDateService::class)->toBSString('2026-05-11')
 *   // => '2083-01-28'
 *
 *   app(NepaliDateService::class)->addBsFields($array, ['date', 'due_date'])
 *   // adds 'date_bs' and 'due_date_bs' keys to the array
 */
class NepaliDateService
{
    private NepaliCalendarConverter $converter;

    public function __construct()
    {
        $this->converter = new NepaliCalendarConverter;
    }

    /**
     * Convert an AD date string to a BS date array.
     *
     * @param  string|null  $adDate  Any date parseable by strtotime (e.g. '2026-05-11')
     * @return array{year: int, month: int, day: int, weekday: int}|null
     */
    public function toBS(?string $adDate): ?array
    {
        if (empty($adDate)) {
            return null;
        }

        $timestamp = strtotime($adDate);
        if ($timestamp === false) {
            return null;
        }

        $y = (int) date('Y', $timestamp);
        $m = (int) date('m', $timestamp);
        $d = (int) date('d', $timestamp);

        $result = $this->converter->convertEnglishToNepali($y, $m, $d);

        if (! is_array($result) || empty($result)) {
            return null;
        }

        return $result;
    }

    /**
     * Convert an AD date string to a formatted BS date string.
     *
     * @param  string|null  $adDate  Any strtotime-parseable date
     * @param  string  $separator  Separator character between year, month, day
     * @return string|null e.g. '2083-01-28' or null on failure
     */
    public function toBSString(?string $adDate, string $separator = '-'): ?string
    {
        $bs = $this->toBS($adDate);

        if ($bs === null) {
            return null;
        }

        return sprintf(
            '%04d%s%02d%s%02d',
            $bs['year'], $separator,
            $bs['month'], $separator,
            $bs['day']
        );
    }

    /**
     * Add BS date fields to a plain associative array.
     *
     * For each key in $dateFields, a new key `{field}_bs` is added with the
     * converted BS date string, or null if conversion fails.
     *
     * @param  array  $data  The source data array
     * @param  string[]  $dateFields  Keys in $data that hold AD date strings
     */
    public function addBsFields(array $data, array $dateFields): array
    {
        foreach ($dateFields as $field) {
            $data[$field.'_bs'] = isset($data[$field])
                ? $this->toBSString((string) $data[$field])
                : null;
        }

        return $data;
    }

    /**
     * Add BS date fields to each item in a collection.
     *
     * @param  Collection  $collection  Collection of arrays or objects
     * @param  string[]  $dateFields  Fields to convert in each item
     */
    public function addBsFieldsToCollection(Collection $collection, array $dateFields): Collection
    {
        return $collection->map(function ($item) use ($dateFields) {
            $arr = is_array($item) ? $item : $item->toArray();

            return $this->addBsFields($arr, $dateFields);
        });
    }

    /**
     * Add BS date fields to each item in a plain array of arrays.
     *
     * @param  array[]  $items  Array of associative arrays
     * @param  string[]  $dateFields  Fields to convert in each item
     * @return array[]
     */
    public function addBsFieldsToItems(array $items, array $dateFields): array
    {
        return array_map(fn ($item) => $this->addBsFields($item, $dateFields), $items);
    }
}
