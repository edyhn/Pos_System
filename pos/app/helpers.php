<?php

if (!function_exists('formatNumber')) {
    function formatNumber(float $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency(float $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, string $format = 'd/m/Y H:i'): string
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        return $date instanceof \Carbon\Carbon ? $date->format($format) : '-';
    }
}
