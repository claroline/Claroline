<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 4/13/17
 */

namespace Claroline\ExternalSynchronizationBundle\Manager;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\PluginManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ExternalSynchronizationBundle\Entity\ExternalGroup;
use Claroline\ExternalSynchronizationBundle\Entity\ExternalUser;
use Claroline\ExternalSynchronizationBundle\Repository\ExternalResourceSynchronizationRepository;
use Cocur\Slugify\Slugify;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

/**
 * Class ExternalSynchronizationManager.
 *
 * @DI\Service("claroline.manager.external_user_group_sync_manager")
 */
class ExternalSynchronizationManager
{
    use LoggableTrait;

    const USER_BATCH_SIZE_COMMAND = 250;
    const USER_BATCH_SIZE_BROWSER = 50;

    /** @var string */
    private $syncFilePath;
    /** @var ObjectManager */
    private $om;
    /** @var UserManager */
    private $userManager;
    /** @var GroupManager */
    private $groupManager;
    /** @var ExternalSynchronizationUserManager */
    private $externalUserManager;
    /** @var ExternalSynchronizationGroupManager */
    private $externalGroupManager;
    /** @var \Claroline\CasBundle\Manager\CasManager|object */
    private $casManager;
    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;
    /** @var mixed */
    private $sourcesArray;
    /** @var Slugify */
    private $slugify;
    /** @var Parser */
    private $ymlParser;
    /** @var Dumper */
    private $ymlDumper;
    /** @var ClaroUtilities */
    private $utilities;

    /**
     * @DI\InjectParams({
     *     "synchronizationDir"     = @DI\Inject("%claroline.param.synchronization_directory%"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "externalUserManager"    = @DI\Inject("claroline.manager.external_user_sync_manager"),
     *     "externalGroupManager"   = @DI\Inject("claroline.manager.external_group_sync_manager"),
     *     "groupManager"           = @DI\Inject("claroline.manager.group_manager"),
     *     "pluginManager"          = @DI\Inject("claroline.manager.plugin_manager"),
     *     "container"              = @DI\Inject("service_container"),
     *     "platformConfigHandler"  = @DI\Inject("claroline.config.platform_config_handler"),
     *     "utilities"              = @DI\Inject("claroline.utilities.misc")
     * })
     *
     * @param $synchronizationDir
     * @param ObjectManager                $om
     * @param UserManager                  $userManager
     * @param GroupManager                 $groupManager
     * @param PlatformConfigurationHandler $platformConfigHandler
     */
    public function __construct(
        $synchronizationDir,
        ObjectManager $om,
        UserManager $userManager,
        ExternalSynchronizationUserManager $externalUserManager,
        ExternalSynchronizationGroupManager $externalGroupManager,
        GroupManager $groupManager,
        PluginManager $pluginManager,
        ContainerInterface $container,
        PlatformConfigurationHandler $platformConfigHandler,
        ClaroUtilities $utilities
    ) {
        $this->syncFilePath = $synchronizationDir.'external.sources.yml';
        $this->ymlParser = new Parser();
        $this->ymlDumper = new Dumper();
        $this->slugify = new Slugify();
        $this->om = $om;
        $this->userManager = $userManager;
        $this->externalUserManager = $externalUserManager;
        $this->externalGroupManager = $externalGroupManager;
        $this->groupManager = $groupManager;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->sourcesArray = $this->loadExternalSources();
        $this->casManager = null;
        $this->utilities = $utilities;
        if ($pluginManager->isLoaded('ClarolineCasBundle')) {
            $this->casManager = $container->get('claroline.manager.cas_manager');
        }
    }

    public function getExternalSourcesNames($filterBy = [])
    {
        $sources = $this->sourcesArray['sources'];
        $names = [];
        foreach ($sources as $key => $source) {
            if ($this->isPropertyConfigured($source, $filterBy)) {
                $names[$key] = $source['name'];
            }
        }

        return $names;
    }

    public function getExternalSourceList()
    {
        $sources = $this->sourcesArray['sources'];
        $sourceList = [];
        foreach ($sources as $key => $source) {
            $sourceList[] = [
                'name' => $source['name'],
                'slug' => $key,
            ];
        }

        return $sourceList;
    }

    public function getExternalSource($name, $withSlug = false)
    {
        $name = $this->slugifyName($name);
        $source = isset($this->sourcesArray['sources'][$name]) ? $this->sourcesArray['sources'][$name] : [];
        if (!empty($source) && $withSlug) {
            $source['slug'] = $name;
        }

        return $source;
    }

    public function setExternalSource($name, array $config, $oldName = null)
    {
        $name = $this->slugifyName($name);
        if (!is_null($oldName)) {
            $oldName = $this->slugifyName($oldName);
            if (isset($this->sourcesArray['sources'][$oldName])) {
                unset($this->sourcesArray['sources'][$oldName]);
                $this->externalGroupManager->updateGroupsExternalSourceName($oldName, $name);
                $this->externalUserManager->updateUsersExternalSourceName($oldName, $name);
            }
        }

        $this->sourcesArray['sources'][$name] = $config;

        return $this->saveConfig() ? $name : false;
    }

    public function deleteExternalSource($sourceName)
    {
        if (isset($this->sourcesArray['sources'][$sourceName])) {
            unset($this->sourcesArray['sources'][$sourceName]);
            $this->externalGroupManager->deleteGroupsForExternalSource($sourceName);
            $this->externalUserManager->deleteUsersForExternalSource($sourceName);

            return $this->saveConfig();
        }

        return true;
    }

    public function getTableAndViewNames($sourceName)
    {
        $repo = $this->getRepositoryForExternalSource($this->getExternalSource($sourceName));

        $names = [];
        try {
            $names = $repo->findTableNames();
        } catch (\Exception $e) {
            unset($e);
        }
        try {
            $names = array_merge($names, $repo->findViewNames());
        } catch (\Exception $e) {
            unset($e);
        }

        return $names;
    }

    public function getColumnNamesForTable($sourceName, $table)
    {
        $repo = $this->getRepositoryForExternalSource($this->getExternalSource($sourceName));

        return $repo->findColumnNames($table);
    }

    public function loadUsersForExternalSource($sourceName)
    {
        $externalSource = $this->getExternalSource($sourceName);
        $repo = $this->getRepositoryForExternalSource($externalSource);

        return $repo->findUsers();
    }

    public function searchGroupsForExternalSource($sourceName, $search = null, $max = -1)
    {
        $externalSource = $this->getExternalSource($sourceName);
        $repo = $this->getRepositoryForExternalSource($externalSource);
        $groups = $repo->findGroups($search, $max);
        foreach ($groups as &$group) {
            $group['name'] = $this->utilities->stringToUtf8($group['name']);
            if (!empty($group['type'])) {
                $group['type'] = $this->utilities->stringToUtf8($group['type']);
            }
        }

        return $groups;
    }

    public function getExternalSourceGroupById($sourceName, $groupId)
    {
        $externalSource = $this->getExternalSource($sourceName);
        $repo = $this->getRepositoryForExternalSource($externalSource);
        $group = $repo->findOneGroupById($groupId);
        $group['name'] = $this->utilities->stringToUtf8($group['name']);
        if (!empty($group['type'])) {
            $group['type'] = $this->utilities->stringToUtf8($group['type']);
        }

        return $group;
    }

    public function synchronizeUsersForExternalSource(
        $sourceName,
        $synchronizeCas = false,
        $casSynchronizedField = 'username',
        Role $additionalRole = null,
        $batch = null
    ) {
        // Initialize parameters
        $batchSize = is_null($batch) ? self::USER_BATCH_SIZE_COMMAND : self::USER_BATCH_SIZE_BROWSER;
        $sourceName = $this->slugifyName($sourceName);
        $casSynchronizedFieldUcf = ucfirst($casSynchronizedField);
        // Get external source repository
        $externalSource = $this->getExternalSource($sourceName);
        $externalSourceRepo = $this->getRepositoryForExternalSource($externalSource);
        // Get additional role if set
        $rolesToAdd = [];
        if (!is_null($additionalRole)) {
            $rolesToAdd = [$additionalRole];
        }
        // Count users in external source to synchronize
        $countUsers = $externalSourceRepo->countUsers(true);
        // Return object
        $returnObj = ['next' => false, 'synced' => true, 'first' => 0, 'last' => $countUsers];
        if (!is_null($batch)) {
            $countUsers -= ($batch - 1) * $batchSize;
        }
        $this->log("Synchronizing {$countUsers} users for source '{$externalSource['name']}'");
        // While there are still users to sync
        $cnt = (!is_null($batch)) ? max(0, $batch - 1) : 0;
        // Liste of already examined usernames and mails
        $existingCasUsers = [];
        $existingCasIds = [];
        $existingCasUsernames = [];
        $existingCasUserIds = [];
        // List with already processed usernames and emails
        $alreadyProcessedUserUsernames = [];
        $alreadyProcessedUserEmails = [];
        $this->om->allowForceFlush(false);
        while ($countUsers > 0) {
            // Start flash suite
            $this->om->startFlushSuite();
            // Current batch size
            $curBatchSize = min($batchSize, $countUsers);
            $firstUserIndex = $cnt * $batchSize;
            $lastUserIndex = $firstUserIndex + $curBatchSize;
            $this->log("Syncing users {$firstUserIndex} -> {$lastUserIndex}");
            // Get users from external source
            $externalSourceUsers = $externalSourceRepo->findUsers($batchSize, $cnt, true);
            $externalSourceUserIds = array_column($externalSourceUsers, 'id');
            $externalSourceUserUsernames = array_column($externalSourceUsers, 'username');
            $externalSourceUserEmails = array_column($externalSourceUsers, 'email');
            // Get already synchronized users
            $alreadyImportedUsers = $this
                ->externalUserManager
                ->getExternalUsersByExternalIdsAndSourceSlug($externalSourceUserIds, $sourceName);
            $alreadyImportedUserIds = array_map(
                function (ExternalUser $extUser) {
                    return $extUser->getExternalUserId();
                },
                $alreadyImportedUsers
            );
            // Get already existing users by username or mail in platform
            $existingPlatformUsers = $this
                ->userManager
                ->getUsersByUsernamesOrMails($externalSourceUserUsernames, $externalSourceUserEmails, true);
            $existingPlatformUserUsernames = array_map(
                function (User $user) {
                    return strtoupper($user->getUsername());
                },
                $existingPlatformUsers
            );
            $existingPlatformUserMails = array_map(
                function (User $user) {
                    return strtoupper($user->getMail());
                },
                $existingPlatformUsers
            );
            $existingPlatformUserIds = array_map(
                function (User $user) {
                    return $user->getId();
                },
                $existingPlatformUsers
            );
            // If CAS enabled get existing user in CAS
            if (
                $synchronizeCas &&
                !is_null($this->casManager) &&
                isset(${"externalSourceUser${casSynchronizedFieldUcf}s"})
            ) {
                $existingCasUsers = $this
                    ->casManager
                    ->getCasUsersByCasIdsOrUserIds(
                        ${"externalSourceUser${casSynchronizedFieldUcf}s"},
                        $existingPlatformUserIds
                    );
                $existingCasIds = array_map(
                    function ($casUser) {
                        return strtoupper($casUser->getCasId());
                    },
                    $existingCasUsers
                );
                $existingCasUsernames = array_map(
                    function ($casUser) {
                        return strtoupper($casUser->getUser()->getUsername());
                    },
                    $existingCasUsers
                );
                $existingCasUserIds = array_map(
                    function ($casUser) {
                        return strtoupper($casUser->getUser()->getId());
                    },
                    $existingCasUsers
                );
            }
            // List with already used public urls
            $publicUrlList = [];
            // For every user
            foreach ($externalSourceUsers as $externalSourceUser) {
                $externalSourceUser['username'] = $this->utilities->stringToUtf8($externalSourceUser['username']);
                // If user already examined, ommit user
                if (
                    in_array(strtoupper($externalSourceUser['username']), $alreadyProcessedUserUsernames) ||
                    in_array($externalSourceUser['email'], $alreadyProcessedUserEmails)
                ) {
                    continue;
                }
                $alreadyProcessedUserUsernames[] = strtoupper($externalSourceUser['username']);
                $alreadyProcessedUserEmails[] = $externalSourceUser['email'];
                $this->log("Syncing user: {$externalSourceUser['username']}");
                $alreadyImportedUser = null;
                $casAccount = null;
                // Test if user already imported
                if (($key = array_search($externalSourceUser['id'], $alreadyImportedUserIds)) !== false) {
                    $alreadyImportedUser = $alreadyImportedUsers[$key];
                }
                // If user not already imported test if a CAS account is connected to it
                if (
                    is_null($alreadyImportedUser) &&
                    !empty($existingCasUsers) &&
                    ($key = array_search(
                        strtoupper($externalSourceUser[$casSynchronizedField]), $existingCasIds)
                    ) !== false
                ) {
                    $casAccount = $existingCasUsers[$key];
                    $alreadyImportedUser = $this->externalUserManager->createExternalUser(
                        $externalSourceUser['id'],
                        $sourceName,
                        $casAccount->getUser()
                    );
                }
                // If user mail exists already in platform then link with this account
                if (
                    is_null($alreadyImportedUser) &&
                    !empty($existingPlatformUserMails) &&
                    ($key = array_search(strtoupper($externalSourceUser['email']), $existingPlatformUserMails)) !== false
                ) {
                    $platformUser = $existingPlatformUsers[$key];
                    $alreadyImportedUser = $this->externalUserManager->createExternalUser(
                        $externalSourceUser['id'],
                        $sourceName,
                        $platformUser
                    );
                }
                $user = is_null($alreadyImportedUser) ? null : $alreadyImportedUser->getUser();
                // If user doesn't exist create it
                if (is_null($user)) {
                    // Otherwise create new user
                    $user = new User();
                    // Search if username exists
                    $username = $externalSourceUser['username'];
                    if (in_array(strtoupper($username), $existingPlatformUserUsernames)) {
                        $username .= uniqid();
                    }
                    $user->setUsername($username);
                    $user->setIsMailValidated(true);
                    $user->setPlainPassword(bin2hex(random_bytes(10)));
                }
                // Update or set user values
                $user->setFirstName($this->utilities->stringToUtf8($externalSourceUser['first_name']));
                $user->setLastName($this->utilities->stringToUtf8($externalSourceUser['last_name']));
                $user->setMail($externalSourceUser['email']);
                if (!empty($externalSourceUser['code'])) {
                    $user->setAdministrativeCode($this->utilities->stringToUtf8($externalSourceUser['code']));
                }
                if (is_null($alreadyImportedUser)) {
                    $publicUrl = $this->userManager->generatePublicUrl($user);
                    $publicUrl .= in_array($publicUrl, $publicUrlList) ? '_'.uniqid() : '';
                    $publicUrlList[] = $publicUrl;
                    $this->userManager->createUser($user, false, $rolesToAdd, null, $publicUrl);
                    $this->externalUserManager->createExternalUser(
                        $externalSourceUser['id'],
                        $sourceName,
                        $user
                    );
                } else {
                    if ($additionalRole !== null && !$user->hasRole($additionalRole->getName())) {
                        $user->addRole($additionalRole);
                    }
                    $this->externalUserManager->updateExternalUserDate($alreadyImportedUser);
                    $this->om->persist($user);
                }
                // If cas enabled and user doesn't exist in CAS create cas user
                if (
                    !is_null($this->casManager) &&
                    $synchronizeCas &&
                    !in_array(strtoupper($externalSourceUser[$casSynchronizedField]), $existingCasIds) &&
                    !in_array(strtoupper($externalSourceUser['username']), $existingCasUsernames) &&
                    (empty($user->getId()) || !in_array($user->getId(), $existingCasUserIds))
                ) {
                    $this->casManager->createCasUser($externalSourceUser[$casSynchronizedField], $user);
                }
            }
            $this->om->endFlushSuite();
            $this->om->clear();

            $countUsers -= $curBatchSize;
            if (!is_null($batch)) {
                $next = ($countUsers > 0) ? $batch + 1 : false;
                $returnObj['next'] = $next;
                $returnObj['first'] = $firstUserIndex;
                $returnObj['last'] = $lastUserIndex;
                $countUsers = 0;
            }
            ++$cnt;
        }
        $this->log('All users have been synchronized');
        $this->om->allowForceFlush(true);
        unset($casSynchronizedFieldUcf);

        return $returnObj;
    }

    public function countExternalSourceUsers($externalSource)
    {
        $externalSourceRepo = $this->getRepositoryForExternalSource($externalSource);

        return $externalSourceRepo->countUsers(true);
    }

    public function syncrhonizeAllGroupsForExternalSource($sourceName, $forceUnsubscribe = true)
    {
        $sourceName = $this->slugifyName($sourceName);
        // Get external source repository
        $groups = $this->externalGroupManager->getExternalGroupsBySourceSlug($sourceName);
        $this->log('Synchronizing '.count($groups).' groups for '.$sourceName);
        $this->om->allowForceFlush(false);
        foreach ($groups as $group) {
            $this->syncrhonizeGroupForExternalSource($sourceName, $group, $forceUnsubscribe);
        }
        $this
            ->om
            ->getRepository('ClarolineExternalSynchronizationBundle:ExternalGroup')
            ->deactivateGroupsForSource($sourceName);
        $this->log('All groups have been synchronized');
        $this->om->allowForceFlush(true);
    }

    public function syncrhonizeGroupForExternalSource($sourceName, ExternalGroup $extGroup, $forceUnsubscribe = true)
    {
        $externalSource = $this->getExternalSource($sourceName);
        $externalSourceRepo = $this->getRepositoryForExternalSource($externalSource);
        $group = $extGroup->getGroup();
        $this->log('Synchronizing group '.$group->getName());
        // Get all user ids subscribed to external source group
        $externalSourceUserIds = $externalSourceRepo->findUserIdsByGroupId($extGroup->getExternalGroupId());
        if (empty($externalSourceUserIds)) {
            $this->log('Group '.$group->getName().' has no users, abort syncing...');

            return;
        }
        $externalUsers = $this
            ->externalUserManager
            ->getExternalUsersByExternalIdsAndSourceSlug($externalSourceUserIds, $sourceName);
        // Get all external users already subscribed to group
        $subscribedUserIds = $group->getUserIds();
        // For each external user, subscribe him to group if not already subscribed
        $alreadySubscribedIds = [];

        $this->om->startFlushSuite();
        foreach ($externalUsers as $externalUser) {
            $user = $externalUser->getUser();
            if (!in_array($user->getId(), $subscribedUserIds)) {
                $user->addGroup($group);
                $this->om->persist($user);
            } else {
                $alreadySubscribedIds[] = $user->getId();
            }
        }
        if ($forceUnsubscribe) {
            $unsubscribeUserIds = array_diff($subscribedUserIds, $alreadySubscribedIds);
            $unsubscribedUsers = $this->userManager->getUsersByIds($unsubscribeUserIds);
            foreach ($unsubscribedUsers as $user) {
                $group->removeUser($user);
            }
            $this->om->persist($group);
        }
        $this->externalGroupManager->updateExternalGroupDate($extGroup);
        $this->om->endFlushSuite();
        $this->log('Group '.$group->getName().' has been synced.');
    }

    public function saveConfig()
    {
        return file_put_contents($this->syncFilePath, $this->ymlDumper->dump($this->sourcesArray, 3));
        if (!empty($this->sourcesArray['sources'])) {
        }

        return false;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->om->setLogger($logger);
        $this->om->activateLog();
    }

    private function loadExternalSources()
    {
        $fs = new Filesystem();
        if (!$fs->exists($this->syncFilePath)) {
            if (!$fs->exists(dirname($this->syncFilePath))) {
                $fs->mkdir(dirname($this->syncFilePath), 0775);
                $fs->chmod(dirname($this->syncFilePath), 0775);
            }
            $fs->touch($this->syncFilePath);
        }
        $yml = $this->ymlParser->parse(file_get_contents($this->syncFilePath));

        return empty($yml) ? ['sources' => []] : $yml;
    }

    private function slugifyName($name)
    {
        return $this->slugify->slugify($name, '_');
    }

    private function getRepositoryForExternalSource($resourceConfig)
    {
        return new ExternalResourceSynchronizationRepository($resourceConfig);
    }

    private function isPropertyConfigured($source, $filters)
    {
        $isConfigured = true;
        foreach ($filters as $filter) {
            if (!array_key_exists($filter, $source)) {
                $isConfigured = false;
                break;
            }
        }

        return $isConfigured;
    }
}
