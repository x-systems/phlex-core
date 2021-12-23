<?php

declare(strict_types=1);

namespace Phlex\Core;

final class Utils
{
    private function __construct()
    {
        // zeroton
    }

    /**
     * Generates human readable caption from camelCase model class name or field names.
     *
     * This will translate 'this\\ _isNASA_MyBigBull shit_123\Foo'
     * into 'This Is NASA My Big Bull Shit 123 Foo'
     */
    public static function getReadableCaption(string $s): string
    {
        //$s = 'this\\ _isNASA_MyBigBull shit_123\Foo';

        // first remove not allowed characters and uppercase words
        $words = ucwords(preg_replace('/[^a-z0-9]+/i', ' ', $s));

        // and then run regex to split camelcased words too
        $words = array_map('trim', preg_split('/^[^A-Z\d]+\K|[A-Z\d][^A-Z\d]+\K/', $words, -1, \PREG_SPLIT_NO_EMPTY));

        return implode(' ', $words);
    }

    public static function resolveFromRegistry(array $registry, string $searchClass)
    {
        if (array_key_exists($searchClass, $registry)) {
            return $registry[$searchClass];
        }

        foreach ($registry as $mapClass => $seed) {
            if (is_string($mapClass) && is_a($searchClass, $mapClass, true)) {
                return $seed;
            }
        }

        return $registry[0] ?? null;
    }
}
