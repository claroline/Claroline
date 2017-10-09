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

use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\Facet\GeneralFacetPreference;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\Facet\PanelFacetRole;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\FieldFacetCollection;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

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
    private $container;

    /**
     * @InjectParams({
     *     "om"              = @Inject("claroline.persistence.object_manager"),
     *     "translator"      = @Inject("translator"),
     *     "authorization"   = @Inject("security.authorization_checker"),
     *     "tokenStorage"    = @Inject("security.token_storage"),
     *     "container"       = @Inject("service_container")
     * })
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        TranslatorInterface $translator,
        $container
    ) {
        $this->om = $om;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->panelRepo = $om->getRepository('ClarolineCoreBundle:Facet\PanelFacet');
        $this->fieldRepo = $om->getRepository('ClarolineCoreBundle:Facet\FieldFacet');
        $this->fieldValueRepo = $om->getRepository('ClarolineCoreBundle:Facet\FieldFacetValue');
        $this->panelRoleRepo = $om->getRepository('ClarolineCoreBundle:Facet\PanelFacetRole');
        $this->facetRepo = $om->getRepository('ClarolineCoreBundle:Facet\Facet');
        $this->container = $container;
    }

    /**
     * Creates a new facet.
     *
     * @param $name
     */
    public function createFacet($name, $forceCreationForm = false, $isMain = false)
    {
        $this->om->startFlushSuite();
        $count = $this->facetRepo->countFacets($isMain);
        $facet = new Facet();
        $facet->setName($name);
        $facet->setIsMain($isMain);
        $facet->setForceCreationForm($forceCreationForm);
        $facet->setPosition($count);
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

    public function editFacet(Facet $facet, $name, $forceCreationForm = false, $isMain = false)
    {
        $facet->setName($name);
        $facet->setForceCreationForm($forceCreationForm);
        $facet->setIsMain($isMain);
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

    public function createField($name, $isRequired, $type, ResourceNode $resourceNode = null)
    {
        $fieldFacet = new FieldFacet();
        $fieldFacet->setName($name);
        $fieldFacet->setType($type);
        $fieldFacet->setIsRequired($isRequired);
        $fieldFacet->setResourceNode($resourceNode);
        $this->om->persist($fieldFacet);
        $this->om->flush();

        return $fieldFacet;
    }

    /**
     * Creates a new field for a facet.
     *
     * @param PanelFacet $facet
     * @param string     $name
     * @param int        $type
     */
    public function addField(PanelFacet $panelFacet, $name, $isRequired, $type)
    {
        $this->om->startFlushSuite();
        $position = $this->om->count('Claroline\CoreBundle\Entity\Facet\FieldFacet');
        $fieldFacet = $this->createField($name, $isRequired, $type);
        $fieldFacet->setPanelFacet($panelFacet);
        $fieldFacet->setPosition($position);
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
    public function addPanel(Facet $facet, $name, $collapse = false, $autoEditable = false)
    {
        $panelFacet = new PanelFacet();
        $panelFacet->setName($name);
        $panelFacet->setFacet($facet);
        $panelFacet->setIsDefaultCollapsed($collapse);
        $panelFacet->setIsEditable($autoEditable);
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
    public function editPanel(PanelFacet $panelFacet, $name, $collapse)
    {
        $panelFacet->setName($name);
        $panelFacet->setIsDefaultCollapsed($collapse);
        $this->om->persist($panelFacet);
        $this->om->flush();

        return $panelFacet;
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
        if (!$this->authorization->isGranted('edit', new FieldFacetCollection([$field], $user)) && !$force) {
            throw new AccessDeniedException();
        }

        $fieldFacetValue = $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacetValue')
            ->findOneBy(['user' => $user, 'fieldFacet' => $field]);

        if ($fieldFacetValue === null) {
            $fieldFacetValue = new FieldFacetValue();
            $fieldFacetValue->setUser($user);
            $fieldFacetValue->setFieldFacet($field);
        }

        switch ($field->getType()) {
            case FieldFacet::DATE_TYPE:
                $date = is_string($value) ?
                    new \DateTime($value) :
                    $value;
                $fieldFacetValue->setDateValue($date);
                break;
            case FieldFacet::FLOAT_TYPE:
                $fieldFacetValue->setFloatValue($value);
                break;
            case FieldFacet::CHECKBOXES_TYPE:
                $fieldFacetValue->setArrayValue($value);
                break;
            default:
                $fieldFacetValue->setStringValue($value);
        }

        $this->om->persist($fieldFacetValue);
        $this->om->flush();
    }

    public function getFieldValuesByUser(User $user)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacetValue')
            ->findBy(['user' => $user]);
    }

    /**
     * Moves a facet down.
     *
     * @param Facet $facet
     */
    public function moveFacetDown(Facet $facet)
    {
        $currentPosition = $facet->getPosition();

        if ($currentPosition < $this->facetRepo->count($facet->isMain()) - 1) {
            $nextPosition = $currentPosition + 1;
            $nextFacet = $this->om
                ->getRepository('ClarolineCoreBundle:Facet\Facet')
                ->findOneBy(['position' => $nextPosition, 'isMain' => $facet->isMain()]);
            $nextFacet->setPosition($currentPosition);
            $facet->setPosition($nextPosition);
            $this->om->persist($nextFacet);
            $this->om->persist($facet);
            $this->om->flush();
        }
    }

    /**
     * Moves a facet up.
     *
     * @param Facet $facet
     */
    public function moveFacetUp(Facet $facet)
    {
        $currentPosition = $facet->getPosition();

        if ($currentPosition > 0) {
            $prevPosition = $currentPosition - 1;
            $prevFacet = $this->om
                ->getRepository('ClarolineCoreBundle:Facet\Facet')
                ->findOneBy(['position' => $prevPosition, 'isMain' => $facet->isMain()]);
            $prevFacet->setPosition($currentPosition);
            $facet->setPosition($prevPosition);
            $this->om->persist($prevFacet);
            $this->om->persist($facet);
            $this->om->flush();
        }
    }

    public function editField(FieldFacet $fieldFacet, $name, $isRequired, $type)
    {
        $fieldFacet->setName($name);
        $fieldFacet->setType($type);
        $fieldFacet->setIsRequired($isRequired);
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
                if ((int) $id === $field->getId()) {
                    $field->setPosition($key + 1);
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
                if ((int) $id === $panel->getId()) {
                    $panel->setPosition($key + 1);
                    $this->om->persist($panel);
                }
            }
        }

        $this->om->flush();
    }

    /**
     * Get the ordered fields of facet.
     * unused.
     *
     * @param Facet $facet
     *
     * @deprecated
     */
    public function getFields(Facet $facet)
    {
        return $this->om
            ->getRepository('ClarolineCoreBundle:Facet\FieldFacet')
            ->findBy(['facet' => $facet], ['position' => 'ASC']);
    }

    /**
     * Get the ordered facet list.
     */
    public function getFacets()
    {
        return $this->om
            ->getRepository('ClarolineCoreBundle:Facet\Facet')
            ->findBy([], ['position' => 'ASC']);
    }

    public function setFacetRoles(Facet $facet, array $roles)
    {
        $facet->setRoles($roles);
        $this->om->persist($facet);
        $this->om->flush();

        return $facet;
    }

    public function setPanelFacetRole(PanelFacet $panelFacet, Role $role, $canOpen, $canEdit)
    {
        $panelFacetRole = $this->panelRoleRepo->findOneBy(['role' => $role, 'panelFacet' => $panelFacet]);

        if (!$panelFacetRole) {
            $panelFacetRole = new PanelFacetRole();
            $panelFacetRole->setRole($role);
            $panelFacetRole->setPanelFacet($panelFacet);
        }

        $panelFacetRole->setCanEdit($canEdit);
        $panelFacetRole->setCanOpen($canOpen);

        $this->om->persist($panelFacetRole);
        $this->om->flush();
    }

    public function getFieldFacet($id)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacet')->find($id);
    }

    public function getFieldFacetByName($name)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacet')->findOneByName($name);
    }

    public function getFieldFacets()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacet')->findPlatformFieldFacets();
    }

    /**
     * Used by public profile application.
     *
     * @deprecated ?
     */
    public function getVisibleFacets($max = null)
    {
        $token = $this->tokenStorage->getToken();
        $data = [];
        $entities = $this->om->getRepository('ClarolineCoreBundle:Facet\Facet')->findVisibleFacets($token, $max);

        foreach ($entities as $entity) {
            $data[] = [
                'id' => $entity->getId(),
                'canOpen' => true,
                'name' => $entity->getName(),
                'position' => $entity->getPosition(),
                'panels' => $entity->getPanelFacets(),
            ];
        }

        return $data;
    }

    public function getVisibleFieldForCurrentUserFacets()
    {
        $roles = $this->tokenStorage->getToken()->getUser()->getEntityRoles();

        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacet')->findByRoles($roles);
    }

    public function getDisplayedValue(FieldFacetValue $ffv)
    {
        switch ($ffv->getFieldFacet()->getType()) {
            case FieldFacet::FLOAT_TYPE: return $ffv->getFloatValue();
            case FieldFacet::DATE_TYPE:
                return $ffv->getDateValue()->format($this->translator->trans('date_form_datepicker_php', [], 'platform'));
            case FieldFacet::STRING_TYPE || FieldFacet::COUNTRY_TYPE || FieldFacet::SELECT_TYPE || FieldFacet::RADIO_TYPE || FieldFacet::EMAIL_TYPE: return $ffv->getStringValue();
            case FieldFacet::CHECKBOXES_TYPE: return $ffv->getArrayValue();
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

    public function getFacetsByUser(User $user)
    {
        return $this->facetRepo->findByUser($user, $this->authorization->isGranted('ROLE_ADMIN'));
    }

    public function getVisiblePublicPreference()
    {
        $tokenRoles = $this->tokenStorage->getToken()->getRoles();
        $roles = [];

        foreach ($tokenRoles as $tokenRole) {
            $roles[] = $tokenRole->getRole();
        }

        return $this->om->getRepository('ClarolineCoreBundle:Facet\GeneralFacetPreference')
            ->getAdminPublicProfilePreferenceByRole($roles);
    }

    /**
     * @deprecated
     */
    public function getAdminPublicPreference()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\GeneralFacetPreference')->findAll();
    }

    public function findForcedRegistrationFacet()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\Facet')
            ->findBy(['forceCreationForm' => true]);
    }

    public function addFacetFieldChoice($label, FieldFacet $field, FieldFacetChoice $parent = null, $position = null)
    {
        $choice = new FieldFacetChoice();
        $choice->setFieldFacet($field);
        $choice->setLabel($label);
        $position = is_null($position) ? $this->om->count('Claroline\CoreBundle\Entity\Facet\FieldFacetChoice') : $position;
        $choice->setPosition($position);
        $choice->setParent($parent);
        $this->om->persist($choice);
        $this->om->flush();

        return $choice;
    }

    /**
     * Takes an array from the API/FacetController.php.
     */
    public function editFacetFieldChoice(array $choiceDef, FieldFacet $field, $position = null)
    {
        $choice = $this->om->getRepository('Claroline\CoreBundle\Entity\Facet\FieldFacetChoice')->find($choiceDef['id']);

        if ($choice) {
            $choice->setLabel($choiceDef['label']);
            if ($position) {
                $choice->setPosition($position);
            }
            $this->om->persist($choice);
            $this->om->flush();
        } else {
            $choice = $this->addFacetFieldChoice($choiceDef['label'], $field);
        }

        return $choice;
    }

    public function removeFieldFacetChoice(FieldFacetChoice $choice)
    {
        $field = $choice->getFieldFacet();
        $this->om->remove($choice);
        //first flush is required altough bad
        $this->om->flush();
        $this->reorderChoices($field);
    }

    public function setPanelEditable(PanelFacet $panel, $bool)
    {
        $panel->setIsEditable($bool);
        $this->om->persist($panel);
        $this->om->flush();
    }

    public function reorderChoices(FieldFacet $field)
    {
        $choices = $field->getFieldFacetChoices();
        $order = 0;

        foreach ($choices as $choice) {
            $field->setPosition($order);
            ++$order;
            $this->om->persist($choice);
        }

        $this->om->flush();
    }

    public function resetFacetOrder()
    {
        $facets = $this->facetRepo->findAll();
        $facetMain = 0;
        $facetTab = 0;

        foreach ($facets as $facet) {
            if ($facet->isMain()) {
                $facet->setPosition($facetMain);
                ++$facetMain;
            } else {
                $facet->setPosition($facetTab);
                ++$facetTab;
            }
        }
    }

    public function isTypeWithChoices($type)
    {
        $withChoices = false;

        switch ($type) {
            case FieldFacet::CHECKBOXES_TYPE:
            case FieldFacet::RADIO_TYPE:
            case FieldFacet::SELECT_TYPE:
                $withChoices = true;
        }

        return $withChoices;
    }

    public function isFileType($type)
    {
        return $type === FieldFacet::FILE_TYPE;
    }

    public function getFieldFacetChoiceById($id)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacetChoice')->findOneById($id);
    }

    public function getChoiceByFieldFacetAndValueAndParent(FieldFacet $fieldFacet, $value, FieldFacetChoice $parent = null)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacetChoice')->findOneBy(
            ['fieldFacet' => $fieldFacet, 'name' => $value, 'parent' => $parent]
        );
    }
}
