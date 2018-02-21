<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 5/17/17
 */

namespace Claroline\ExternalSynchronizationBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Repository\GroupRepository;
use Claroline\ExternalSynchronizationBundle\Entity\ExternalGroup;
use Claroline\ExternalSynchronizationBundle\Repository\ExternalGroupRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ExternalSynchronizationManager.
 *
 * @DI\Service("claroline.manager.external_group_sync_manager")
 */
class ExternalSynchronizationGroupManager
{
    /** @var ObjectManager */
    private $om;
    /** @var GroupManager */
    private $groupManager;
    /** @var GroupRepository */
    private $groupRepo;
    /** @var ExternalGroupRepository */
    private $externalGroupRepo;
    /** @var PagerFactory */
    private $pagerFactory;

    /**
     * @DI\InjectParams({
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "groupManager"           = @DI\Inject("claroline.manager.group_manager"),
     *     "pagerFactory"           = @DI\Inject("claroline.pager.pager_factory")
     * })
     *
     * @param ObjectManager $om
     * @param GroupManager  $groupManager
     * @param PagerFactory  $pagerFactory
     */
    public function __construct(
        ObjectManager $om,
        GroupManager $groupManager,
        PagerFactory $pagerFactory
    ) {
        $this->om = $om;
        $this->groupManager = $groupManager;
        $this->pagerFactory = $pagerFactory;
        $this->externalGroupRepo = $om->getRepository('ClarolineExternalSynchronizationBundle:ExternalGroup');
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
    }

    public function getExternalGroupById($id)
    {
        return $this->externalGroupRepo->findOneById($id);
    }

    public function getExternalGroupByExternalIdAndSourceSlug($externalId, $sourceSlug)
    {
        return $this->externalGroupRepo->findOneBy(['externalGroupId' => $externalId, 'sourceSlug' => $sourceSlug]);
    }

    public function getExternalGroupsByRolesAndSearch(
        array $roles,
        $search = null,
        $page = 1,
        $max = 50,
        $orderedBy = 'name',
        $order = 'ASC'
    ) {
        $query = $this->externalGroupRepo->findByRolesAndSearch($roles, $search, $orderedBy, $order, false);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    public function importExternalGroup($externalGroupId, $roles, $source, $name, $code = null)
    {
        $internalGroup = new Group();
        $internalGroup->setName($this->createValidName($name, $code));
        $internalGroup->setPlatformRoles($roles);
        $this->groupManager->insertGroup($internalGroup);
        $externalGroup = new ExternalGroup($externalGroupId, $source, $internalGroup);
        $this->om->persist($externalGroup);
        $this->om->flush();

        return $externalGroup;
    }

    public function getExternalGroupsBySourceSlug($sourceSlug)
    {
        return $this->externalGroupRepo->findBy(['sourceSlug' => $sourceSlug]);
    }

    public function updateExternalGroupDate(ExternalGroup $externalGroup)
    {
        if ($externalGroup->updateLastSynchronizationDate()) {
            $this->om->persist($externalGroup);
            $this->om->flush();
        }
    }

    public function searchExternalGroupsForSource(
        $source,
        $page = 1,
        $max = 50,
        $orderBy = 'name',
        $direction = 'ASC',
        $search = ''
    ) {
        return $this->externalGroupRepo->searchForSourcePaginated($source, $page, $max, $orderBy, $direction, $search);
    }

    public function countExternalGroupsForSourceAndSearch($source, $search = '')
    {
        return $this->externalGroupRepo->countBySearchForSource($source, $search);
    }

    public function countUsersInExternalGroup(ExternalGroup $externalGroup)
    {
        return $this->externalGroupRepo->countUsersInGroup($externalGroup);
    }

    public function deleteGroupsForExternalSource($source)
    {
        $this->externalGroupRepo->deleteBySourceSlug($source);
    }

    public function updateGroupsExternalSourceName($oldSource, $newSource)
    {
        $this->externalGroupRepo->updateSourceSlug($oldSource, $newSource);
    }

    private function createValidName($name, $code)
    {
        $validName = $name;
        // Is the name already used ?
        if (count($this->groupRepo->findByName($name)) > 0) {
            if ($code) {
                $validName .= " [$code]";
            } else {
                $date = date('YmdHis');
                $validName = "[ext-$date] ".$name;
            }
        }

        return $validName;
    }
}
