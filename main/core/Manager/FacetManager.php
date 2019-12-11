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
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\FieldFacetCollection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class FacetManager
{
    private $om;
    private $translator;
    private $tokenStorage;
    private $authorization;
    private $panelRepo;
    private $fieldRepo;
    private $container;

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
            case FieldFacet::NUMBER_TYPE:
                $fieldFacetValue->setFloatValue($value);
                break;
            case FieldFacet::CHOICE_TYPE:
                $options = $field->getOptions();

                if (isset($options['multiple']) && $options['multiple']) {
                    $fieldFacetValue->setArrayValue($value);
                } else {
                    $fieldFacetValue->setStringValue($value);
                }
                break;
            case FieldFacet::CASCADE_TYPE:
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
}
