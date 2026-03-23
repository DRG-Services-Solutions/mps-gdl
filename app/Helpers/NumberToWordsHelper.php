<?php

namespace App\Helpers;

class NumberToWordsHelper
{
    private static array $units = [
        '', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO',
        'SEIS', 'SIETE', 'OCHO', 'NUEVE', 'DIEZ',
        'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE',
        'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE', 'VEINTE',
        'VEINTIUN', 'VEINTIDOS', 'VEINTITRES', 'VEINTICUATRO', 'VEINTICINCO',
        'VEINTISEIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE',
    ];

    private static array $tens = [
        '', '', '', 'TREINTA', 'CUARENTA', 'CINCUENTA',
        'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA',
    ];

    private static array $hundreds = [
        '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS',
        'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS',
    ];

    /**
     * Convertir un monto a texto en español
     * Ej: 56797.38 → "CINCUENTA Y SEIS MIL SETECIENTOS NOVENTA Y SIETE PESOS 38/100 M.N."
     */
    public static function convert(float $amount, string $currency = 'PESOS'): string
    {
        $amount = round($amount, 2);
        $intPart = (int) floor($amount);
        $decPart = (int) round(($amount - $intPart) * 100);

        if ($intPart === 0) {
            $words = 'CERO';
        } else {
            $words = self::intToWords($intPart);
        }

        $decStr = str_pad($decPart, 2, '0', STR_PAD_LEFT);

        return "{$words} {$currency} {$decStr}/100 M.N.";
    }

    private static function intToWords(int $number): string
    {
        if ($number === 0) return '';
        if ($number === 100) return 'CIEN';

        if ($number < 30) {
            return self::$units[$number];
        }

        if ($number < 100) {
            $tens = self::$tens[(int) ($number / 10)];
            $units = $number % 10;
            return $units > 0 ? "{$tens} Y " . self::$units[$units] : $tens;
        }

        if ($number < 1000) {
            $h = (int) ($number / 100);
            $remainder = $number % 100;
            $hundredWord = $number === 100 ? 'CIEN' : self::$hundreds[$h];
            return $remainder > 0
                ? "{$hundredWord} " . self::intToWords($remainder)
                : $hundredWord;
        }

        if ($number < 1000000) {
            $thousands = (int) ($number / 1000);
            $remainder = $number % 1000;

            if ($thousands === 1) {
                $thousandWord = 'MIL';
            } else {
                $thousandWord = self::intToWords($thousands) . ' MIL';
            }

            return $remainder > 0
                ? "{$thousandWord} " . self::intToWords($remainder)
                : $thousandWord;
        }

        if ($number < 1000000000) {
            $millions = (int) ($number / 1000000);
            $remainder = $number % 1000000;

            if ($millions === 1) {
                $millionWord = 'UN MILLON';
            } else {
                $millionWord = self::intToWords($millions) . ' MILLONES';
            }

            return $remainder > 0
                ? "{$millionWord} " . self::intToWords($remainder)
                : $millionWord;
        }

        return (string) $number;
    }
}
