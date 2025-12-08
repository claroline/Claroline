<?php

namespace Claroline\HomeBundle\Component\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\ToolComponent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\PublicContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Manager\HomeManager;

class HomeTool extends ToolComponent
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly SerializerProvider $serializer,
        private readonly HomeManager $manager
    ) {
    }

    public static function getName(): string
    {
        return 'home';
    }

    public static function getIcon(): string
    {
        return 'home';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            PublicContext::getName(),
            DesktopContext::getName(),
            AdministrationContext::getName(),
            WorkspaceContext::getName(),
        ]);
    }

    public function open(OrderedTool $tool, string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        $homeTabs = $this->om->getRepository(HomeTab::class)->findBy([
            'contextName' => $context,
            'contextId' => $contextSubject?->getContextIdentifier(),
        ], ['order' => 'ASC']);

        return [
            'tabs' => $this->manager->formatTabs($homeTabs/* , [SerializerInterface::SERIALIZE_MINIMAL] */),
        ];
    }

    public function configure(OrderedTool $tool, string $context, ?ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        $tabs = $configData['tabs'];

        // retrieve existing tabs for the context to remove deleted ones
        /** @var HomeTab[] $installedTabs */
        $installedTabs = $this->om->getRepository(HomeTab::class)->findBy([
            'contextName' => $context,
            'contextId' => $contextSubject?->getContextIdentifier(),
        ]);

        $this->om->startFlushSuite();

        $ids = [];
        $updated = [];
        foreach ($tabs as $tab) {
            $new = true;
            $existingTab = null;
            if (isset($tab['id'])) {
                foreach ($installedTabs as $installedTab) {
                    if ($installedTab->getUuid() === $tab['id']) {
                        $existingTab = $installedTab;
                        $new = false;
                        break;
                    }
                }
            }

            if (empty($existingTab)) {
                $existingTab = new HomeTab();
                $existingTab->setContextName($context);
                $existingTab->setContextId($contextSubject?->getContextIdentifier());
            }

            if ($new) {
                $this->crud->create($existingTab, $tab);
            } else {
                $this->crud->update($existingTab, $tab);
            }

            $updated[] = $existingTab;
            $ids = array_merge($ids, [$existingTab->getUuid()], array_map(function (HomeTab $child) {
                return $child->getUuid();
            }, $existingTab->getChildren()->toArray())); // will be used to determine deleted tabs
        }

        $this->cleanDatabase($installedTabs, $ids);

        $this->om->endFlushSuite();

        return [
            'tabs' => array_values(array_map(function (HomeTab $tab) {
                return $this->serializer->serialize($tab);
            }, $updated)),
        ];
    }

    public function export(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null): ?array
    {
        $homeTabs = $this->om->getRepository(HomeTab::class)->findBy([
            'contextName' => $context,
            'contextId' => $contextSubject?->getContextIdentifier(),
        ], ['order' => 'ASC']);

        return [
            'tabs' => $this->manager->formatTabs($homeTabs, [SerializerInterface::SERIALIZE_TRANSFER]),
        ];
    }

    public function import(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null, array $data = [], array $entities = []): ?array
    {
        if (empty($data['tabs'])) {
            return [];
        }

        $this->om->startFlushSuite();
        foreach ($data['tabs'] as $tab) {
            if (isset($tab['workspace'])) {
                unset($tab['workspace']);
            }

            if (!empty($tab['restrictions']) && !empty($tab['restrictions']['roles'])) {
                // replace roles ids
                foreach ($tab['restrictions']['roles'] as $i => $roleData) {
                    /** @var Role $role */
                    $role = $entities[$roleData['id']];
                    if ($role) {
                        $tab['restrictions']['roles'][$i]['id'] = $role->getUuid();
                    }
                }
            }

            $new = new HomeTab();
            $new->setContextName($context);
            $new->setContextId($contextSubject?->getContextIdentifier());

            $this->crud->create($new, $tab, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, SerializerInterface::REFRESH_UUID]);

            $entities[$tab['id']] = $new;
        }

        $this->om->endFlushSuite();

        return $entities;
    }

    private function cleanDatabase(array $installedTabs, array $ids): void
    {
        foreach ($installedTabs as $installedTab) {
            if (!in_array($installedTab->getUuid(), $ids)) {
                // the tab no longer exists we can remove it
                $this->crud->delete($installedTab);
            }
        }
    }
}
