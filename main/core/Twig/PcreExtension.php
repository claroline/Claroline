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

/**
 * Class PcreTwigExtension
 * Preg filters for twig, for handling regular expressions.
 * Filters includes: preg_filter, preg_grep, preg_match, preg_quote, preg_replace, preg_split.
 */
class PcreExtension extends \Twig_Extension
{
    protected $env;

    public function getName()
    {
        return 'twig_pcre_filters';
    }

    public function getFilters()
    {
        return [
            'preg_replace' => new \Twig_SimpleFilter('preg_replace', [$this, '_preg_replace']),
        ];
    }

    public function initRuntime(\Twig_Environment $env)
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
    public function _preg_replace($subject, $pattern, $replacement = '', $limit = -1)
    {
        if (!isset($subject)) {
            return;
        } else {
            return preg_replace($pattern, $replacement, $subject, $limit);
        }
    }
}
