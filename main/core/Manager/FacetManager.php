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
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\Facet\FieldFacetRole;
use Claroline\CoreBundle\Entity\Facet\GeneralFacetPreference;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Service("claroline.manager.facet_manager")
 */
class FacetManager
{
    private $om;
    private $translator;
    private $tokenStorage;
    private $authorization;
    private $panelRepo;
    private $fieldRepo;

    /**
     * @InjectParams({
     *     "om"              = @Inject("claroline.persistence.object_manager"),
     *     "translator"      = @Inject("translator"),
     *     "authorization"   = @Inject("security.authorization_checker"),
     *     "tokenStorage"    = @Inject("security.token_storage"),
     * })
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        TranslatorInterface $translator
    ) {
        $this->om = $om;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->panelRepo = $om->getRepository('ClarolineCoreBundle:Facet\PanelFacet');
        $this->fieldRepo = $om->getRepository('ClarolineCoreBundle:Facet\FieldFacet');
    }

    /**
     * Creates a new facet.
     *
     * @param $name
     */
    public function createFacet($name, $forceCreationForm = false)
    {
        $this->om->startFlushSuite();
        $facet = new Facet();
        $facet->setName($name);
        $facet->setForceCreationForm($forceCreationForm);
        $facet->setPosition($this->om->count('Claroline\CoreBundle\Entity\Facet\Facet'));
        $this->initFacetPermissions($facet);
        $this->om->persist($facet);
        $this->om->endFlushSuite();

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

    public function editFacet(Facet $facet, $name, $forceCreationForm = false)
    {
        $facet->setName($name);
        $facet->setForceCreationForm($forceCreationForm);
        $this->om->persist($facet);
        $this->om->flush();

        return $facet;
    }

    /**
     * Fixes gaps beteween facet orders.
     */
    public function reorderFacets()
    {
        $facets = $this->getFacets();
        $order = 0;

        foreach ($facets as $facet) {
            $facet->setPosition($order);
            ++$order;
            $this->om->persist($facet);
        }

        $this->om->flush();
    }

    /**
     * Fixes gaps beteween fields orders.
     */
    public function reorderFields(PanelFacet $panelFacet)
    {
        $fields = $panelFacet->getFieldsFacet();
        $order = 0;

        foreach ($fields as $field) {
            $field->setPosition($order);
            ++$order;
            $this->om->persist($field);
        }

        $this->om->flush();
    }

    /**
     * Creates a new field for a facet.
     *
     * @param PanelFacet $facet
     * @param string     $name
     * @param int        $type
     */
    public function addField(PanelFacet $panelFacet, $name, $type)
    {
        $this->om->startFlushSuite();
        $fieldFacet = new FieldFacet();
        $fieldFacet->setPanelFacet($panelFacet);
        $fieldFacet->setName($name);
        $fieldFacet->setType($type);
        $fieldFacet->setPosition($this->om->count('Claroline\CoreBundle\Entity\Facet\FieldFacet'));
        $this->initFieldPermissions($fieldFacet);
        $this->om->persist($fieldFacet);
        $this->om->endFlushSuite();

        return $fieldFacet;
    }

    /**
     * Adds a panel in a facet.
     *
     * @param Facet  $facet
     * @param string $name
     *
     * @return PanelFacet
     */
    public function addPanel(Facet $facet, $name, $collapse = false)
    {
        $panelFacet = new PanelFacet();
        $panelFacet->setName($name);
        $panelFacet->setFacet($facet);
        $panelFacet->setIsDefaultCollapsed($collapse);
        $panelFacet->setPosition($this->om->count('Claroline\CoreBundle\Entity\Facet\PanelFacet'));
        $this->om->persist($panelFacet);
        $this->om->flush();

        return $panelFacet;
    }

    /**
     * Persists and flush a panel.
     *
     * @param FacetPanel $panel
     *
     * @return FacetPanel
     */
    public function editPanel(PanelFacet $panel)
    {
        $this->om->persist($panel);
        $this->om->flush();

        return $panel;
    }

    /**
     * Removes a panel.
     *
     * @param FacetPanel $panel
     */
    public function removePanel(PanelFacet $panel)
    {
        //some reordering have to happen here...
        $panels = $this->panelRepo->findPanelsAfter($panel);

        foreach ($panels as $afterPanel) {
            $afterPanel->setPosition($afterPanel->getPosition() - 1);
            $this->om->persist($afterPanel);
        }

        $this->om->remove($panel);
        $this->om->flush();
        //reorder the fields for the still standing panels
        $panels = $this->panelRepo->findAll();

        foreach ($panels as $panel) {
            $this->reorderFields($panel);
        }
    }

    /**
     * Removes a field from a facet.
     *
     * @param FieldFacet $field
     */
    public function removeField(FieldFacet $field)
    {
        $panel = $field->getPanelFacet();
        $this->om->remove($field);
        $this->om->flush();
        $this->reorderFields($panel);
    }

    /**
     * Set the value of a field for a user.
     *
     * @param User       $user
     * @param FieldFacet $field
     * @param mixed      $value
     *
     * @throws \Exception
     */
    public function setFieldValue(User $user, FieldFacet $field, $value, $force = false)
    {
        if (!$this->authorization->isGranted('edit', $field) && !$force) {
            throw new AccessDeniedException();
        }

        $fieldFacetValue = $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacetValue')
            ->findOneBy(array('user' => $user, 'fieldFacet' => $field));

        if ($fieldFacetValue === null) {
            $fieldFacetValue = new FieldFacetValue();
            $fieldFacetValue->setUser($user);
            $fieldFacetValue->setFieldFacet($field);
        }

        switch ($field->getType()) {
            case FieldFacet::DATE_TYPE:
                $date = is_string($value) ?
                    \DateTime::createFromFormat(
                        $this->translator->trans('date_form_datepicker_php', array(), 'platform'),
                        $value
                    ) :
                    $value;
                $fieldFacetValue->setDateValue($date);
                break;
            case FieldFacet::FLOAT_TYPE:
                $fieldFacetValue->setFloatValue($value);
                break;
            case FieldFacet::STRING_TYPE:
                $fieldFacetValue->setStringValue($value);
                break;
            default:
                throw new \Exception('The facet type '.$field->getType().' is unknown.');
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
     * Moves a facet up.
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
     * Moves a facet down.
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
     * Order the fields of a panel according to the $ids order.
     *
     * @param array      $ids
     * @param PanelFacet $facet
     */
    public function orderFields(array $ids, PanelFacet $panel)
    {
        $fields = $panel->getFieldsFacet();

        foreach ($fields as $field) {
            foreach ($ids as $key => $id) {
                if ($id === $field->getId()) {
                    $field->setPosition($key);
                    $this->om->persist($field);
                }
            }
        }

        $this->om->flush();
    }

    /**
     * Order the panels of a facet according to the $ids order.
     *
     * @param array      $ids
     * @param PanelFacet $facet
     */
    public function orderPanels(array $ids, Facet $facet)
    {
        $panels = $facet->getPanelFacets();

        foreach ($panels as $panel) {
            foreach ($ids as $key => $id) {
                if ($id === $panel->getId()) {
                    $panel->setPosition($key);
                    $this->om->persist($panel);
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
     * Get the ordered facet list.
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
        $roles = $field->getPanelFacet()->getFacet()->getRoles();

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
     * @param array      $roles
     * @param $property (canOpen | canEdit)
     */
    public function setFieldBoolProperty(FieldFacet $fieldFacet, array $roles, $property)
    {

        //find each fields sharing the same role as $fieldFacet
        $fieldFacetsRole = $fieldFacet->getFieldFacetsRole();

        //get the correct setter
        $setterFunc = 'set'.ucfirst($property);

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

    public function getFieldFacets()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacet')->findAll();
    }

    public function getVisibleFieldFacets()
    {
        $token = $this->tokenStorage->getToken();
        $tokenRoles = $token->getRoles();
        $roles = array();

        foreach ($tokenRoles as $tokenRole) {
            $roles[] = $tokenRole->getRole();
        }

        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacetRole')->findByRoles($roles);
    }

    public function getVisibleFacets($max = null)
    {
        $token = $this->tokenStorage->getToken();
        $data = [];
        $entities = $this->om->getRepository('ClarolineCoreBundle:Facet\Facet')->findVisibleFacets($token, $max);

        foreach ($entities as $entity) {
            $data[] = array(
                'id' => $entity->getId(),
                'canOpen' => true,
                'name' => $entity->getName(),
                'position' => $entity->getPosition(),
                'panels' => $entity->getPanelFacets(),
            );
        }

        return $data;
    }

    public function getDisplayedValue(FieldFacetValue $ffv)
    {
        switch ($ffv->getFieldFacet()->getType()) {
            case FieldFacet::FLOAT_TYPE: return $ffv->getFloatValue();
            case FieldFacet::DATE_TYPE:
                return $ffv->getDateValue()->format($this->translator->trans('date_form_datepicker_php', array(), 'platform'));
            case FieldFacet::STRING_TYPE: return $ffv->getStringValue();
            default: return 'error';
        }
    }

    public function setProfilePreference(
        $baseData,
        $mail,
        $phone,
        $sendMail,
        $sendMessage,
        Role $role
    ) {
        $profilePref = $this->om->getRepository('ClarolineCoreBundle:Facet\GeneralFacetPreference')
            ->findOneByRole($role);

        $profilePref = $profilePref === null ? new GeneralFacetPreference() : $profilePref;
        $profilePref->setBaseData($baseData);
        $profilePref->setMail($mail);
        $profilePref->setPhone($phone);
        $profilePref->setSendMail($sendMail);
        $profilePref->setSendMessage($sendMessage);
        $profilePref->setRole($role);

        $this->om->persist($profilePref);
        $this->om->flush();
    }

    public function getProfilePreferences()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\GeneralFacetPreference')->findAll();
    }

    /**
     * Returns each facet wich are visible in the private profile.
     *
     * @todo change the implementation
     *
     * @return Facet[]
     */
    public function getPrivateVisibleFacets()
    {
        $visibleFacets = $this->getVisibleFacets();
        $visibleByOwner = $this->om->getRepository('ClarolineCoreBundle:Facet\Facet')
            ->findBy(array('isVisibleByOwner' => true));
        $private = [];

        foreach ($visibleByOwner as $visible) {
            $private[$visible->getPosition()] = array(
                'position' => $visible->getPosition(),
                'id' => $visible->getId(),
                'name' => $visible->getName(),
                'canOpen' => true,
                'panels' => $visible->getPanelFacets(),
            );
        }

        $data = [];

        //merge both array and set canOpen to true when needed
        foreach ($private as $el) {
            $found = false;

            foreach ($visibleFacets as $facet) {
                if ($el['id'] === $facet['id']) {
                    $canOpen = $facet['canOpen'] | $el['canOpen'];
                    $data[] = array(
                        'position' => $facet['position'],
                        'id' => $facet['id'],
                        'name' => $facet['name'],
                        'canOpen' => $canOpen,
                        'panels' => $facet['panels'],
                    );
                    $found = true;
                }
            }

            if (!$found) {
                $data[$el['position']] = $el;
            }
        }

        $array = $data + $visibleFacets;
        ksort($array);

        return $array;
    }

    /**
     * Return each field visible in the private profile.
     * The fields are returned as an array wich is used in the facetPane.html.twig file.
     * The array is has the same structure as the FieldFacetRoleRepository::findByRoles() method.
     */
    public function getPrivateVisibleFields()
    {
        $fields = $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacet')->findBy(array('isVisibleByOwner' => true));

        $data = [];

        foreach ($fields as $field) {
            if ($this->authorization->isGranted('ROLE_ADMIN')) {
                $canOpen = true;
                $canEdit = true;
            } else {
                $canOpen = $field->getIsVisibleByOwner();
                $canEdit = $field->getIsEditableByOwner();
            }

            $data[] = array(
                'id' => $field->getId(),
                'canOpen' => $canOpen,
                'canEdit' => $canEdit,
                'position' => $field->getPosition(),
            );
        }

        $visibleFieldFacets = $this->getVisibleFieldFacets();

        foreach ($data as $field) {
            $found = false;

            foreach ($visibleFieldFacets as $visibleFieldFacet) {
                if ($field['id'] === $visibleFieldFacet['id']) {
                    $data[$field['position']]['canOpen'] = $field['canOpen'] | $visibleFieldFacet['canOpen'];
                    $data[$field['position']]['canEdit'] = $field['canEdit'] | $visibleFieldFacet['canEdit'];
                }
            }
        }

        return $data;
    }

    public function getVisiblePublicPreference()
    {
        $tokenRoles = $this->tokenStorage->getToken()->getRoles();
        $roles = array();

        foreach ($tokenRoles as $tokenRole) {
            $roles[] = $tokenRole->getRole();
        }

        return $this->om->getRepository('ClarolineCoreBundle:Facet\GeneralFacetPreference')
            ->getAdminPublicProfilePreferenceByRole($roles);
    }

    public function getAdminPublicPreference()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\GeneralFacetPreference')->findAll();
    }

    public function findForcedRegistrationFacet()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\Facet')
            ->findBy(array('forceCreationForm' => true));
    }
}
