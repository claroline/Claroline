<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Component\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Category;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\ClacoFormBundle\Manager\ExportManager;
use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ClacoFormResource extends ResourceComponent implements DownloadableResourceInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly ClacoFormManager $clacoFormManager,
        private readonly ExportManager $exportManager,
        private readonly TempFileManager $tempManager
    ) {
    }

    public static function getName(): string
    {
        return 'claroline_claco_form';
    }

    /** @param ClacoForm $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        return [
            'resource' => $this->serializer->serialize($resource),
            'categories' => array_map(function (Category $category) {
                return $this->serializer->serialize($category);
            }, $resource->getCategories()),

            'myEntriesCount' => $this->clacoFormManager->countUserEntries($resource, $this->tokenStorage->getToken()?->getUser()),
        ];
    }

    public function create(AbstractResource $resource, array $data): void
    {
        $resource->getResourceNode()->setDownloadable(true);
    }

    /** @param ClacoForm $resource */
    public function download(AbstractResource $resource, FileBag $fileBag): void
    {
        $entries = $this->clacoFormManager->getAllEntries($resource);
        if (empty($entries)) {
            return;
        }

        $exportedFile = $this->tempManager->generate();
        $archive = new \ZipArchive();
        $archive->open($exportedFile, \ZipArchive::CREATE);

        foreach ($entries as $entry) {
            if ($this->authorization->isGranted('OPEN', $entry)) {
                $archive->addFromString(
                    TextNormalizer::toKey($entry->getTitle()).'.pdf',
                    $this->exportManager->generatePdfForEntry($entry, $this->tokenStorage->getToken()?->getUser())
                );
            }
        }

        $fileBag->add($resource->getName().'.zip', $exportedFile);
    }

    /** @param ClacoForm $resource */
    public function update(AbstractResource $resource, array $data, array $previousData): ?array
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

            // removes categories that no longer exist
            $currentCategories = $resource->getCategories();
            foreach ($currentCategories as $currentCategory) {
                if (!in_array($currentCategory->getUuid(), $ids)) {
                    $this->crud->delete($currentCategory);
                    $resource->removeCategory($currentCategory);
                }
            }
        }

        $this->om->endFlushSuite();

        $categories = $resource->getCategories();

        return [
            'resource' => $this->serializer->serialize($resource),
            'categories' => array_map(function (Category $category) {
                return $this->serializer->serialize($category);
            }, $categories),
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

    /** @param ClacoForm $resource */
    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        $categories = $resource->getCategories();
        $entries = $this->clacoFormManager->getAllEntries($resource);

        return [
            'categories' => array_map(function (Category $category) {
                return $this->serializer->serialize($category);
            }, $categories),
            'entries' => array_map(function (Entry $entry) {
                return $this->serializer->serialize($entry);
            }, $entries),
        ];
    }

    /** @param ClacoForm $resource */
    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void
    {
        // We will replace UUIDs in the string version of the data,
        // it will be easier to fix relationships this way than creating a mapping.
        // This may have a huge performance impact because we need to decode the string multiple times.
        $rawData = json_encode($data);
        if (!empty($data['resource']) && !empty($data['resource']['fields'])) {
            foreach ($data['resource']['fields'] as $fieldData) {
                $newField = new Field();
                $newField->setClacoForm($resource);
                $resource->addField($newField);

                // No Crud here. This is managed by the ClacoFormSerializer in the app
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
