<?php

namespace Claroline\AppBundle\Manager\Component;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Context\Exception\ContextNotFoundException;
use Claroline\AppBundle\Component\Tool\ToolProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ContextManager
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly ContextProvider $contextProvider,
        private readonly ToolProvider $toolProvider,
    ) {
    }

    public function create(string $contextType, ?string $contextId = null, ?array $data = []): ?ContextSubjectInterface
    {
        try {
            $contextHandler = $this->contextProvider->getContext($contextType);
        } catch (\Exception $e) {
            throw new ContextNotFoundException($contextType, $contextId, $e);
        }

        $contextSubject = null;

        // create the context subject
        if (!empty($data['data']) && $contextId) {
            /** @var ContextSubjectInterface $contextSubject */
            $contextSubject = $this->crud->create($contextHandler::getSubjectClass(), $data['data'], [Crud::NO_PERMISSIONS, Options::PERSIST_TAG]);
        }

        $this->om->startFlushSuite();

        // add tools to the context
        if (!empty($data['tools'])) {
            foreach ($data['tools'] as $toolData) {
                $newTool = new OrderedTool();
                $newTool->setContextName($contextType);
                $newTool->setContextId($contextSubject?->getContextIdentifier());

                $this->crud->create($newTool, $toolData, [Crud::NO_PERMISSIONS]);
            }
        }

        $this->om->endFlushSuite();

        // let the handler of the context type do some stuff if needed
        $contextHandler->create($contextSubject, $data);

        return $contextSubject;
    }

    public function update(string $contextType, ?string $contextId = null, ?array $data = []): ?ContextSubjectInterface
    {
        try {
            $contextHandler = $this->contextProvider->getContext($contextType);
            $contextSubject = $contextHandler->getSubject($contextId);
        } catch (\Exception $e) {
            throw new ContextNotFoundException($contextType, $contextId, $e);
        }

        if (!$contextHandler->isGranted('ADMINISTRATE', $contextSubject)) {
            throw new AccessDeniedException();
        }

        $this->om->startFlushSuite();

        // update context configuration
        if (!empty($data['data']) && $contextSubject) {
            $this->crud->update($contextSubject, $data['data'], [Crud::NO_PERMISSIONS, Options::PERSIST_TAG]);
        }

        // update tools configuration if any
        $contextTools = $this->toolProvider->getEnabledTools($contextType, $contextSubject);
        if (!empty($data['tools'])) {
            $updatedTools = [];
            foreach ($data['tools'] as $toolData) {
                $updatedTool = new OrderedTool();
                $updatedTool->setContextName($contextType);
                $updatedTool->setContextId($contextSubject?->getContextIdentifier());

                $updatedTool = $this->crud->createOrUpdate($updatedTool, $toolData, [Crud::NO_PERMISSIONS]);
                $updatedTools[$updatedTool->getName()] = $updatedTool;
            }

            foreach ($contextTools as $existingTool) {
                if (!array_key_exists($existingTool->getName(), $updatedTools)) {
                    $this->crud->delete($existingTool);
                }
            }
        }

        $this->om->endFlushSuite();

        // let the handler of the context type do some stuff if needed
        $contextHandler->update($contextSubject, $data);

        return $contextSubject;
    }
}
