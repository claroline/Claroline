<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.cursus_api_manager")
 */
class CursusApiManager
{
    private $apiManager;
    private $om;

    private $friendRepo;

    /**
     * @DI\InjectParams({
     *     "apiManager" = @DI\Inject("claroline.manager.api_manager"),
     *     "om"         = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ApiManager $apiManager,
        ObjectManager $om
    ) {
        $this->apiManager = $apiManager;
        $this->om = $om;

        $this->friendRepo = $this->om->getRepository('Claroline\CoreBundle\Entity\Oauth\FriendRequest');
    }

    public function getRemoteCursus($platformName, $cursusId = null)
    {
        $targetPlatformUrl = $this->friendRepo->findOneByName($platformName);
        $url = is_null($cursusId) ?
            'clarolinecursusbundle/api/all/cursus.json' :
            'clarolinecursusbundle/api/cursuses/'.$cursusId.'.json';
        $serverOutput = $this->apiManager->url($targetPlatformUrl, $url);

        return json_decode($serverOutput, true);
    }

    public function getRemoteCourses($platformName)
    {
        $targetPlatformUrl = $this->friendRepo->findOneByName($platformName);
        $url = 'clarolinecursusbundle/api/course.json';
        $serverOutput = $this->apiManager->url($targetPlatformUrl, $url);

        return json_decode($serverOutput, true);
    }

    public function registerUserToCursusHierarchy($platformName, User $user, $cursusId)
    {
        $targetPlatformUrl = $this->friendRepo->findOneByName($platformName);
        $url = 'clarolinecursusbundle/api/users/'.
            $user->getId().
            '/tos/'.
            $cursusId.
            '/cursus/hierarchy/add.json';
        $serverOutput = $this->apiManager->url($targetPlatformUrl, $url);

        return json_decode($serverOutput, true);
    }
}
