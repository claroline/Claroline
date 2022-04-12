<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Listener\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Entity\Keyword;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\ExportResourceEvent;
use Claroline\CoreBundle\Event\Resource\ImportResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ClacoFormListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var SerializerProvider */
    private $serializer;
    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;
    /** @var RoleManager */
    private $roleManager;
    /** @var ClacoFormManager */
    private $clacoFormManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud,
        PlatformConfigurationHandler $platformConfigHandler,
        RoleManager $roleManager,
        ClacoFormManager $clacoFormManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->roleManager = $roleManager;
        $this->clacoFormManager = $clacoFormManager;
    }

    /**
     * Loads the ClacoForm resource.
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /** @var ClacoForm $clacoForm */
        $clacoForm = $event->getResource();
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = !$user instanceof User;
        $myEntries = $isAnon ? [] : $this->clacoFormManager->getUserEntries($clacoForm, $user);
        $canGeneratePdf = !$isAnon;
        $cascadeLevelMax = $this->platformConfigHandler->hasParameter('claco_form_cascade_select_level_max') ?
            $this->platformConfigHandler->getParameter('claco_form_cascade_select_level_max') :
            2;
        $roles = [];
        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
        $roleAnonymous = $this->roleManager->getRoleByName('ROLE_ANONYMOUS');
        $workspaceRoles = $this->roleManager->getWorkspaceRoles($clacoForm->getResourceNode()->getWorkspace());
        $roles[] = $this->serializer->serialize($roleUser, [Options::SERIALIZE_MINIMAL]);
        $roles[] = $this->serializer->serialize($roleAnonymous, [Options::SERIALIZE_MINIMAL]);

        foreach ($workspaceRoles as $workspaceRole) {
            $roles[] = $this->serializer->serialize($workspaceRole, [Options::SERIALIZE_MINIMAL]);
        }
        $myRoles = $isAnon ? [$roleAnonymous->getName()] : $user->getRoles();
        $serializedClacoForm = $this->serializer->serialize($clacoForm);
        $canEdit = $isAnon ?
            false :
            $this->authorization->isGranted('EDIT', $clacoForm->getResourceNode());

        if ($canEdit) {
            foreach ($serializedClacoForm['list']['filters'] as $key => $filter) {
                $filter['locked'] = false;
                $serializedClacoForm['list']['filters'][$key] = $filter;
            }
        }

        $event->setData([
            'clacoForm' => $serializedClacoForm,
            'canGeneratePdf' => $canGeneratePdf,
            'cascadeLevelMax' => $cascadeLevelMax,
            'myEntriesCount' => count($myEntries),
            // do not expose this and pre calculate missing user rights
            'roles' => $roles,
            'myRoles' => $myRoles,
        ]);
        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event)
    {
        /** @var ClacoForm $clacoForm */
        $clacoForm = $event->getResource();
        /** @var ClacoForm $copy */
        $copy = $event->getCopy();
        $copy = $this->clacoFormManager->copyClacoForm($clacoForm, $copy);

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    public function onExport(ExportResourceEvent $exportEvent)
    {
        /** @var ClacoForm $clacoForm */
        $clacoForm = $exportEvent->getResource();

        $exportEvent->setData([
            'categories' => array_map(function (Category $category) {
                return $this->serializer->serialize($category);
            }, $clacoForm->getCategories()),
            'keywords' => array_map(function (Keyword $keyword) {
                return $this->serializer->serialize($keyword);
            }, $clacoForm->getKeywords()),
            'entries' => array_map(function (Entry $entry) {
                return $this->serializer->serialize($entry);
            }, $this->clacoFormManager->getAllEntries($clacoForm)),
        ]);
    }

    public function onImport(ImportResourceEvent $event)
    {
        /** @var ClacoForm $clacoForm */
        $clacoForm = $event->getResource();
        $data = $event->getData();

        // We will replace UUIDs in the string version of the data,
        // it will be easier to fix relationships this way than creating a mapping.
        // This may have a huge performances impact because we need to decode the string multiple times.
        $rawData = json_encode($data);

        foreach ($data['fields'] as $fieldData) {
            $newField = new Field();
            $newField->setClacoForm($clacoForm);
            $clacoForm->addField($newField);

            // no Crud here. This is managed by the ClacoFormSerializer in the app
            $newField = $this->serializer->deserialize($fieldData, $newField, [Options::REFRESH_UUID]);

            $this->om->persist($newField);
            $this->om->persist($clacoForm);

            // replace UUIDs for Categories and Entries data
            $rawData = str_replace($fieldData['id'], $newField->getUuid(), $rawData);
        }

        // get decoded data with new UUIDs
        $data = json_decode($rawData, true);
        foreach ($data['categories'] as $categoryData) {
            $category = new Category();
            $category->setClacoForm($clacoForm);

            $this->crud->create($category, $categoryData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);

            // replace UUIDs for Entries data
            $rawData = str_replace($categoryData['id'], $category->getUuid(), $rawData);
        }

        // get decoded data with new UUIDs
        $data = json_decode($rawData, true);
        foreach ($data['keywords'] as $keywordData) {
            $keyword = new Keyword();
            $keyword->setClacoForm($clacoForm);

            $this->crud->create($keyword, $keywordData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);

            // replace UUIDs for Entries data
            $rawData = str_replace($keywordData['id'], $keyword->getUuid(), $rawData);
        }

        // get decoded data with new UUIDs
        $data = json_decode($rawData, true);
        foreach ($data['entries'] as $entryData) {
            $entry = new Entry();
            $entry->setClacoForm($clacoForm);

            $this->crud->create($entry, $entryData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);
        }
    }
}
