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

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Entity\FieldValue;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Event\ExportObjectEvent;
use Claroline\CoreBundle\Event\ImportObjectEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\RoleManager;
use JMS\DiExtraBundle\Annotation as DI;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service
 */
class ClacoFormListener
{
    private $clacoFormManager;
    private $om;
    private $finder;
    private $platformConfigHandler;
    private $roleManager;
    private $serializer;
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "clacoFormManager"      = @DI\Inject("claroline.manager.claco_form_manager"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "roleManager"           = @DI\Inject("claroline.manager.role_manager"),
     *     "serializer"            = @DI\Inject("claroline.api.serializer"),
     *     "tokenStorage"          = @DI\Inject("security.token_storage"),
     *     "finder"                = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param ClacoFormManager             $clacoFormManager
     * @param ObjectManager                $om
     * @param FinderProvider               $finder
     * @param PlatformConfigurationHandler $platformConfigHandler
     * @param RoleManager                  $roleManager,
     * @param SerializerProvider           $serializer
     * @param TokenStorageInterface        $tokenStorage
     */
    public function __construct(
        ClacoFormManager $clacoFormManager,
        ObjectManager $om,
        FinderProvider $finder,
        PlatformConfigurationHandler $platformConfigHandler,
        RoleManager $roleManager,
        SerializerProvider $serializer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->clacoFormManager = $clacoFormManager;
        $this->om = $om;
        $this->finder = $finder;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->roleManager = $roleManager;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Loads the ClacoForm resource.
     *
     * @DI\Observe("resource.claroline_claco_form.load")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /** @var ClacoForm $clacoForm */
        $clacoForm = $event->getResource();
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = 'anon.' === $user;
        $myEntries = $isAnon ? [] : $this->clacoFormManager->getUserEntries($clacoForm, $user);
        $canGeneratePdf = !$isAnon &&
            $this->platformConfigHandler->hasParameter('knp_pdf_binary_path') &&
            file_exists($this->platformConfigHandler->getParameter('knp_pdf_binary_path'));
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

        $event->setData([
            'clacoForm' => $this->serializer->serialize($clacoForm),
            'canGeneratePdf' => $canGeneratePdf,
            'cascadeLevelMax' => $cascadeLevelMax,
            'myEntriesCount' => count($myEntries),
            'roles' => $roles,
            'myRoles' => $myRoles,
        ]);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("resource.claroline_claco_form.copy")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $clacoForm = $event->getResource();
        $copy = $event->getCopy();
        $copy = $this->clacoFormManager->copyClacoForm($clacoForm, $copy);

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("transfer.claroline_claco_form.export")
     */
    public function onExport(ExportObjectEvent $exportEvent)
    {
        $clacoForm = $exportEvent->getObject();
        $data = $exportEvent->getData();
        $params['hiddenFilters']['clacoForm'] = $clacoForm->getId();
        $data['_data']['entries'] = $this->finder->search(Entry::class, $params)['data'];

        $exportEvent->setData($data);
    }

    /**
     * @DI\Observe("transfer.claroline_claco_form.import.before")
     */
    public function onImportBefore(ImportObjectEvent $event)
    {
        $data = $event->getData();
        $replaced = json_encode($event->getExtra());

        foreach ($data['fields'] as $field) {
            $uuid = Uuid::uuid4()->toString();
            $replaced = str_replace($field['id'], $uuid, $replaced);
        }

        $data = json_decode($replaced, true);
        $event->setExtra($data);
    }

    /**
     * @DI\Observe("transfer.claroline_claco_form.import.after")
     */
    public function onImportAfter(ImportObjectEvent $event)
    {
        $data = $event->getData();
        $clacoForm = $event->getObject();

        foreach ($data['categories'] as $dataCategory) {
            $category = new Category();
            $category = $this->serializer->deserialize($dataCategory, $category, [Options::REFRESH_UUID]);
            $category->setClacoForm($clacoForm);
            $this->om->persist($category);
        }

        foreach ($data['keywords'] as $dataKeyword) {
            $keyword = new Keyword();
            $keyword = $this->serializer->deserialize($dataKeyword, $keyword, [Options::REFRESH_UUID]);
            $keyword->setClacoForm($clacoForm);
            $this->om->persist($keyword);
        }

        $fields = [];

        foreach ($data['fields'] as $fieldData) {
            $newField = new Field();
            $newField->setClacoForm($clacoForm);
            $newField = $this->serializer->deserialize($fieldData, $newField);
            $this->om->persist($newField);
            $clacoForm->addField($newField);
            $this->om->persist($clacoForm);
            $fields[] = $newField;
        }

        foreach ($data['_data']['entries'] as $dataEntry) {
            $entry = new Entry();
            $this->serializer->deserialize($dataEntry, $entry, [Options::REFRESH_UUID]);
            $entry->setClacoForm($clacoForm);
            $this->om->persist($entry);

            foreach ($fields as $field) {
                $uuid = $field->getUuid();
                if (isset($dataEntry['values'][$uuid])) {
                    $fieldValue = new FieldValue();
                    $fieldValue->setEntry($entry);
                    $fieldValue->setField($field);

                    $fielFacetValue = new FieldFacetValue();
                    $fielFacetValue->setUser($entry->getUser());
                    $fielFacetValue->setFieldFacet($field->getFieldFacet());
                    $fielFacetValue->setValue($dataEntry['values'][$uuid]);
                    $fieldValue->setFieldFacetValue($fielFacetValue);

                    $this->om->persist($fielFacetValue);
                    $this->om->persist($fieldValue);
                }
            }
        }
    }

    /**
     * @DI\Observe("delete_claroline_claco_form")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }
}
