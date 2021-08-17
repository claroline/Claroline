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
use Claroline\CursusBundle\Manager\SessionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/cursus_quota")
 */
class QuotaController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SessionManager */
    private $sessionManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        SessionManager $sessionManager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->sessionManager = $sessionManager;
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
     * @Route("/{id}/statistics", name="apiv2_cursus_quota_statistics", methods={"GET"})
     * @EXT\ParamConverter("quota", class="Claroline\CursusBundle\Entity\Quota", options={"mapping": {"id": "uuid"}})
     */
    public function getStatisticsAction(Quota $quota): JsonResponse
    {
        $sessionUsers = $this->om->getRepository(SessionUser::class)->findByQuota($quota);

        return new JsonResponse([
            'total' => count($sessionUsers),
            'pending' => array_reduce($sessionUsers, function($accum, $subscription) {
                return $accum + (!$subscription->isValidated() && !$subscription->isManaged() && !$subscription->isRefused() ? 1 : 0);
            }, 0),
            'refused' => array_reduce($sessionUsers, function($accum, $subscription) {
                return $accum + (!$subscription->isValidated() && !$subscription->isManaged() && $subscription->isRefused() ? 1 : 0);
            }, 0),
            'validated' => array_reduce($sessionUsers, function($accum, $subscription) {
                return $accum + ($subscription->isValidated() && !$subscription->isManaged() && !$subscription->isRefused() ? 1 : 0);
            }, 0),
            'managed' => array_reduce($sessionUsers, function($accum, $subscription) {
                return $accum + ($subscription->isValidated() && $subscription->isManaged() && !$subscription->isRefused() ? 1 : 0);
            }, 0),
            'calculated' => array_reduce($sessionUsers, function($accum, $subscription) {
                if (!$subscription->isManaged()) {
                    return $accum;
                }

                $session = $subscription->getSession();
                return $accum + $session->getQuotaDays() + 1 / 24 * $session->getQuotaHours();
            }, 0)
        ]);
    }

    /**
     * @Route("/{id}/subscriptions", name="apiv2_cursus_quota_list_subscriptions", methods={"GET"})
     * @EXT\ParamConverter("quota", class="Claroline\CursusBundle\Entity\Quota", options={"mapping": {"id": "uuid"}})
     */
    public function listSubscriptionsAction(Quota $quota, Request $request): JsonResponse
    {
        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'organization' => $quota->getOrganization(),
            'used_by_quotas' => true
        ];

        $options = isset($query['options']) ? $query['options'] : [];

        return new JsonResponse(
            $this->finder->search(SessionUser::class, $query, $options)
        );
    }

    /**
     * @Route("/subscriptions/{id}", name="apiv2_cursus_subscription_status", methods={"PATCH"})
     * @EXT\ParamConverter("sessionUser", class="Claroline\CursusBundle\Entity\Registration\SessionUser", options={"mapping": {"id": "uuid"}})
     */
    public function setSubscriptionStatus(SessionUser $sessionUser, Request $request): JsonResponse
    {
        $status = $request->query->get('status', null);
        if (!$status) {
            return new JsonResponse('The status is missing.', 401);
        }

        $STATUS = [
            'managed' => [
                'managed' => true,
                'validated' => true,
                'refused' => false
            ],
            'validated' => [
                'managed' => false,
                'validated' => true,
                'refused' => false
            ],
            'refused' => [
                'managed' => false,
                'validated' => false,
                'refused' => true
            ],
            'pending' => [
                'managed' => false,
                'validated' => false,
                'refused' => false
            ]
        ];
        if (!isset($STATUS[$status])) {
            return new JsonResponse('The status don\'t have been updated.', 401);
        }

        // Execute action, dispatch event, send mail, etc
        switch ($status) {
            case 'validated':
                $this->sessionManager->addUsers($sessionUser->getSession(), [ $sessionUser->getUser() ]);
                break;
            case 'refused':
                $this->sessionManager->removeUsers($sessionUser->getSession(), [ $sessionUser ]);
                break;
            case 'pending':
                $this->sessionManager->removeUsers($sessionUser->getSession(), [ $sessionUser ]);
                break;
        }

        // Set flags in DB
        $flags = $STATUS[$status];

        $sessionUser->setManaged($flags['managed']);
        $sessionUser->setValidated($flags['validated']);
        $sessionUser->setRefused($flags['refused']);

        $this->om->persist($sessionUser);
        $this->om->flush();

        return new JsonResponse([
            'subscription' => $this->serializer->serialize($sessionUser),
        ]);
    }

    /**
     * @Route("/{id}/open", name="apiv2_cursus_quota_open", methods={"GET"})
     * @EXT\ParamConverter("quota", class="Claroline\CursusBundle\Entity\Quota", options={"mapping": {"id": "uuid"}})
     */
    public function openAction(Quota $quota): JsonResponse
    {
        return new JsonResponse([
            'quota' => $this->serializer->serialize($quota),
        ]);
    }
}
