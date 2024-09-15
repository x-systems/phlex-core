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
        // first remove not allowed characters and uppercase words
        $s = ucwords(preg_replace('~[^a-z\d]+~i', ' ', $s));

        // and then run regex to split camelcased words too
        $s = array_map('trim', preg_split('~(?:^|[A-Z\d])[^A-Z\d]+\K~', $s, -1, \PREG_SPLIT_NO_EMPTY));
        $s = implode(' ', $s);

        // replace "Id" with "ID"
        $s = preg_replace('~(?<=^| )Id~', 'ID', $s);

        return $s;
    }

    public static function resolveFromRegistry(array $registry, string $searchClass)
    {
        if (array_key_exists($searchClass, $registry)) {
            return $registry[$searchClass];
        }

        foreach (class_exists($searchClass) ? array_merge(class_implements($searchClass), class_parents($searchClass)) : [] as $parentClass) {
            if (array_key_exists($parentClass, $registry)) {
                return $registry[$parentClass];
            }
        }

        return $registry[0] ?? null;
    }
}
