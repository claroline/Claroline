<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class JavascriptSafeTransformer implements  DataTransformerInterface
{
    private $blacklistedAttributes = array(
        'onload', 'onunload', 'onclick', 'ondblclick', 'onmousedown',
        'onmouseup', 'onmouseover', 'onmousemove', 'onmouseout',
        'onfocus', 'onblur', 'onkeypress', 'onkeydown', 'onkeyup',
        'onsubmit', 'onreset', 'onselect', 'onchange',
    );

    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        // regex-based replacements are used here instead of dom manipulation
        // because this filter operates on html fragments (i.e. not complete documents),
        // which doesn't seem feasible using the DOMDocument class (where a whole
        // document is always built when using the loadHTML method).

        $scriptPattern = '#<[\s]*script[^>]*>.*<[\s]*/[\s]*script[^>]*>#i';
        $onEventPattern =
            '#<[\s]*[^>]+([\s]*('
            .implode('|', $this->blacklistedAttributes)
            .')[\s]*=[\s]*(["][\s]*[^>"]*["]|[\'][\s]*[^>\']*[\'])[\s]*)[^>]*>#i';

        return preg_replace_callback(
            $onEventPattern,
            function ($matches) {
                return str_replace($matches[1], '', $matches[0]);
            },
            preg_replace($scriptPattern, '', $value)
        );
    }
}
