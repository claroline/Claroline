<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\HttpFoundation;

use Symfony\Component\HttpFoundation\Response;

class XmlResponse extends Response
{
    protected $data;

    public function __construct($data = null, $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        if (null === $data) {
            $data = new \ArrayObject();
        }

        $this->setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public static function create($data = null, $status = 200, $headers = array())
    {
        return new static($data, $status, $headers);
    }

    protected function setData($data = array())
    {
        $doc = new \DOMDocument();
        $doc->encoding = 'UTF-8';
        $this->format = 'xml';
        $root = $doc->createElement('response');
        $doc->appendChild($root);
        $this->buildXml($data, $doc, $root);
        $this->data = $doc->saveXML();

        return $this->update();
    }

    protected function update()
    {
        $this->setContent($this->data);
        $this->headers->set('Content-Type', 'text/xml');
    }

    /*
     * temporary because XmlEncoder doesn't encode UTF-8 properly.
     */
    private function buildXml($data, \DOMDocument $doc, \DOMNode $parentNode = null)
    {
        if (is_array($data)) {
            foreach ($data as $elementName => $element) {
                if (is_int($elementName)) {
                    $elementName = 'item';
                }

                $newElement = $doc->createElement($elementName);

                if ($parentNode) {
                    $parentNode->appendChild($newElement);
                } else {
                    $doc->appendChild($newElement);
                }

                $this->buildXml($element, $doc, $newElement);
            }
        } else {
            $parentNode->nodeValue = $data;
        }
    }
}
