<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MigrationBundle\Twig;

use Claroline\MigrationBundle\Generator\SqlFormatter as Formatter;

/**
 * Adds a 'formatSql' function to the Twig environment.
 */
class SqlFormatterExtension extends \Twig_Extension
{
    /**
     * Constructor. Sets the formatting options of the SQL formatter.
     */
    public function __construct()
    {
        Formatter::$tab = '    ';
        Formatter::setKeywordFormattingOptions(array(
            'SELECT' => Formatter::KEYWORD_NEWLINE,
            'FROM' => Formatter::KEYWORD_NEWLINE,
            'WHERE' => Formatter::KEYWORD_NEWLINE,
            'DROP' => Formatter::KEYWORD_NEWLINE,
            'ALTER TABLE' => Formatter::KEYWORD_NEWLINE,
            'ADD' => Formatter::KEYWORD_NEWLINE,
            'REFERENCES' => Formatter::KEYWORD_NEWLINE,
            'ON DELETE CASCADE' => Formatter::KEYWORD_NEWLINE,
            'ON DELETE SET NULL' => Formatter::KEYWORD_NEWLINE
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sql_formatter_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'formatSql' => new \Twig_SimpleFunction('formatSql', array($this, 'formatSql'))
        );
    }

    /**
     * Formats an SQL query using the SqlFormatter library.
     *
     * @param string    $sql
     * @param integer   $tabOffset
     * @return string
     */
    public function formatSql($sql, $tabOffset = 3)
    {
        $tab = Formatter::$tab;
        $indent = '';

        for ($i = 0; $i < $tabOffset; ++$i) {
            $indent .= $tab;
        }

        $sql = explode("\n", Formatter::format($sql, false));
        $indentedLines = array();

        foreach ($sql as $line) {
            $indentedLines[] = $indent . str_replace('"', '\"', $line);
        }

        return implode("\n", $indentedLines);
    }
}