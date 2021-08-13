<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CursusBundle\Entity\Quota;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/cursus_quota")
 */
class QuotaController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
    }

    public function getName()
    {
        return 'cursus_quota';
    }

    public function getClass()
    {
        return Quota::class;
    }

    public function getIgnore()
    {
        return ['copyBulk', 'doc', 'exist'];
    }
    
    /**
     * @Route("/", name="apiv2_cursus_quota_list", methods={"GET"})
     */
    public function listAction(Request $request, $class = Quota::class): JsonResponse
    {
        $query = $request->query->all();
        $query['hiddenFilters'] = $this->getDefaultHiddenFilters();

        $options = isset($query['options']) ? $query['options'] : [];
        $options[] = Options::SERIALIZE_MINIMAL;

        return new JsonResponse(
            $this->finder->search($class, $query, $options)
        );
    }

    /**
     * @Route("/{id}/subscriptions", name="apiv2_cursus_quota_list_subscriptions", methods={"GET"})
     * @EXT\ParamConverter("quota", class="Claroline\CursusBundle\Entity\Quota", options={"mapping": {"id": "uuid"}})
     */
    public function listSubscriptionsAction(Quota $quota, Request $request): JsonResponse
    {
        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'organization' => $quota->getOrganization()
        ];

        $options = isset($query['options']) ? $query['options'] : [];

        return new JsonResponse(
            $this->finder->search(SessionUser::class, $query, $options)
        );
    }
    
    /**
     * @Route("subscriptions/{id}", name="apiv2_cursus_subscription_status", methods={"PATCH"})
     * @EXT\ParamConverter("sessionUser", class="Claroline\CursusBundle\Entity\Registration\SessionUser", options={"mapping": {"id": "uuid"}})
     */
    public function setSubscriptionStatus(SessionUser $sessionUser, Request $request): JsonResponse
    {
        $status = $request->query->get('status', null);
        if (!$status) return new JsonResponse('The status is missing.', 401);

        $validated = ['pending' => false, 'validated' => true, 'managed' => true, 'refused' => false];
        $managed = ['pending' => false, 'validated' => false, 'managed' => true, 'refused' => false];
        $refused = ['pending' => false, 'validated' => false, 'managed' => false, 'refused' => true];
        if (!isset($validated[$status])) return new JsonResponse('The status don\'t have been updated.', 401);

        $sessionUser->setValidated($validated[$status]);
        $sessionUser->setManaged($managed[$status]);
        $sessionUser->setRefused($refused[$status]);

        $this->om->persist($sessionUser);
        $this->om->flush();

        return new JsonResponse([
            'subscription' => $this->serializer->serialize($sessionUser)
        ]);
    }

    /**
     * @Route("/{id}/open", name="apiv2_cursus_quota_open", methods={"GET"})
     * @EXT\ParamConverter("quota", class="Claroline\CursusBundle\Entity\Quota", options={"mapping": {"id": "uuid"}})
     */
    public function openAction(Quota $quota): JsonResponse
    {
        return new JsonResponse([
            'quota' => $this->serializer->serialize($quota)
        ]);
    }
}
