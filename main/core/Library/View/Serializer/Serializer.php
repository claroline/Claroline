<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\View\Serializer;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Translation\TranslatorInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.library.view.serializer.serializer")
 */
class Serializer
{
    private $om;
    private $trans;
    private $container;

    /**
     * @DI\InjectParams({
     *     "trans" = @DI\Inject("translator"),
     *     "om"    = @DI\Inject("claroline.persistence.object_manager"),
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        ObjectManager $om,
        TranslatorInterface $trans,
        $container
    ) {
        $this->om = $om;
        $this->trans = $trans;
        $this->container = $container;
    }

    /**
     * This function will export properties from the class $class whose typing hint is
     * "string", "integer", "\DateTime".
     *
     * @param $class a class entity class to be exported
     * @param $exporter the exporter object to use
     * @param $extra some extra parameters depending on the exporter
     */
    public function serialize($array, $format)
    {
        $objects = [];
        //objects is passed by reference
        $class = $this->getClass($array, $objects);

        if ($class === 'Claroline\CoreBundle\Entity\User') {
            //this could be something we could make extendable.
            return $this->exportUsers($objects, $format);
        }

        return $this->defaultExport($objects, $format);
    }

    /**
     * We add the facets to the user export.
     */
    private function exportUsers($users, $format)
    {
        //set this var to false if facets somehow brake everything
        $exportFacets = true;

        $dontExport = array('password', 'description', 'salt', 'plainPassword');
        $fieldsFacets = $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacet')->findAll();
        $fields = $this->getExportableFields('Claroline\CoreBundle\Entity\User');

        foreach ($fields as $field) {
            if (in_array($field, $dontExport)) {
                unset($fields[array_search($field, $fields)]);
            }
        }

        $data = [];

        //no support for facets yet

        if ($exportFacets) {
            $fieldFacetsName = [];

            foreach ($fieldsFacets as $fieldsFacet) {
                $fieldFacetsName[] = $fieldsFacet->getName();
            }
        }

        foreach ($users as $user) {
            $data[$user->getId()] = [];
            foreach ($fields as $field) {
                $data[$user->getId()][$field] = $this->formatValue($this->getValueFromObject($user, $field));
            }

            if ($exportFacets) {
                foreach ($fieldFacetsName as $fieldFacetName) {
                    $found = false;
                    foreach ($user->getFieldsFacetValue() as $fieldFacetValue) {
                        if ($fieldFacetValue->getFieldFacet()->getName() === $fieldFacetName) {
                            $found = true;
                            $data[$user->getId()][$fieldFacetName] = $this->formatValue($fieldFacetValue->getValue());
                        }
                    }

                    if (!$found) {
                        $data[$user->getId()][$fieldFacetName] = null;
                    }
                }
            }
        }

        if ($exportFacets) {
            foreach ($fieldFacetsName as $fieldFacetName) {
                $fields[] = $fieldFacetName;
            }
        }

        $exporter = $this->container->get('claroline.library.view.serializer.'.$format);

        return $exporter->export($fields, $data);
    }

    private function getExportableFields($class)
    {
        $usableVarType = array('string', 'integer', '\DateTime', 'boolean');
        $refClass = new \ReflectionClass($class);
        $fields = [];

        foreach ($refClass->getProperties() as $refProperty) {
            if (preg_match('/@var\s+([^\s]+)/', $refProperty->getDocComment(), $matches)) {
                list(, $type) = $matches;
                if (in_array($type, $usableVarType)) {
                    $fields[] = $refProperty->getName();
                }
            }
        }

        return array_unique($fields);
    }

    private function formatValue($value)
    {
        //the only object support is DateTime for now
        if (gettype($value) === 'object') {
            return $value->format($this->trans->trans('date_range.format.with_hours', array(), 'platform'));
        }

        return $value;
    }

    private function defaultExport($class, $exporter)
    {
        throw new \Exception('No implementation yet');
    }

    private function getValueFromObject($object, $varname)
    {
        foreach ($this->findPossibleGettersForVar($varname) as $getter) {
            if (method_exists($object, $getter)) {
                return $object->$getter();
            }
        }

        return;
    }

    private function findPossibleGettersForVar($varname)
    {
        return [
            'get'.ucfirst($varname),
            'has'.ucfirst($varname),
            'is'.ucfirst($varname),
            $varname,
        ];
    }

    //recursively find an object array to export.
    //it is the only thing I'll support.
    private function getClass($array, &$objects)
    {
        foreach ($array as $el) {
            if (is_object($el)) {
                $objects = $array;

                $class = get_class($el);
            } else {
                if (is_array($el)) {
                    $class = $this->getClass($el, $objects);
                }
            }
        }

        return $class;
    }
}
