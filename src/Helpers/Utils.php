<?php

namespace Epesi\Core\Helpers;

class Utils
{
    public static function bytesToHuman($bytes)
    {
        $units = [__('B'), __('KiB'), __('MiB'), __('GiB'), __('TiB'), __('PiB')];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}