<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\ForumBundle\Entity\Category;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Manager\Manager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * ForumController.
 */
class ForumController extends Controller
{
    private $authorization;
    private $forumManager;
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "forumManager"  = @DI\Inject("claroline.manager.forum_manager"),
     *     "tokenStorage"  = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        Manager $forumManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorization = $authorization;
        $this->forumManager = $forumManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @EXT\Route(
     *     "/{forum}/category",
     *     name="claro_forum_categories",
     *     defaults={"page"=1}
     * )
     * @EXT\Template("ClarolineForumBundle::index.html.twig")
     *
     * @param Forum $forum
     * @param User  $user
     */
    public function openAction(Forum $forum)
    {
        $em = $this->getDoctrine()->getManager();
        $this->checkAccess($forum);
        $categories = $em->getRepository('ClarolineForumBundle:Forum')->findCategories($forum);
        $user = $this->tokenStorage->getToken()->getUser();
        $hasSubscribed = 'anon.' === $user ?
            false :
            $this->forumManager->hasSubscribed($user, $forum);
        $isModerator = $this->authorization->isGranted(
            'moderate',
            new ResourceCollection([$forum->getResourceNode()])
        ) && 'anon.' !== $user;

        return [
            'search' => null,
            '_resource' => $forum,
            'isModerator' => $isModerator,
            'categories' => $categories,
            'hasSubscribed' => $hasSubscribed,
            'workspace' => $forum->getResourceNode()->getWorkspace(),
        ];
    }
}
