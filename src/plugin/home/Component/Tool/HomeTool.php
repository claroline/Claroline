<?php

namespace Claroline\HomeBundle\Component\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\PublicContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Role;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Manager\HomeManager;

class HomeTool extends AbstractTool
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly HomeManager $manager
    ) {
    }

    public static function getName(): string
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

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        $homeTabs = $this->om->getRepository(HomeTab::class)->findBy([
            'contextName' => $context,
            'contextId' => $contextSubject ? $contextSubject->getContextIdentifier() : null,
        ], ['order' => 'ASC']);

        return [
            'tabs' => $this->manager->formatTabs($homeTabs/* , [SerializerInterface::SERIALIZE_MINIMAL] */),
        ];
    }

    public function configure(string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        return [];
    }

    public function export(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null): ?array
    {
        $homeTabs = $this->om->getRepository(HomeTab::class)->findBy([
            'contextName' => $context,
            'contextId' => $contextSubject ? $contextSubject->getContextIdentifier() : null,
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
            $new->setContextId($contextSubject ? $contextSubject->getContextIdentifier() : null);

            $this->crud->create($new, $tab, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, SerializerInterface::REFRESH_UUID]);

            $entities[$tab['id']] = $new;
        }

        $this->om->endFlushSuite();

        return [];
    }
}
