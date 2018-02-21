<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.manager.exporter_manager")
 */
class ExporterManager
{
    private $om;
    private $trans;

    /**
     * @DI\InjectParams({
     *     "trans" = @DI\Inject("translator"),
     *     "om"    = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        TranslatorInterface $trans
    ) {
        $this->om = $om;
        $this->trans = $trans;
    }

    /**
     * This function will export properties from the class $class whose typing hint is
     * "string", "integer", "\DateTime".
     *
     * @param $class a class entity class to be exported
     * @param $exporter the exporter object to use
     * @param $extra some extra parameters depending on the exporter
     */
    public function export($class, $exporter, array $extra = [])
    {
        if ('Claroline\CoreBundle\Entity\User' === $class) {
            return $this->exportUsers($exporter, $extra);
        }

        return $this->defaultExport($class, $exporter, $extra);
    }

    /**
     * We add the facets to the user export.
     */
    private function exportUsers($exporter, array $extra)
    {
        $dontExport = ['password', 'description', 'salt', 'plainPassword'];

        if (isset($extra['workspace'])) {
            $users = $this->om->getRepository('ClarolineCoreBundle:User')
                ->findAllWithFacetsByWorkspace($extra['workspace']);
        } else {
            $users = $this->om->getRepository('ClarolineCoreBundle:User')
                ->findAllWithFacets();
        }

        $fieldsFacets = $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacet')->findAll();
        $fields = $this->getExportableFields('Claroline\CoreBundle\Entity\User');

        foreach ($fields as $field) {
            if (in_array($field, $dontExport)) {
                unset($fields[array_search($field, $fields)]);
            }
        }

        $data = [];
        $fieldFacetsName = [];

        foreach ($fieldsFacets as $fieldsFacet) {
            $fieldFacetsName[] = $fieldsFacet->getName();
        }

        foreach ($users as $user) {
            $data[$user->getId()] = [];
            foreach ($fields as $field) {
                $data[$user->getId()][$field] = $this->formatValue($this->getValueFromObject($user, $field));
            }

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

        foreach ($fieldFacetsName as $fieldFacetName) {
            $fields[] = $fieldFacetName;
        }

        return $exporter->export($fields, $data);
    }

    private function getExportableFields($class)
    {
        $usableVarType = ['string', 'integer', '\DateTime', 'boolean'];
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
        if ('object' === gettype($value)) {
            return $value->format($this->trans->trans('date_range.format.with_hours', [], 'platform'));
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
        $getters = [];
        $getters[] = 'get'.ucfirst($varname);
        $getters[] = 'has'.ucfirst($varname);
        $getters[] = 'is'.ucfirst($varname);
        $getters[] = $varname;

        return $getters;
    }
}
