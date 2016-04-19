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

use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class PcreTwigExtension
 * Preg filters for twig, for handling regular expressions.
 * Filters includes: preg_filter, preg_grep, preg_match, preg_quote, preg_replace, preg_split.
 *
 * @DI\Service
 * @DI\Tag("twig.extension")
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
        return array(
            'preg_filter' => new \Twig_Filter_Method($this, '_preg_filter'),
            'preg_grep' => new \Twig_Filter_Method($this, '_preg_grep'),
            'preg_match' => new \Twig_Filter_Method($this, '_preg_match'),
            'preg_quote' => new \Twig_Filter_Method($this, '_preg_quote'),
            'preg_replace' => new \Twig_Filter_Method($this, '_preg_replace'),
            'preg_split' => new \Twig_Filter_Method($this, '_preg_split'),
        );
    }

    public function initRuntime(\Twig_Environment $env)
    {
        $this->env = $env;
    }

    /**
     * Perform a regular expression search and replace, returning only matched subjects.
     *
     * @param string $subject
     * @param string $pattern
     * @param string $replacement
     * @param int    $limit
     *
     * @return string
     */
    public function _preg_filter($subject, $pattern, $replacement = '', $limit = -1)
    {
        if (!isset($subject)) {
            return;
        } else {
            return preg_filter($pattern, $replacement, $subject, $limit);
        }
    }

    /**
     * Perform a regular expression match and return an array of entries that match the pattern.
     *
     * @param array  $subject
     * @param string $pattern
     *
     * @return array
     */
    public function _preg_grep($subject, $pattern)
    {
        if (!isset($subject)) {
            return;
        } else {
            return preg_grep($pattern, $subject);
        }
    }

    /**
     * Perform a regular expression match.
     *
     * @param string $subject
     * @param string $pattern
     *
     * @return bool
     */
    public function _preg_match($subject, $pattern)
    {
        if (!isset($subject)) {
            return;
        } else {
            return preg_match($pattern, $subject);
        }
    }

    /**
     * Quote regular expression characters.
     *
     * @param string $subject
     * @param string $delimiter
     *
     * @return string
     */
    public function _preg_quote($subject, $delimiter)
    {
        if (!isset($subject)) {
            return;
        } else {
            return preg_quote($subject, $delimiter);
        }
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

    /**
     * Split text into an array using a regular expression.
     *
     * @param string $subject
     * @param string $pattern
     *
     * @return array
     */
    public function _preg_split($subject, $pattern)
    {
        if (!isset($subject)) {
            return;
        } else {
            return preg_split($pattern, $subject);
        }
    }
}
