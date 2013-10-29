<?php

namespace Claroline\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class JavascriptSafeTransformer implements  DataTransformerInterface
{
    private $document;
    private $blacklistedAttributes = array(
        'onload', 'onunload', 'onclick', 'ondblclick', 'onmousedown',
        'onmouseup', 'onmouseover', 'onmousemove', 'onmouseout',
        'onfocus', 'onblur', 'onkeypress', 'onkeydown', 'onkeyup',
        'onsubmit', 'onreset', 'onselect', 'onchange'
    );

    public function __construct()
    {
        $this->document = new \DOMDocument();
    }

    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        $this->document->loadHTML($value);
        $nodes = $this->document->getElementsByTagName('*');

        foreach ($nodes as $node) {
            if (strtolower($node->nodeName) === 'script') {
                $node->parentNode->removeChild($node);

                continue;
            }

            foreach ($this->blacklistedAttributes as $attribute) {
                if ($node->hasAttribute($attribute)) {
                    $node->removeAttribute($attribute);
                }
            }
        }

        return $this->document->saveHTML();
    }
}
