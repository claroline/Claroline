<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MigrationBundle\Generator;

use \SqlFormatter as BaseFormatter;

/**
 * Light extension of the original SqlFormatter, allowing to override its new line
 * and indententation policy for simple statements such as DDL queries.
 */
class SqlFormatter extends BaseFormatter
{
    // Constants for formatting options
    const KEYWORD_NEWLINE = 'keyword_newline';
    const KEYWORD_TOPLEVEL = 'keyword_toplevel';

    /**
     * Sets the formatting options of the SQL keywords.
     *
     * Expects an associative array where each key is an SQL keyword or expression,
     * and each value a KEYWORD_* class constant. Existing keywords are moved to the
     * specified category (i.e. toplevel or newline), while new keywords or expressions
     * are directly added to it.
     */
    public static function setKeywordFormattingOptions(array $keywords)
    {
        $keyword_map = array(
            self::KEYWORD_NEWLINE => &self::$reserved_newline,
            self::KEYWORD_TOPLEVEL => &self::$reserved_toplevel
        );

        foreach ($keywords as $keyword => $type) {
            if (!array_key_exists($type, $keyword_map)) {
                throw new \InvalidArgumentException(
                    "Unexpected type '{$type}' : type must be a KEYWORD_* class constant"
                );
            }

            foreach ($keyword_map as $keyword_type => $registered_keywords) {
                if (in_array($keyword, $registered_keywords)) {
                    if ($type === $keyword_type) {
                        continue(2); // the keyword is already of the specified type
                    }

                    // remove the keyword from its current type collection
                    $keyword_map[$keyword_type] = array_diff($keyword_map[$keyword_type], array($keyword));
                }
            }

            // add the keyword to the specified type collection
            $keyword_map[$type][] = $keyword;
        }
    }
}
