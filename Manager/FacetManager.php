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

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Persistence\ObjectManager;

/**
 * @Service("claroline.manager.home_manager")
 */
class FacetManager
{
    /**
     * @InjectParams({
     *     "om"= @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ObjectManager $om
    )
    {
        $this->om = $om;
    }

    public function createFacet($name)
    {
        $facet = new Facet();
        $facet->setName($name);
        $this->om->persist($facet);
        $this->om->flush();
    }

    public function removeFacet(Facet $facet)
    {
        $this->om->remove($facet);
        $this->om->flush();
    }

    public function addField(Facet $facet, $name, $type)
    {
        $fieldFacet = new FieldFacet();
        $fieldFacet->setFacet($facet);
        $fieldFacet->setName($name);
        $fieldFacet->setType($type);
        $this->om->persist($facet);
        $this->om->flush();
    }

    public function removeField(FieldFacet $field)
    {
        $this->om->remove($field);
        $this->om->flush();
    }

    public function setFieldValue(User $user, FieldFacet $field, $value)
    {
        $fieldFacetValue = new FieldFacetValue();
        $fieldFacetValue->setUser($user);
        $fieldFacetValue->setFieldFacet($field);

        switch ($field->getType()) {
            case FieldFacet::DATE_TYPE:
                $fieldFacetValue->setDateValue($value);
                break;
            case FieldFacet::FLOAT_TYPE:
                $fieldFacetValue->setFloatValue($value);
                break;
            case FieldFacet::STRING_TYPE:
                $fieldFacetValue->setStringValue($value);
                break;
            default:
                throw new \Exception('The facet type ' . $field->getType() . ' is unknown.');
        }

        $this->om->persist($fieldFacetValue);
        $this->om->flush();
    }

    public function moveFacetUp(Facet $facet)
    {

    }

    public function moveFacetDown(Facet $facet)
    {

    }

    public function moveFieldFacetUp(FieldFacet $fieldFacet)
    {

    }

    public function moveFieldFacetDown(FieldFacet $fieldFacet)
    {

    }

    public function getFacetsByUser(User $user)
    {

    }

    public function getFieldsValueByUserAndFacet(User $user, Facet $facet)
    {

    }

    public function getFacets()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\Facet')->findAll();
    }
} 