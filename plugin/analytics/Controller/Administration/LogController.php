<?php

namespace Claroline\AnalyticsBundle\Controller\Administration;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\LogManager;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/tools/admin/logs")
 * @SEC\PreAuthorize("canOpenAdminTool('dashboard')")
 */
class LogController
{
    /** @var FinderProvider */
    private $finder;

    /** @var SerializerProvider */
    private $serializer;

    /** @var LogManager */
    private $logManager;

    /** @var User */
    private $loggedUser;

    /**
     * LogController constructor.
     *
     * @param FinderProvider     $finder
     * @param SerializerProvider $serializer
     * @param LogManager         $logManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder,
        SerializerProvider $serializer,
        LogManager $logManager
    ) {
        $this->loggedUser = $tokenStorage->getToken()->getUser();
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->logManager = $logManager;
    }

    /**
     * Get the name of the managed entity.
     *
     * @return string
     */
    public function getName()
    {
        return 'log';
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/", name="apiv2_admin_tool_logs_list")
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        $query = $this->addOrganizationFilter($request->query->all());

        return new JsonResponse($this->finder->search(
            $this->getClass(),
            $query,
            []
        ));
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @Route("/csv", name="apiv2_admin_tool_logs_list_csv")
     * @Method("GET")
     */
    public function listCsvAction(Request $request)
    {
        // Filter data, but return all of them
        $query = $this->addOrganizationFilter($request->query->all());
        $dateStr = date('YmdHis');

        return new StreamedResponse(function () use ($query) {
            $this->logManager->exportLogsToCsv($query);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="actions_'.$dateStr.'.csv"',
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/chart", name="apiv2_admin_tool_logs_list_chart")
     * @Method("GET")
     */
    public function listChartAction(Request $request)
    {
        $query = $this->addOrganizationFilter($request->query->all());
        $chartData = $this->logManager->getChartData($query);

        return new JsonResponse($chartData);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/users", name="apiv2_admin_tool_logs_list_users")
     * @Method("GET")
     */
    public function userActionsListAction(Request $request)
    {
        $query = $this->addOrganizationFilter($request->query->all());
        $userList = $this->logManager->getUserActionsList($query);

        return new JsonResponse($userList);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @Route("/users/csv", name="apiv2_admin_tool_logs_list_users_csv")
     * @Method("GET")
     */
    public function userActionsListCsvAction(Request $request)
    {
        // Filter data, but return all of them
        $query = $this->addOrganizationFilter($request->query->all());
        $dateStr = date('YmdHis');

        return new StreamedResponse(function () use ($query) {
            $this->logManager->exportUserActionToCsv($query);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="user_actions_'.$dateStr.'.csv"',
        ]);
    }

    /**
     * @param Log $log
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/{id}", name="apiv2_admin_tool_logs_get", requirements={"id"="\d+"})
     * @Method("GET")
     *
     * @ParamConverter("log", class="Claroline\CoreBundle\Entity\Log\Log")
     */
    public function getAction(Log $log)
    {
        return new JsonResponse($this->serializer->serialize($log, ['details' => true]));
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Log\Log';
    }

    private function addOrganizationFilter($query)
    {
        if (!$this->loggedUser->hasRole('ROLE_ADMIN')) {
            $query['hiddenFilters']['organization'] = $this->loggedUser->getAdministratedOrganizations();
        }

        return $query;
    }
}
