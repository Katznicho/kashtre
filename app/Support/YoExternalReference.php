<?php

namespace App\Support;

/**
 * Short external_reference strings for Yo Payments (Kashtre).
 * Replaces uniqid() / invoice_number + time() + uniqid() patterns that produced very long references.
 */
class YoExternalReference
{
    /**
     * Returns e.g. MM-A1B2C3D4E5 (prefix + 10 hex chars from 5 random bytes).
     */
    public static function make(string $prefix): string
    {
        $p = rtrim($prefix, '-');

        return $p.'-'.strtoupper(bin2hex(random_bytes(5)));
    }
}
