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

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Log\Connection\AbstractLogConnect;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectAdminTool;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectPlatform;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectResource;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectTool;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Log\LogAdminToolReadEvent;
use Claroline\CoreBundle\Event\Log\LogDesktopToolReadEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @todo : find the proper way to manage connections to deleted resources (for now its kept in DB).
 */
class LogConnectManager
{
    /** @var FinderProvider */
    private $finder;

    /** @var ObjectManager */
    private $om;

    /** @var WorkspaceEvaluationManager */
    private $workspaceEvaluationManager;

    /** @var ResourceEvaluationManager */
    private $resourceEvaluationManager;

    /** @var TranslatorInterface */
    private $translator;

    private $logRepo;
    /** @var OrderedToolRepository */
    private $orderedToolRepo;
    private $adminToolRepo;
    private $workspaceRepo;

    private $logPlatformRepo;
    private $logWorkspaceRepo;
    private $logToolRepo;
    private $logResourceRepo;
    private $logAdminToolRepo;

    public function __construct(
        FinderProvider $finder,
        ObjectManager $om,
        WorkspaceEvaluationManager $workspaceEvaluationManager,
        ResourceEvaluationManager $resourceEvaluationManager,
        TranslatorInterface $translator
    ) {
        $this->finder = $finder;
        $this->om = $om;
        $this->workspaceEvaluationManager = $workspaceEvaluationManager;
        $this->resourceEvaluationManager = $resourceEvaluationManager;
        $this->translator = $translator;

        $this->logRepo = $om->getRepository(Log::class);
        $this->orderedToolRepo = $om->getRepository(OrderedTool::class);
        $this->adminToolRepo = $om->getRepository(AdminTool::class);
        $this->workspaceRepo = $om->getRepository(Workspace::class);

        $this->logPlatformRepo = $om->getRepository(LogConnectPlatform::class);
        $this->logWorkspaceRepo = $om->getRepository(LogConnectWorkspace::class);
        $this->logToolRepo = $om->getRepository(LogConnectTool::class);
        $this->logResourceRepo = $om->getRepository(LogConnectResource::class);
        $this->logAdminToolRepo = $om->getRepository(LogConnectAdminTool::class);
    }

    public function manageConnection(Log $log)
    {
        $action = $log->getAction();
        $dateLog = $log->getDateLog();
        $user = $log->getDoer();

        if (!is_null($user)) {
            switch ($action) {
                case SecurityEvents::USER_LOGIN:
                    $this->om->startFlushSuite();

                    $this->createLogConnectPlatform($user, $dateLog);

                    $this->om->endFlushSuite();
                    break;
                case LogWorkspaceEnterEvent::ACTION:
                    $this->om->startFlushSuite();

                    $logWorkspace = $log->getWorkspace();

                    // Computes duration for the most recent workspace connection (with no duration)
                    // for the current user's session
                    $workspaceConnection = $this->getComputableWorkspace($user);

                    if (!is_null($workspaceConnection)) {
                        $updatedWorkspaceConnection = $this->computeConnectionDuration($workspaceConnection, $dateLog);
                    }

                    if (!empty($updatedWorkspaceConnection) && $updatedWorkspaceConnection->getDuration()) {
                        $this->workspaceEvaluationManager->addDurationToWorkspaceEvaluation($workspaceConnection->getWorkspace(), $user, $updatedWorkspaceConnection->getDuration());
                    }

                    // Creates workspace log for current connection
                    $this->createLogConnectWorkspace($user, $logWorkspace, $dateLog);

                    $this->om->endFlushSuite();
                    break;
                /*
                 * When opening tool, computes duration for :
                 * - last resource
                 * - last admin tool
                 * - last tool
                 */
                case LogWorkspaceToolReadEvent::ACTION:
                case LogDesktopToolReadEvent::ACTION:
                    $this->om->startFlushSuite();

                    $logWorkspace = $log->getWorkspace();
                    $logToolName = $log->getToolName();

                    // Computes duration for the most recent tool connection (with no duration)
                    // for the current user's session
                    $toolConnection = $this->getComputableLogTool($user);
                    $resourceConnection = $this->getComputableLogResource($user);
                    $adminToolConnection = $this->getComputableLogAdminTool($user);

                    // Computes last resource duration
                    if (!is_null($resourceConnection) && !empty($resourceConnection->getResource())) {
                        $updatedConnection = $this->computeConnectionDuration($resourceConnection, $dateLog);

                        if ($updatedConnection && $updatedConnection->getDuration()) {
                            $this->resourceEvaluationManager->addDurationToResourceEvaluation(
                                $resourceConnection->getResource(),
                                $user,
                                $updatedConnection->getDuration()
                            );
                        }
                    }
                    // Computes last admin tool duration
                    if (!is_null($adminToolConnection)) {
                        $this->computeConnectionDuration($adminToolConnection, $dateLog);
                    }
                    // Computes last workspace duration if opening desktop tool
                    if (is_null($logWorkspace)) {
                        $workspaceConnection = $this->getComputableWorkspace($user);

                        if (!is_null($workspaceConnection)) {
                            $updatedWorkspaceConnection = $this->computeConnectionDuration($workspaceConnection, $dateLog);
                            if ($updatedWorkspaceConnection && $updatedWorkspaceConnection->getDuration()) {
                                $this->workspaceEvaluationManager->addDurationToWorkspaceEvaluation($workspaceConnection->getWorkspace(), $user, $updatedWorkspaceConnection->getDuration());
                            }
                        }
                    }
                    // Computes last tool duration
                    if (!is_null($toolConnection)) {
                        // Ignores log if previous tool opening log and this one are associated to the same tool for the current session
                        if (((is_null($toolConnection->getWorkspace()) && is_null($logWorkspace)) || $toolConnection->getWorkspace() === $logWorkspace) &&
                            $toolConnection->getToolName() === $logToolName
                        ) {
                            break;
                        } else {
                            $this->computeConnectionDuration($toolConnection, $dateLog);
                        }
                    }
                    // Creates tool log for current connection
                    $this->createLogConnectTool($user, $logToolName, $dateLog, $logWorkspace);

                    $this->om->endFlushSuite();
                    break;
                /*
                 * When opening resource, computes duration for :
                 * - last tool
                 * - last admin tool
                 * - last resource
                 */
                case LogResourceReadEvent::ACTION:
                    $this->om->startFlushSuite();

                    $logResourceNode = $log->getResourceNode();
                    $details = $log->getDetails();
                    $embedded = $details && isset($details['embedded']) ? $details['embedded'] : false;

                    if (!$embedded) {
                        // Computes duration for the most recent resource opening (with no duration)
                        // for the current user's session
                        $resourceConnection = $this->getComputableLogResource($user);
                        $toolConnection = $this->getComputableLogTool($user);
                        $adminToolConnection = $this->getComputableLogAdminTool($user);

                        // Computes last workspace tool duration, but not the resources tool
                        if (!is_null($toolConnection) && !(
                            'resources' === $toolConnection->getToolName() &&
                            $toolConnection->getWorkspace() === $logResourceNode->getWorkspace()
                        )) {
                            $this->computeConnectionDuration($toolConnection, $dateLog);
                        }
                        // Computes last admin tool duration
                        if (!is_null($adminToolConnection)) {
                            $this->computeConnectionDuration($adminToolConnection, $dateLog);
                        }
                        // Computes last resource duration
                        if (!is_null($resourceConnection) && !empty($resourceConnection->getResource())) {
                            // Ignores log if previous resource opening log and this one are associated to the same resource
                            // for the current session
                            if ($resourceConnection->getResource() === $logResourceNode) {
                                break;
                            } else {
                                $updatedConnection = $this->computeConnectionDuration($resourceConnection, $dateLog);
                            }

                            if ($updatedConnection && $updatedConnection->getDuration()) {
                                $this->resourceEvaluationManager->addDurationToResourceEvaluation(
                                    $resourceConnection->getResource(),
                                    $user,
                                    $updatedConnection->getDuration()
                                );
                            }
                        }
                    }
                    // Creates resource log for current connection
                    $this->createLogConnectResource($user, $logResourceNode, $dateLog, $embedded);

                    $this->om->endFlushSuite();
                    break;
                /*
                 * When opening admin tool, computes duration for :
                 * - last resource
                 * - last tool
                 * - last admin tool
                 */
                case LogAdminToolReadEvent::ACTION:
                    $this->om->startFlushSuite();

                    $logToolName = $log->getToolName();

                    // Computes duration for the most recent admin tool connection (with no duration)
                    // for the current user's session
                    $adminToolConnection = $this->getComputableLogAdminTool($user);
                    $toolConnection = $this->getComputableLogTool($user);
                    $resourceConnection = $this->getComputableLogResource($user);
                    $workspaceConnection = $this->getComputableWorkspace($user);

                    // Computes last workspace duration
                    if (!is_null($workspaceConnection)) {
                        $this->computeConnectionDuration($workspaceConnection, $dateLog);
                    }
                    // Computes last resource duration
                    if (!is_null($resourceConnection) && !empty($resourceConnection->getResource())) {
                        $updatedConnection = $this->computeConnectionDuration($resourceConnection, $dateLog);

                        if ($updatedConnection && $updatedConnection->getDuration()) {
                            $this->resourceEvaluationManager->addDurationToResourceEvaluation(
                                $resourceConnection->getResource(),
                                $user,
                                $updatedConnection->getDuration()
                            );
                        }
                    }
                    // Computes last tool duration
                    if (!is_null($toolConnection)) {
                        $this->computeConnectionDuration($toolConnection, $dateLog);
                    }
                    // Computes last admin tool duration
                    if (!is_null($adminToolConnection)) {
                        // Ignores log if previous admin tool opening log and this one are associated to the same admin tool
                        // for the current session
                        if ($adminToolConnection->getToolName() === $logToolName) {
                            break;
                        } else {
                            $this->computeConnectionDuration($adminToolConnection, $dateLog);
                        }
                    }
                    // Creates admin tool log for current connection
                    $this->createLogConnectAdminTool($user, $logToolName, $dateLog);

                    $this->om->endFlushSuite();
                    break;
            }
        }
    }

    public function computeWorkspaceDuration(User $user, Workspace $workspace)
    {
        $workspaceConnection = $this->getLogConnectWorkspaceByWorkspace($user, $workspace);

        if (!is_null($workspaceConnection)) {
            $updatedWorkspaceConnection = $this->computeConnectionDuration($workspaceConnection, new \DateTime());
            if ($updatedWorkspaceConnection && $updatedWorkspaceConnection->getDuration()) {
                $this->workspaceEvaluationManager->addDurationToWorkspaceEvaluation($workspaceConnection->getWorkspace(), $user, $updatedWorkspaceConnection->getDuration());
            }
        }
    }

    public function computeResourceDuration(User $user, ResourceNode $resource, $embedded)
    {
        $resourceConnection = $this->getLogConnectResourceByResource($user, $resource, $embedded);

        if (!is_null($resourceConnection) && !empty($resourceConnection->getResource())) {
            $updatedConnection = $this->computeConnectionDuration($resourceConnection, new \DateTime());

            if ($updatedConnection && $updatedConnection->getDuration()) {
                $this->resourceEvaluationManager->addDurationToResourceEvaluation(
                    $resourceConnection->getResource(),
                    $user,
                    $updatedConnection->getDuration()
                );
            }
        }
    }

    public function computeToolDuration(User $user, $toolName, $context, $contextId = null)
    {
        $toolConnection = null;

        switch ($context) {
            case 'administration':
                $toolConnection = $this->getLogConnectAdminToolByName($user, $toolName);
                break;
            case 'desktop':
                $toolConnection = $this->getLogConnectToolByName($user, $toolName);
                break;
            case 'workspace':
                $workspace = !empty($contextId) ?
                    $this->workspaceRepo->findOneBy(['uuid' => $contextId]) :
                    null;

                if ($workspace) {
                    $toolConnection = $this->getLogConnectToolByName($user, $toolName, $workspace);
                }
                break;
        }
        if (!is_null($toolConnection)) {
            $this->computeConnectionDuration($toolConnection, new \DateTime());
        }
    }

    public function computeAllPlatformDuration()
    {
        $usersDone = [];

        // Fetches all platform connections with no duration
        /** @var LogConnectPlatform[] $connections */
        $connections = $this->logPlatformRepo->findBy(
            ['duration' => null],
            ['connectionDate' => 'ASC']
        );

        foreach ($connections as $connection) {
            $user = $connection->getUser();

            if (!isset($usersDone[$user->getUuid()])) {
                // Fetches all platform connections with no duration for an user
                $userConnections = $this->logPlatformRepo->findBy(
                    ['user' => $user, 'duration' => null],
                    ['connectionDate' => 'ASC']
                );
                $this->computeConnectionDurationFromLogs($user, $userConnections);
                $usersDone[$user->getUuid()] = true;
            }
        }
    }

    public function computeAllWorkspacesDuration()
    {
        $usersDone = [];

        // Fetches all workspaces connections with no duration
        /** @var LogConnectWorkspace[] $connections */
        $connections = $this->logWorkspaceRepo->findBy(
            ['duration' => null],
            ['connectionDate' => 'ASC']
        );

        foreach ($connections as $connection) {
            $user = $connection->getUser();

            if (!isset($usersDone[$user->getUuid()])) {
                // Fetches all workspaces connections with no duration for an user
                $userConnections = $this->logWorkspaceRepo->findBy(
                    ['user' => $user, 'duration' => null],
                    ['connectionDate' => 'ASC']
                );
                $this->computeConnectionDurationFromLogs($user, $userConnections);
                $usersDone[$user->getUuid()] = true;
            }
        }
    }

    public function computeAllToolsDuration()
    {
        $usersDone = [];

        // Fetches all tools connections with no duration
        /** @var LogConnectTool $connections */
        $connections = $this->logToolRepo->findBy(
            ['duration' => null],
            ['connectionDate' => 'ASC']
        );

        foreach ($connections as $connection) {
            $user = $connection->getUser();

            if (!isset($usersDone[$user->getUuid()])) {
                // Fetches all tools connections with no duration for an user
                $userConnections = $this->logToolRepo->findBy(
                    ['user' => $user, 'duration' => null],
                    ['connectionDate' => 'ASC']
                );
                $this->computeConnectionDurationFromLogs($user, $userConnections);
                $usersDone[$user->getUuid()] = true;
            }
        }
    }

    public function computeAllAdminToolsDuration()
    {
        $usersDone = [];

        // Fetches all tools connections with no duration
        /** @var LogConnectAdminTool[] $connections */
        $connections = $this->logAdminToolRepo->findBy(
            ['duration' => null],
            ['connectionDate' => 'ASC']
        );

        foreach ($connections as $connection) {
            $user = $connection->getUser();

            if (!isset($usersDone[$user->getUuid()])) {
                // Fetches all admin tools connections with no duration for an user
                $userConnections = $this->logAdminToolRepo->findBy(
                    ['user' => $user, 'duration' => null],
                    ['connectionDate' => 'ASC']
                );
                $this->computeConnectionDurationFromLogs($user, $userConnections);
                $usersDone[$user->getUuid()] = true;
            }
        }
    }

    public function computeAllResourcesDuration()
    {
        $usersDone = [];

        // Fetches all resources connections with no duration
        /** @var LogConnectResource[] $connections */
        $connections = $this->logResourceRepo->findBy(
            ['embedded' => false, 'duration' => null],
            ['connectionDate' => 'ASC']
        );

        foreach ($connections as $connection) {
            $user = $connection->getUser();

            if (!isset($usersDone[$user->getUuid()])) {
                // Fetches all resources connections with no duration for an user
                $userConnections = $this->logResourceRepo->findBy(
                    ['user' => $user, 'embedded' => false, 'duration' => null],
                    ['connectionDate' => 'ASC']
                );
                $this->computeConnectionDurationFromLogs($user, $userConnections);
                $usersDone[$user->getUuid()] = true;
            }
        }
    }

    public function computeConnectionDurationFromLogs(User $user, array $connections)
    {
        $this->om->startFlushSuite();
        $i = 0;

        foreach ($connections as $connection) {
            // Gets the following platform connection
            $filters = [
                'user' => $user->getUuid(),
                'afterDate' => $connection->getConnectionDate(),
            ];
            $sortBy = ['property' => 'connectionDate', 'direction' => 1];
            $nextConnections = $this->finder->get(LogConnectPlatform::class)->find($filters, $sortBy, 0, 1);
            $nextDate = 0 < count($nextConnections) ? $nextConnections[0]->getConnectionDate() : null;

            // Gets most recent log preceding the following platform connection
            if (!is_null($nextDate)) {
                $logFilters = [
                    'doer' => $user->getUuid(),
                    'dateToStrict' => $nextDate,
                ];
                $logSortBy = ['property' => 'dateLog', 'direction' => -1];
                $logs = $this->finder->get(Log::class)->find($logFilters, $logSortBy, 0, 1);

                if (1 === count($logs) && $this->computeConnectionDuration($connection, $logs[0]->getDateLog())) {
                    ++$i;

                    if (0 === $i % 200) {
                        $this->om->forceFlush();
                    }
                }
            }
        }

        $this->om->endFlushSuite();
    }

    public function exportConnectionsToCsv($class, array $filters = [], $sortBy = null, $output = null)
    {
        $connections = $this->finder->get($class)->find($filters, $sortBy);

        if (null === $output) {
            $output = 'php://output';
        }
        $fp = fopen($output, 'w+');
        fputcsv($fp, [
            $this->translator->trans('date', [], 'platform'),
            $this->translator->trans('user', [], 'platform'),
            $this->translator->trans('duration', [], 'platform'),
        ], ';', '"');

        foreach ($connections as $connection) {
            $duration = $connection->getDuration();
            $durationString = null;

            if (!is_null($duration)) {
                $hours = floor($duration / 3600);
                $duration %= 3600;
                $minutes = floor($duration / 60);
                $seconds = $duration % 60;

                $durationString = "{$hours}:";
                $durationString .= 10 > $minutes ? "0{$minutes}:" : "{$minutes}:";
                $durationString .= 10 > $seconds ? "0{$seconds}" : "{$seconds}";
            }
            fputcsv($fp, [
                $connection->getConnectionDate()->format('Y-m-d H:i:s'),
                $connection->getUser()->getFirstName().' '.$connection->getUser()->getLastName(),
                $durationString,
            ], ';', '"');
        }
        fclose($fp);

        return $fp;
    }

    private function getLogConnectPlatform(User $user): ?LogConnectPlatform
    {
        $openConnections = $this->logPlatformRepo->findBy(
            ['user' => $user],
            ['connectionDate' => 'DESC'],
            1
        );

        // Only fetches connection if there is no duration
        return 0 < count($openConnections) && is_null($openConnections[0]->getDuration()) ? $openConnections[0] : null;
    }

    private function getLogConnectWorkspace(User $user): ?LogConnectWorkspace
    {
        $openConnections = $this->logWorkspaceRepo->findBy(
            ['user' => $user],
            ['connectionDate' => 'DESC'],
            1
        );

        // Only fetches connection if there is no duration
        return 0 < count($openConnections) && is_null($openConnections[0]->getDuration()) ? $openConnections[0] : null;
    }

    private function getLogConnectWorkspaceByWorkspace(User $user, Workspace $workspace): ?LogConnectWorkspace
    {
        $openConnections = $this->logWorkspaceRepo->findBy(
            ['user' => $user, 'workspace' => $workspace],
            ['connectionDate' => 'DESC'],
            1
        );

        // Only fetches connection if there is no duration
        return 0 < count($openConnections) && is_null($openConnections[0]->getDuration()) ? $openConnections[0] : null;
    }

    private function getLogConnectTool(User $user): ?LogConnectTool
    {
        $openConnections = $this->logToolRepo->findBy(
            ['user' => $user],
            ['connectionDate' => 'DESC'],
            1
        );

        // Only fetches connection if there is no duration
        return 0 < count($openConnections) && is_null($openConnections[0]->getDuration()) ? $openConnections[0] : null;
    }

    private function getLogConnectToolByName(User $user, $toolName, Workspace $workspace = null): ?LogConnectTool
    {
        $openConnections = $this->logToolRepo->findBy(
            ['user' => $user, 'toolName' => $toolName, 'workspace' => $workspace],
            ['connectionDate' => 'DESC'],
            1
        );

        // Only fetches connection if there is no duration
        return 0 < count($openConnections) && is_null($openConnections[0]->getDuration()) ? $openConnections[0] : null;
    }

    private function getLogConnectAdminTool(User $user): ?LogConnectAdminTool
    {
        $openConnections = $this->logAdminToolRepo->findBy(
            ['user' => $user],
            ['connectionDate' => 'DESC'],
            1
        );

        // Only fetches connection if there is no duration
        return 0 < count($openConnections) && is_null($openConnections[0]->getDuration()) ? $openConnections[0] : null;
    }

    private function getLogConnectAdminToolByName(User $user, $toolName): ?LogConnectAdminTool
    {
        $openConnections = $this->logAdminToolRepo->findBy(
            ['user' => $user, 'toolName' => $toolName],
            ['connectionDate' => 'DESC'],
            1
        );

        // Only fetches connection if there is no duration
        return 0 < count($openConnections) && is_null($openConnections[0]->getDuration()) ? $openConnections[0] : null;
    }

    private function getLogConnectResource(User $user): ?LogConnectResource
    {
        $openConnections = $this->logResourceRepo->findBy(
            ['user' => $user, 'embedded' => false],
            ['connectionDate' => 'DESC'],
            1
        );

        // Only fetches connection if there is no duration
        return 0 < count($openConnections) && is_null($openConnections[0]->getDuration()) ? $openConnections[0] : null;
    }

    private function getLogConnectResourceByResource(User $user, ResourceNode $resourceNode, $embedded): ?LogConnectResource
    {
        $openConnections = $this->logResourceRepo->findBy(
            ['user' => $user, 'resource' => $resourceNode, 'embedded' => $embedded],
            ['connectionDate' => 'DESC'],
            1
        );

        // Only fetches connection if there is no duration
        return 0 < count($openConnections) && is_null($openConnections[0]->getDuration()) ? $openConnections[0] : null;
    }

    private function getComputableWorkspace(User $user): ?LogConnectWorkspace
    {
        // Gets the most recent workspace connection (with no duration) for the current user's session
        $workspaceConnection = $this->getLogConnectWorkspace($user);
        // Gets current user's connection to platform
        $platformConnection = $this->getLogConnectPlatform($user);

        $isComputable = !is_null($workspaceConnection) && $workspaceConnection->getWorkspace() &&
            !is_null($platformConnection) &&
            $this->isComputableWithoutLogs($workspaceConnection, $platformConnection);

        return $isComputable ? $workspaceConnection : null;
    }

    private function getComputableLogTool(User $user): ?LogConnectTool
    {
        // Gets the most recent workspace tool connection (with no duration) for the current user's session
        $toolConnection = $this->getLogConnectTool($user);
        // Gets current user's connection to platform
        $platformConnection = $this->getLogConnectPlatform($user);

        $isComputable = !is_null($toolConnection) &&
            !is_null($platformConnection) &&
            $this->isComputableWithoutLogs($toolConnection, $platformConnection);

        return $isComputable ? $toolConnection : null;
    }

    private function getComputableLogResource(User $user): ?LogConnectResource
    {
        // Gets the most recent resource opening (with no duration) for the current user's session
        $resourceConnection = $this->getLogConnectResource($user);
        // Gets current user's connection to platform
        $platformConnection = $this->getLogConnectPlatform($user);

        $isComputable = !is_null($resourceConnection) &&
            !is_null($platformConnection) &&
            $this->isComputableWithoutLogs($resourceConnection, $platformConnection);

        return $isComputable ? $resourceConnection : null;
    }

    private function getComputableLogAdminTool(User $user): ?LogConnectAdminTool
    {
        // Gets the most recent admin tool connection (with no duration) for the current user's session
        $toolConnection = $this->getLogConnectAdminTool($user);
        // Gets current user's connection to platform
        $platformConnection = $this->getLogConnectPlatform($user);

        $isComputable = !is_null($toolConnection) &&
            !is_null($platformConnection) &&
            $this->isComputableWithoutLogs($toolConnection, $platformConnection);

        return $isComputable ? $toolConnection : null;
    }

    private function computeConnectionDuration(AbstractLogConnect $connection, \DateTime $date): ?AbstractLogConnect
    {
        $connectionDate = $connection->getConnectionDate();

        if ($date >= $connectionDate) {
            $duration = $date->getTimestamp() - $connectionDate->getTimestamp();
            $connection->setDuration($duration);
            $this->om->persist($connection);
            $this->om->flush();

            return $connection;
        }

        return null;
    }

    private function createLogConnectPlatform(User $user, \DateTime $date): void
    {
        // Creates a new platform connection with no duration for the current connection
        $newConnection = new LogConnectPlatform();
        $newConnection->setUser($user);
        $newConnection->setConnectionDate($date);
        $this->om->persist($newConnection);
        $this->om->flush();
    }

    private function createLogConnectWorkspace(User $user, Workspace $workspace, \DateTime $date): void
    {
        // Creates a new workspace connection with no duration for the current connection
        $newConnection = new LogConnectWorkspace();
        $newConnection->setUser($user);
        $newConnection->setConnectionDate($date);
        $newConnection->setWorkspace($workspace);
        $this->om->persist($newConnection);
        $this->om->flush();
    }

    private function createLogConnectTool(User $user, $toolName, \DateTime $date, Workspace $workspace = null): void
    {
        $orderedTool = $this->orderedToolRepo->findOneByNameAndWorkspace($toolName, $workspace);

        if (!is_null($orderedTool)) {
            // Creates a new workspace tool connection with no duration for the current connection
            $newConnection = new LogConnectTool();
            $newConnection->setUser($user);
            $newConnection->setConnectionDate($date);
            $newConnection->setTool($orderedTool);
            $this->om->persist($newConnection);
            $this->om->flush();
        }
    }

    private function createLogConnectResource(User $user, ResourceNode $node, \DateTime $date, $embedded = false): void
    {
        // Creates a new resource connection with no duration for the current connection
        $newConnection = new LogConnectResource();
        $newConnection->setUser($user);
        $newConnection->setConnectionDate($date);
        $newConnection->setResource($node);
        $newConnection->setEmbedded($embedded);
        $this->om->persist($newConnection);
        $this->om->flush();
    }

    private function createLogConnectAdminTool(User $user, $toolName, \DateTime $date): void
    {
        $adminTool = $this->adminToolRepo->findOneBy(['name' => $toolName]);

        if (!is_null($adminTool)) {
            // Creates a new admin tool connection with no duration for the current connection
            $newConnection = new LogConnectAdminTool();
            $newConnection->setUser($user);
            $newConnection->setConnectionDate($date);
            $newConnection->setTool($adminTool);
            $this->om->persist($newConnection);
            $this->om->flush();
        }
    }

    private function isComputableWithoutLogs(AbstractLogConnect $connection, LogConnectPlatform $platformConnect): bool
    {
        return $connection->getConnectionDate() > $platformConnect->getConnectionDate();
    }
}
