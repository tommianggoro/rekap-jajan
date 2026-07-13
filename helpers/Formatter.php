<?php

class Formatter
{
    public static function rupiah($amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public static function shortDate($datetime): string
    {
        return date('d/m/y', strtotime($datetime));
    }
}