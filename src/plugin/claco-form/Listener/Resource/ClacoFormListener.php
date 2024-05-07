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
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Entity\Keyword;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ClacoFormListener extends ResourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly RoleManager $roleManager,
        private readonly ClacoFormManager $clacoFormManager
    ) {
    }

    public static function getName(): string
    {
        return 'claroline_claco_form';
    }

    /** @var ClacoForm $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = !$user instanceof User;
        $myEntries = $isAnon ? [] : $this->clacoFormManager->getUserEntries($resource, $user);
        $canGeneratePdf = !$isAnon;
        $roles = [];
        $roleUser = $this->roleManager->getRoleByName(PlatformRoles::USER);
        $roleAnonymous = $this->roleManager->getRoleByName(PlatformRoles::ANONYMOUS);
        $workspaceRoles = $this->roleManager->getWorkspaceRoles($resource->getResourceNode()->getWorkspace());
        $roles[] = $this->serializer->serialize($roleUser, [Options::SERIALIZE_MINIMAL]);
        $roles[] = $this->serializer->serialize($roleAnonymous, [Options::SERIALIZE_MINIMAL]);

        foreach ($workspaceRoles as $workspaceRole) {
            $roles[] = $this->serializer->serialize($workspaceRole, [Options::SERIALIZE_MINIMAL]);
        }
        $myRoles = $isAnon ? [$roleAnonymous->getName()] : $user->getRoles();

        $categories = $resource->getCategories();
        $keywords = $resource->getKeywords();

        return [
            'resource' => $this->serializer->serialize($resource),
            'categories' => array_map(function (Category $category) {
                return $this->serializer->serialize($category);
            }, $categories),
            'keywords' => array_map(function (Keyword $keyword) {
                return $this->serializer->serialize($keyword);
            }, $keywords),

            'myEntriesCount' => count($myEntries),
            // this should use the standard right system.
            'canGeneratePdf' => $canGeneratePdf,
            'roles' => $roles,
            'myRoles' => $myRoles,
        ];
    }

    /** @var ClacoForm $resource */
    public function update(AbstractResource $resource, array $data): ?array
    {
        $this->om->startFlushSuite();

        if (isset($data['categories'])) {
            $ids = [];
            foreach ($data['categories'] as $categoryData) {
                $new = false;
                if ($categoryData['id']) {
                    $category = $resource->getCategory($categoryData['id']);
                }

                if (empty($category)) {
                    $category = new Category();
                    $new = true;
                }

                $resource->addCategory($category);
                if ($new) {
                    $this->crud->create($category, $categoryData, [Crud::NO_PERMISSIONS]);
                } else {
                    $this->crud->update($category, $categoryData, [Crud::NO_PERMISSIONS]);
                }

                $ids[] = $category->getUuid();
            }

            // removes categories which no longer exists
            $currentCategories = $resource->getCategories();
            foreach ($currentCategories as $currentCategory) {
                if (!in_array($currentCategory->getUuid(), $ids)) {
                    $this->crud->delete($currentCategory);
                    $resource->removeCategory($currentCategory);
                }
            }
        }

        if (isset($data['keywords'])) {
            $ids = [];
            foreach ($data['keywords'] as $keywordData) {
                $new = false;
                if ($keywordData['id']) {
                    $keyword = $resource->getKeyword($keywordData['id']);
                }

                if (empty($keyword)) {
                    $keyword = new Keyword();
                    $new = true;
                }

                $resource->addKeyword($keyword);
                if ($new) {
                    $this->crud->create($keyword, $keywordData, [Crud::NO_PERMISSIONS]);
                } else {
                    $this->crud->update($keyword, $keywordData, [Crud::NO_PERMISSIONS]);
                }

                $ids[] = $keyword->getUuid();
            }

            // removes categories which no longer exists
            $currentKeywords = $resource->getKeywords();
            foreach ($currentKeywords as $currentKeyword) {
                if (!in_array($currentKeyword->getUuid(), $ids)) {
                    $this->crud->delete($currentKeyword);
                    $resource->removeKeyword($currentKeyword);
                }
            }
        }

        $this->om->endFlushSuite();

        $categories = $resource->getCategories();
        $keywords = $resource->getKeywords();

        return [
            'resource' => $this->serializer->serialize($resource),
            'categories' => array_map(function (Category $category) {
                return $this->serializer->serialize($category);
            }, $categories),
            'keywords' => array_map(function (Keyword $keyword) {
                return $this->serializer->serialize($keyword);
            }, $keywords),
        ];
    }

    /**
     * @param ClacoForm $original
     * @param ClacoForm $copy
     */
    public function copy(AbstractResource $original, AbstractResource $copy): void
    {
        $this->clacoFormManager->copyClacoForm($original, $copy);
    }

    /** @var ClacoForm $resource */
    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        $categories = $resource->getCategories();
        $keywords = $resource->getKeywords();
        $entries = $this->clacoFormManager->getAllEntries($resource);

        return [
            'categories' => array_map(function (Category $category) {
                return $this->serializer->serialize($category);
            }, $categories),
            'keywords' => array_map(function (Keyword $keyword) {
                return $this->serializer->serialize($keyword);
            }, $keywords),
            'entries' => array_map(function (Entry $entry) {
                return $this->serializer->serialize($entry);
            }, $entries),
        ];
    }

    /** @var ClacoForm $resource */
    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void
    {
        // We will replace UUIDs in the string version of the data,
        // it will be easier to fix relationships this way than creating a mapping.
        // This may have a huge performances impact because we need to decode the string multiple times.
        $rawData = json_encode($data);
        if (!empty($data['resource']) && !empty($data['resource']['fields'])) {
            foreach ($data['resource']['fields'] as $fieldData) {
                $newField = new Field();
                $newField->setClacoForm($resource);
                $resource->addField($newField);

                // no Crud here. This is managed by the ClacoFormSerializer in the app
                $newField = $this->serializer->deserialize($fieldData, $newField, [Options::REFRESH_UUID]);

                $this->om->persist($newField);
                $this->om->persist($resource);

                // replace UUIDs for Categories and Entries data
                $rawData = str_replace($fieldData['id'], $newField->getUuid(), $rawData);

                // update template placeholders if any
                if (!empty($resource->getTemplate())) {
                    $template = str_replace("%field_{$fieldData['id']}%", "%field_{$newField->getUuid()}%", $resource->getTemplate());

                    $resource->setTemplate($template);
                    $this->om->persist($resource);
                }
            }
        }

        // get decoded data with new UUIDs
        $data = json_decode($rawData, true);
        if (!empty($data['categories'])) {
            foreach ($data['categories'] as $categoryData) {
                $category = new Category();
                $category->setClacoForm($resource);

                $this->crud->create($category, $categoryData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);

                // replace UUIDs for Entries data
                $rawData = str_replace($categoryData['id'], $category->getUuid(), $rawData);
            }
        }

        // get decoded data with new UUIDs
        $data = json_decode($rawData, true);
        if (!empty($data['keywords'])) {
            foreach ($data['keywords'] as $keywordData) {
                $keyword = new Keyword();
                $keyword->setClacoForm($resource);

                $this->crud->create($keyword, $keywordData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);

                // replace UUIDs for Entries data
                $rawData = str_replace($keywordData['id'], $keyword->getUuid(), $rawData);
            }
        }

        // get decoded data with new UUIDs
        $data = json_decode($rawData, true);
        if (!empty($data['entries'])) {
            foreach ($data['entries'] as $entryData) {
                $entry = new Entry();
                $entry->setClacoForm($resource);

                $this->crud->create($entry, $entryData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);

                // correctly set the entry creator
                // it's forced to the current user in EntrySubscriber.
                // This will no longer be required when import will stop using creation process
                if (!empty($entryData['user'])) {
                    /** @var User $creator */
                    $creator = $this->om->getObject($entryData['user'], Entry::class);
                    if ($creator) {
                        $entry->setUser($creator);
                        $this->om->persist($entry);
                    }
                }
            }
        }

        $this->om->flush();
    }
}
