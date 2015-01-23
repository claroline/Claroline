<?php

namespace Icap\PortfolioBundle\Transformer;

class XmlToArray implements TransformerInterface
{
    private $xmlNamespaces = array();

    /**
     * @param string $string
     *
     * @return array
     */
    public function transform($string)
    {
        $array = [];

        $xml = new \SimpleXMLElement($string);
        $this->xmlNamespaces = $xml->getNamespaces(true);

        $array = $this->xmlToArray($xml);

        return $array;
    }

    public function xmlToArray(\SimpleXMlElement $xml)
    {
        $array = [];

        foreach ($this->xmlNamespaces as $namespaceKey => $namespace) {
            $namespacedChildren = $xml->children($namespace);
            /** @var \SimpleXMLElement $child */
            foreach((array)$namespacedChildren as $childKey => $child) {
                $array[$childKey] = is_object($child) ? $this->xmlToArray($child) : ['value' => $child];
            }
        }

        return $array;
    }
}
