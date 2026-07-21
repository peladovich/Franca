<?php
/**
 * Single source of truth for reservation booking slots and the business
 * hours shown on the Reservations page. Keys are ISO-8601 weekday numbers
 * (1 = Monday ... 7 = Sunday), matching PHP's date('N').
 */

function reservation_slots(): array
{
    $weekdayHours = [];
    for ($h = 8; $h <= 19; $h++) {
        $weekdayHours[] = sprintf('%02d:00', $h);
    }

    $saturdayHours = [];
    for ($h = 9; $h <= 16; $h++) {
        $saturdayHours[] = sprintf('%02d:00', $h);
    }

    return [
        1 => $weekdayHours,
        2 => $weekdayHours,
        3 => $weekdayHours,
        4 => $weekdayHours,
        5 => $weekdayHours,
        6 => $saturdayHours,
        7 => [],
    ];
}

/** Allowed reservation times ('HH:MM') for a given date, or [] if closed that day. */
function reservation_slots_for_date(string $date): array
{
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return [];
    }
    $weekday = (int) date('N', $timestamp);
    return reservation_slots()[$weekday] ?? [];
}

/**
 * Formats an "HH:MM-HH:MM" business-hours range (or "closed") into a
 * locale-aware display string, e.g. "7:30 a. m.–7:30 p. m." / "7:30 AM–7:30 PM".
 */
function format_hours_range(string $range, string $locale): string
{
    if ($range === '' || strtolower($range) === 'closed') {
        return $locale === 'es' ? 'Cerrado' : 'Closed';
    }
    $parts = array_map('trim', explode('-', $range));
    if (count($parts) !== 2) {
        return $range;
    }
    [$start, $end] = $parts;
    return format_time_range_part($start, $locale) . '–' . format_time_range_part($end, $locale);
}

function format_time_range_part(string $hhmm, string $locale): string
{
    [$h, $m] = array_map('intval', explode(':', $hhmm));
    $period = $h < 12
        ? ($locale === 'es' ? 'a. m.' : 'AM')
        : ($locale === 'es' ? 'p. m.' : 'PM');
    $h12 = $h % 12;
    if ($h12 === 0) {
        $h12 = 12;
    }
    $minutePart = $m > 0 ? sprintf(':%02d', $m) : '';
    return $h12 . $minutePart . ' ' . $period;
}
