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
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\Facet\GeneralFacetPreference;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\FieldFacetCollection;
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
     * used by clacoForm Manager.
     *
     * @deprecated
     *
     * @todo remove me
     */
    public function createField($name, $isRequired, $type, ResourceNode $resourceNode = null)
    {
        $fieldFacet = new FieldFacet();
        $fieldFacet->setLabel($name);
        $fieldFacet->setType($type);
        $fieldFacet->setRequired($isRequired);
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
     *
     * @deprecated
     *
     * @todo remove me
     *
     * Used by claco form widget config
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
     * Used by persister and Updater04000
     * Can be removed.
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
     * Set the value of a field for a user.
     *
     * @param User       $user
     * @param FieldFacet $field
     * @param mixed      $value
     *
     * Has some use at the registration/csv import.
     * Should be removed eventually
     *
     * @deprecated
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

        if (null === $fieldFacetValue) {
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

    /**
     * Used by a widget.
     *
     * @deprecated
     */
    public function getFieldValuesByUser(User $user)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacetValue')
            ->findBy(['user' => $user]);
    }

    /**
     * Used by clacoform manager.
     *
     * @deprecated
     */
    public function editField(FieldFacet $fieldFacet, $name, $isRequired, $type)
    {
        $fieldFacet->setLabel($name);
        $fieldFacet->setType($type);
        $fieldFacet->setRequired($isRequired);
        $this->om->persist($fieldFacet);
        $this->om->flush();

        return $fieldFacet;
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
     * @deprecated remove when ProfileWidget is rewritten
     */
    public function getVisibleFacets()
    {
        $token = $this->tokenStorage->getToken();
        $data = [];
        $entities = $this->om->getRepository('ClarolineCoreBundle:Facet\Facet')->findVisibleFacets($token);

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
        $email,
        $phone,
        $sendMail,
        $sendMessage,
        Role $role
    ) {
        $profilePref = $this->om->getRepository('ClarolineCoreBundle:Facet\GeneralFacetPreference')
            ->findOneByRole($role);

        $profilePref = null === $profilePref ? new GeneralFacetPreference() : $profilePref;
        $profilePref->setBaseData($baseData);
        $profilePref->setEmail($email);
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

    /**
     * Used by claco form.
     */
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

    /**
     * Used by claco form.
     */
    public function isFileType($type)
    {
        return FieldFacet::FILE_TYPE === $type;
    }

    /**
     * Used by claco form.
     *
     * @deprecated
     */
    public function getFieldFacetChoiceById($id)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacetChoice')->findOneById($id);
    }

    /**
     * Used by claco form.
     *
     * @deprecated
     */
    public function getChoiceByFieldFacetAndValueAndParent(FieldFacet $fieldFacet, $value, FieldFacetChoice $parent = null)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Facet\FieldFacetChoice')->findOneBy(
            ['fieldFacet' => $fieldFacet, 'name' => $value, 'parent' => $parent]
        );
    }
}
