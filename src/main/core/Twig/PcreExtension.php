<?php

/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 11/18/15
 */

namespace Claroline\CoreBundle\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class PcreTwigExtension
 * Preg filters for twig, for handling regular expressions.
 * Filters includes: preg_filter, preg_grep, preg_match, preg_quote, preg_replace, preg_split.
 */
class PcreExtension extends AbstractExtension
{
    protected $env;

    public function getName()
    {
        return 'twig_pcre_filters';
    }

    public function getFilters()
    {
        return [
            'preg_replace' => new TwigFilter('preg_replace', [$this, 'replace']),
        ];
    }

    public function initRuntime(Environment $env)
    {
        $this->env = $env;
    }

    /**
     * Perform a regular expression search and replace.
     *
     * @param string $subject
     * @param string $pattern
     * @param string $replacement
     * @param int    $limit
     *
     * @return string
     */
    public function replace($subject, $pattern, $replacement = '', $limit = -1)
    {
        if (isset($subject)) {
            return preg_replace($pattern, $replacement, $subject, $limit);
        }

        return '';
    }
}
