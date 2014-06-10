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
 * @Service("claroline.manager.facet_manager")
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

    /**
     * Creates a new facet.
     *
     * @param $name
     */
    public function createFacet($name)
    {
        $facet = new Facet();
        $facet->setName($name);
        $facet->setPosition($this->om->count('Claroline\CoreBundle\Entity\Facet\Facet'));
        $this->om->persist($facet);
        $this->om->flush();
    }

    /**
     * Removes an existing facet.
     *
     * @param Facet $facet
     */
    public function removeFacet(Facet $facet)
    {
        $this->om->remove($facet);
        $this->om->flush();
        $this->reorderFacets();
    }

    /**
     * Fixes gaps beteween facet orders
     */
    public function reorderFacets()
    {
        $facets = $this->getFacets();
        $order = 0;

        foreach ($facets as $facet) {
            $facet->setPosition($order);
            $order++;
            $this->om->persist($facet);
        }

        $this->om->flush();
    }

    /**
     * Fixes gaps beteween fields orders
     */
    public function reoderFields()
    {
        $fields = $this->getFields();
        $order = 0;

        foreach ($fields as $field) {
            $field->setPosition($order);
            $order++;
            $this->om->persist($field);
        }

        $this->om->flush();
    }

    /**
     * Creates a new field for a facet
     *
     * @param Facet   $facet
     * @param string  $name
     * @param integer $type
     */
    public function addField(Facet $facet, $name, $type)
    {
        $fieldFacet = new FieldFacet();
        $fieldFacet->setFacet($facet);
        $fieldFacet->setName($name);
        $fieldFacet->setType($type);
        $fieldFacet->setPosition($this->om->count('Claroline\CoreBundle\Entity\Facet\FieldFacet'));
        $this->om->persist($facet);
        $this->om->flush();
    }

    /**
     * Removes a field from a facet
     *
     * @param FieldFacet $field
     */
    public function removeField(FieldFacet $field)
    {
        $this->om->remove($field);
        $this->om->flush();
        $this->reorderFields();
    }

    /**
     * Set the value of a field for a user
     *
     * @param User       $user
     * @param FieldFacet $field
     * @param mixed      $value
     *
     * @throws \Exception
     */
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

    /**
     * Moves a facet up
     *
     * @param Facet $facet
     */
    public function moveFacetUp(Facet $facet)
    {
        $currentPosition = $facet->getOrder();

        /*
         * The above facet must take the current position.
         * There is no facet above 0
         */
        if ($currentPosition !== 0) {
            $prevPosition = $currentPosition - 1;
            $prevFacet = $this->om
                ->getRepository('ClarolineCoreBundle:Facet\Facet')
                ->findOneBy(array('order' => $prevPosition));
            $prevFacet->setPosition($currentPosition);
            $facet->setPosition($prevPosition);
            $this->om->persist($prevFacet);
            $this->om->persist($facet);
            $this->om->flush();
        }

    }

    /**
     * Moves a facet down
     *
     * @param Facet $facet
     */
    public function moveFacetDown(Facet $facet)
    {
        $currentPosition = $facet->setPosition();
        $maxPosition = $this->om->count('Claroline\CoreBundle\Entity\Facet\Facet');
    }

    /**
     * Moves a field up
     *
     * @param FieldFacet $fieldFacet
     */
    public function moveFieldFacetUp(FieldFacet $fieldFacet)
    {

    }

    /**
     * Moves a field down
     *
     * @param FieldFacet $fieldFacet
     */
    public function moveFieldFacetDown(FieldFacet $fieldFacet)
    {

    }

    public function getFieldsValueByUserAndFacet(User $user, Facet $facet)
    {

    }

    /**
     * Get the ordered fields of facet.
     *
     * @param Facet $facet
     */
    public function getFields(Facet $facet)
    {
        return $this->om
            ->getRepository('ClarolineCoreBundle:Facet\FieldFacet')
            ->findBy(array('facet' => $facet), array('position' => 'ASC'));
    }

    /**
     * Get the ordered facet list
     */
    public function getFacets()
    {
        return $this->om
            ->getRepository('ClarolineCoreBundle:Facet\Facet')
            ->findBy(array(), array('position' => 'ASC'));
    }
} 