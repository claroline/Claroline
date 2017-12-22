<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 2/19/15
 */

namespace Icap\InwicastBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Icap\InwicastBundle\Entity\MediaCenter;
use Icap\InwicastBundle\Repository\MediaCenterUserRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class MediacenterUserManager.
 *
 * @DI\Service("inwicast.plugin.manager.mediacenteruser")
 */
class MediaCenterUserManager
{
    /**
     * @var \Icap\InwicastBundle\Repository\MediaCenterUserRepository
     */
    private $mediacenterUserRepository;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    private $session;

    /**
     * @DI\InjectParams({
     *      "mediacenterUserRepository"     = @DI\Inject("inwicast.plugin.repository.mediacenteruser"),
     *      "session"                       = @DI\Inject("session")
     * })
     */
    public function __construct(
        MediaCenterUserRepository $mediacenterUserRepository,
        Session $session
    ) {
        $this->mediacenterUserRepository = $mediacenterUserRepository;
        $this->session = $session;
    }

    public function getMediacenterUserToken(User $user, MediaCenter $mediacenter)
    {
        $hasInwicastToken = $this->session->get('has_inwicast_token');
        $token = $this->session->getId();
        if (!$hasInwicastToken) {
            $this->mediacenterUserRepository->createInwicastUserIfNotExists($user, $token, $mediacenter);
            $this->session->set('has_inwicast_token', true);
        }

        return $token;
    }
}
