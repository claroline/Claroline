<?php

namespace Claroline\EvaluationBundle\Component\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\ToolComponent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Entity\Sequence\Assignment;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Entity\UserEvaluation\WorkspaceEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationOptions;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProgressionTool extends ToolComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud
    ) {
    }

    public static function getName(): string
    {
        return 'progression';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            DesktopContext::getName(),
            WorkspaceContext::getName(),
        ]);
    }

    public static function getIcon(): string
    {
        return 'route';
    }

    public function getStatus(string $context, ?ContextSubjectInterface $contextSubject = null): ?string
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user instanceof User) {
            return null;
        }

        if (empty($contextSubject)) {
            return null;
        }

        $workspaceEvaluation = $this->getUserEvaluation($contextSubject);
        $progression = $workspaceEvaluation->getProgression();
        if ($progression) {
            $progression = round($progression, EvaluationOptions::PROGRESSION_PRECISION);
        }

        return $progression.'%';
    }

    public function open(OrderedTool $tool, string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        $workspaceEvaluation = null;

        if (empty($contextSubject)) {
            return [];
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user instanceof User) {
            $workspaceEvaluation = $this->getUserEvaluation($contextSubject);
            $assignments = $this->om->getRepository(Assignment::class)->findByWorkspaceAndUser($contextSubject, $user);

            $sequences = array_map(function (Assignment $assignment) {
                return $assignment->getSequence();
            }, $assignments);
        } else {
            $sequences = $this->om->getRepository(Sequence::class)->findBy([
                'published' => true,
                'public' => true,
                'workspace' => $contextSubject,
            ]);
        }

        return [
            'sequences' => array_map(function (Sequence $sequence) {
                return $this->serializer->serialize($sequence, [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $sequences),
            'workspaceEvaluation' => $workspaceEvaluation ? $this->serializer->serialize($workspaceEvaluation) : null,
        ];
    }

    public function configure(OrderedTool $tool, string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        if (!empty($configData['evaluation'])) {
            $this->crud->update($contextSubject, ['evaluation' => $configData['evaluation']], [Crud::NO_PERMISSIONS]);

            return [
                'evaluation' => $configData['evaluation'],
            ];
        }

        return [];
    }

    public function export(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null): ?array
    {
        $sequences = $this->om->getRepository(Sequence::class)->findBy(['workspace' => $contextSubject]);
        if (empty($sequences)) {
            return [];
        }

        return [
            'sequences' => array_map(function (Sequence $sequence) use ($fileBag) {
                if ($sequence->getPoster()) {
                    $fileBag->add($sequence->getPoster(), $sequence->getPoster());
                }

                return $this->serializer->serialize($sequence, [SerializerInterface::SERIALIZE_TRANSFER]);
            }, $sequences),
        ];
    }

    public function import(string $context, ?ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null, array $data = [], array $entities = []): ?array
    {
        if (empty($data['sequences'])) {
            return [];
        }

        $this->om->startFlushSuite();

        foreach ($data['sequences'] as $sequenceData) {
            $newSequence = new Sequence();
            $newSequence->setWorkspace($contextSubject);

            $assignments = [];
            if (!empty($sequenceData['assignments'])) {
                // we need to manually manage sequence requirements to link them to the correct roles
                $assignments = $sequenceData['assignments'];
                unset($sequenceData['assignments']);
            }

            $this->crud->create($newSequence, $sequenceData, [
                Crud::NO_PERMISSIONS, // the core has already checked this before forwarding the import
                Crud::NO_VALIDATION,
                Options::REFRESH_UUID,
                Options::PERSIST_TAG,
            ]);

            if (!empty($assignments)) {
                foreach ($assignments as $assignmentData) {
                    if (Role::WORKSPACE !== $assignmentData['role']['type']) {
                        $role = $this->om->getRepository(Role::class)->findOneBy(['uuid' => $assignmentData['role']['id']]);
                    } elseif (!empty($entities[$assignmentData['role']['id']])) {
                        $role = $entities[$assignmentData['role']['id']];
                    }

                    if (!empty($role)) {
                        $assignment = new Assignment();
                        $newSequence->addAssignment($assignment);
                        $this->serializer->deserialize(array_merge($assignmentData, [
                            'role' => [
                                'id' => $role->getUuid(),
                            ],
                        ]), $assignment, [Options::REFRESH_UUID]);
                    }
                }
            }

            $entities[$sequenceData['id']] = $newSequence;
        }

        $this->om->endFlushSuite();

        return $entities;
    }

    private function getUserEvaluation(Workspace $workspace): ?WorkspaceEvaluation
    {
        $workspaceEvaluation = $this->om->getRepository(WorkspaceEvaluation::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $this->tokenStorage->getToken()?->getUser(),
            'archived' => false,
        ]);

        if (empty($workspaceEvaluation)) {
            $workspaceEvaluation = new WorkspaceEvaluation();
            $workspaceEvaluation->setWorkspace($workspace);
            $workspaceEvaluation->setUser($this->tokenStorage->getToken()?->getUser());
        }

        return $workspaceEvaluation;
    }
}
