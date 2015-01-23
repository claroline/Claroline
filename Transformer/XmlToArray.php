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

    private function xmlToArray($node, &$parent = null)
    {
        $namespaces = $node->getNameSpaces(true);

        $array = [];
        $value = trim((string) $node);
        if ($value) {
            $array['value'] = $value;
        }

        foreach ($namespaces as $namespaceKey => $namespace) {
            foreach ($node->children($namespace) as $namespacedChildrenKey => $namespacedchildren) {
                $this->xmlToArray($namespacedchildren, $array[$namespacedChildrenKey]);
            }
            foreach ($node->attributes($namespace) as $namespacedAttributeKey => $namespacedAttribute) {
                $array['attributes'][$namespacedAttributeKey] = (string) $namespacedAttribute;
            }
        }
        foreach ($node->children() as $childrenKey => $children) {
            $this->xmlToArray($children, $array[$childrenKey]);
        }
        foreach ($node->attributes() as $attributeKey => $attribute) {
            $array['attributes'][$attributeKey] = "$attribute";
        }

        $parent = $array;
        return $parent;
    }
}
