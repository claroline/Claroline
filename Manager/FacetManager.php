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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\Facet\FieldFacetRole;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Translation\Translator;

/**
 * @Service("claroline.manager.facet_manager")
 */
class FacetManager
{
    private $om;
    private $translator;

    /**
     * @InjectParams({
     *     "om"         = @Inject("claroline.persistence.object_manager"),
     *     "translator" = @Inject("translator")
     * })
     */
    public function __construct(
        ObjectManager $om,
        Translator $translator
    )
    {
        $this->om = $om;
        $this->translator = $translator;
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
        $this->initFacetPermissions($facet);
        $this->om->persist($facet);
        $this->om->flush();

        return $facet;
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

    public function editFacet(Facet $facet, $name)
    {
        $facet->setName($name);
        $this->om->persist($facet);
        $this->om->flush();

        return $facet;
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
    public function reorderFields(Facet $facet)
    {
        $fields = $this->getFields($facet);
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
        $this->om->startFlushSuite();
        $fieldFacet = new FieldFacet();
        $fieldFacet->setFacet($facet);
        $fieldFacet->setName($name);
        $fieldFacet->setType($type);
        $fieldFacet->setPosition($this->om->count('Claroline\CoreBundle\Entity\Facet\FieldFacet'));
        $this->initFieldPermissions($fieldFacet);
        $this->om->persist($fieldFacet);
        $this->om->endFlushSuite();

        return $fieldFacet;
    }

    /**
     * Removes a field from a facet
     *
     * @param FieldFacet $field
     */
    public function removeField(FieldFacet $field)
    {
        $facet = $field->getFacet();
        $this->om->remove($field);
        $this->om->flush();
        $this->reorderFields($facet );
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
        //@todo check permissions

        $fieldFacetValue = $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacetValue')
            ->findOneBy(array('user' => $user, 'fieldFacet' => $field));

        if ($fieldFacetValue === null) {
            $fieldFacetValue = new FieldFacetValue();
            $fieldFacetValue->setUser($user);
            $fieldFacetValue->setFieldFacet($field);
        }

        switch ($field->getType()) {
            case FieldFacet::DATE_TYPE:
                $date = \DateTime::createFromFormat(
                    $this->translator->trans('date_form_datepicker_php', array(), 'platform'),
                    $value
                );

                $fieldFacetValue->setDateValue($date);
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

    public function getFieldValuesByUser(User $user)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacetValue')
            ->findBy(array('user' => $user));
    }

    /**
     * Moves a facet up
     *
     * @param Facet $facet
     */
    public function moveFacetUp(Facet $facet)
    {
        $currentPosition = $facet->getPosition();

        if ($currentPosition < $this->om->count('Claroline\CoreBundle\Entity\Facet\Facet') - 1) {
            $nextPosition = $currentPosition + 1;
            $nextFacet = $this->om
                ->getRepository('ClarolineCoreBundle:Facet\Facet')
                ->findOneBy(array('position' => $nextPosition));
            $nextFacet->setPosition($currentPosition);
            $facet->setPosition($nextPosition);
            $this->om->persist($nextFacet);
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
        $currentPosition = $facet->getPosition();

        if ($currentPosition > 0) {
            $prevPosition = $currentPosition - 1;
            $prevFacet = $this->om
                ->getRepository('ClarolineCoreBundle:Facet\Facet')
                ->findOneBy(array('position' => $prevPosition));
            $prevFacet->setPosition($currentPosition);
            $facet->setPosition($prevPosition);
            $this->om->persist($prevFacet);
            $this->om->persist($facet);
            $this->om->flush();
        }
    }

    public function editField(FieldFacet $fieldFacet, $name, $type)
    {
        $fieldFacet->setName($name);
        $fieldFacet->setType($type);
        $this->om->persist($fieldFacet);
        $this->om->flush();

        return $fieldFacet;
    }

    /**
     * Order the fields of a facet according to the $ids order.
     *
     * @param array $ids
     * @param Facet $facet
     */
    public function orderFields(array $ids, Facet $facet)
    {
        $fields = $this->getFields($facet);

        foreach ($fields as $field) {
            foreach($ids as $key => $id) {
                if ($id === $field->getId()) {
                    $field->setPosition($key);
                    $this->om->persist($field);
                }
            }
        }

        $this->om->flush();
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

    public function initFacetPermissions(Facet $facet)
    {
        $userAdminTool = $this->om
            ->getRepository('Claroline\CoreBundle\Entity\Tool\AdminTool')
            ->findOneByName('user_management');

        $roles = $this->om
            ->getRepository('ClarolineCoreBundle:Role')
            ->findByAdminTool($userAdminTool);

        $facet->setRoles($roles);
    }

    public function setFacetRoles(Facet $facet, array $roles)
    {
        $facet->setRoles($roles);
        $this->om->persist($facet);
        $this->om->flush();
    }

    public function initFieldPermissions(FieldFacet $field)
    {
        $this->om->startFlushSuite();
        $roles = $field->getFacet()->getRoles();

        foreach ($roles as $role) {
            $ffr = new FieldFacetRole();
            $ffr->setRole($role);
            $ffr->setFieldFacet($field);
            $ffr->setCanOpen(true);
            $ffr->setCanEdit(false);
            $this->om->persist($ffr);
        }

        $this->om->endFlushSuite();
    }

    /**
     * This function will allow to set on of the boolean property of FieldFacetRole
     * for a fieldFacet and an array of roles.
     *
     * @param FieldFacet $fieldFacet
     * @param array $roles
     * @param $property (canOpen | canEdit)
     */
    public function setFieldBoolProperty(FieldFacet $fieldFacet, array $roles, $property)
    {

        //find each fields sharing the same role as $fieldFacet
        $fieldFacetsRole = $fieldFacet->getFieldFacetsRole();

        //get the correct setter
        $setterFunc = 'set' . ucfirst($property);

        //initialize an array of roles wich are not linked to the field
        $unknownRoles = array();
        //initialize an array of fieldFacetRoles wich are going to have their property to true
        $fieldFacetRolesToChange = array();

        //initialize each of field facets property to false
        foreach ($fieldFacetsRole as $fieldFacetRole) {
            $fieldFacetRole->$setterFunc(false);
        }

        //find roles wich are not linked to a field
        foreach ($roles as $role) {
            $found = false;

            foreach ($fieldFacetsRole as $fieldFacetRole) {
                if ($fieldFacetRole->getRole()->getId() === $role->getId()) {
                    $found = true;
                    $fieldFacetRolesToChange[] = $fieldFacetRole;
                }
            }

            if (!$found) {
                $unknownRoles[] = $role;
            }
        }

        //create a new FieldFacetRole for each missing role
        foreach ($unknownRoles as $unknownRole) {
            $ffr = new FieldFacetRole();
            $ffr->setRole($unknownRole);
            $ffr->setFieldFacet($fieldFacet);

            //add the new fieldFacetRole to the list of retrieved fieldFacetRoles at the beginning
            $fieldFacetRolesToChange[] = $ffr;
        }

        //set the property correctly
        foreach ($fieldFacetRolesToChange as $ffr) {
            $ffr->$setterFunc(true);
            $this->om->persist($ffr);
        }

        $this->om->flush();
    }

    public function getFieldFacet($id)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacet')->find($id);
    }
}