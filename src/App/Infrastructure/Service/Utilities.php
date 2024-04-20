<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\ValueObject\DateTime;

use function array_filter;
use function explode;
use function implode;
use function in_array;
use function mb_substr;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function strrpos;
use function strtotime;
use function substr;
use function trim;

use const ARRAY_FILTER_USE_BOTH;

class Utilities
{
    /**
     * Returns an array of IDs from Planeta's [ID][ID] fields
     *
     * @return string[]
     */
    public static function stringEntreCorchetesToArray(string $stringConCorchetes): array
    {
        if ((trim($stringConCorchetes) === '') || ($stringConCorchetes === '0')) {
            return [];
        }

        $stringSeparadoPorComas = str_replace(['[]', '] ['], ['', ']['], $stringConCorchetes);

        $p = strpos($stringSeparadoPorComas, '['); //primer '['

        if ($p !== false) {
            //treiem tot el que hi ha abans del primer '['
            $l                      = strlen($stringSeparadoPorComas); //llarg de la cadena
            $stringSeparadoPorComas = substr($stringSeparadoPorComas, $p + 1, $l - ($p - 1));
        }

        $u = strrpos($stringSeparadoPorComas, ']'); //últim ']'

        if ($u !== false) {
            //treiem tot el que hi ha després del últim ']'
            $l                      = strlen($stringSeparadoPorComas); //llarg de la cadena
            $stringSeparadoPorComas = substr($stringSeparadoPorComas, 0, $l - ($l - $u));
        }

        $stringSeparadoPorComas = str_replace('][', ',', $stringSeparadoPorComas);

        $res = explode(',', $stringSeparadoPorComas);

        if (! $res) {
            // Something happended parsing the string
            return [];
        }

        return $res;
    }

    public static function quitarPalabrasCortasTextoBuscador(string $texto): string
    {
        $palabrasExtrasAExcluir = ['que', 'los', 'las', 'del', 'por', 'una', 'uno', 'les', 'els', 'dels'];

        if (strlen($texto) < 3) {
            return $texto;
        }

        $textoSinPalabrasCortas = self::quitarPalabrasCortasTexto($texto);

        return self::quitarPalabrasTexto($textoSinPalabrasCortas, $palabrasExtrasAExcluir);
    }

    public static function quitarPalabrasCortasTexto(string $texto, int $minLength = 3): string
    {
        $palabras = explode(' ', $texto);

        $palabrasFiltradas = array_filter(
            $palabras,
            static function ($palabra, $key) use ($minLength) {
                if ($key !== 0) {
                    return true;
                }

                return strlen($palabra) >= $minLength;
            },
            ARRAY_FILTER_USE_BOTH
        );

        return implode(' ', $palabrasFiltradas);
    }

    /**
     * @param array<string> $palabrasAExcluir
     */
    public static function quitarPalabrasTexto(string $texto, array $palabrasAExcluir): string
    {
        $palabras = explode(' ', $texto);

        $palabrasFiltradas = array_filter($palabras, static function ($palabra) use ($palabrasAExcluir) {
            return ! in_array($palabra, $palabrasAExcluir);
        });

        return implode(' ', $palabrasFiltradas);
    }

    public static function recortarPalabras(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return sprintf(
            '%s [...]',
            mb_substr(
                $text,
                0,
                $length
            )
        );
    }

    public static function milisecondsBetweenTwoDateTime(
        DateTime $firstDate,
        DateTime $secondDate
    ): int {
        return (
            strtotime($firstDate->format('Y-m-d H:i:s')) -
            strtotime($secondDate->format('Y-m-d H:i:s'))
        ) * 1000;
    }
}
