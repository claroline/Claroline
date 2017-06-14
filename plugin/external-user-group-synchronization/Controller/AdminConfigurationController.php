<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 4/11/17
 */

namespace Claroline\ExternalSynchronizationBundle\Controller;

use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\ExternalSynchronizationBundle\Form\ExternalSourceConfigurationType;
use Claroline\ExternalSynchronizationBundle\Manager\ExternalSynchronizationGroupManager;
use Claroline\ExternalSynchronizationBundle\Manager\ExternalSynchronizationManager;
use Claroline\ExternalSynchronizationBundle\Manager\ExternalSynchronizationUserManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ExternalUserGroupSynchronizationAdminController.
 *
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
 */
class AdminConfigurationController extends Controller
{
    /**
     * @var ExternalSynchronizationManager
     * @DI\Inject("claroline.manager.external_user_group_sync_manager")
     */
    private $externalUserGroupSyncManager;

    /**
     * @var ExternalSynchronizationGroupManager
     * @DI\Inject("claroline.manager.external_group_sync_manager")
     */
    private $externalGroupManager;

    /**
     * @var ExternalSynchronizationUserManager
     * @DI\Inject("claroline.manager.external_user_sync_manager")
     */
    private $externalUserManager;

    /**
     * @var RoleManager
     * @DI\Inject("claroline.manager.role_manager")
     */
    private $roleManager;

    private $translator;

    /**
     * @DI\InjectParams({
     *     "translator"     = @DI\Inject("translator"),
     * })
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @EXT\Route("/",
     *     options={"expose"=true},
     *     name="claro_admin_external_sync_config_index")
     * @EXT\Template("ClarolineExternalSynchronizationBundle:Configuration:index.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $sources = $this->externalUserGroupSyncManager->getExternalSourceList();

        return ['sources' => $sources];
    }

    /**
     * @EXT\Route("/new", name="claro_admin_external_sync_new_source_form")
     * @EXT\Method({ "GET" })
     * @EXT\Template("ClarolineExternalSynchronizationBundle:Configuration:newSource.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newSourceAction()
    {
        $form = $this->createForm(new ExternalSourceConfigurationType());

        return ['form' => $form->createView()];
    }

    /**
     * @EXT\Route("/new", name="claro_admin_external_sync_post_new_source")
     * @EXT\Method({ "POST" })
     * @EXT\Template("ClarolineExternalSynchronizationBundle:Configuration:newSource.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postNewSourceAction(Request $request)
    {
        $form = $this->createForm(new ExternalSourceConfigurationType());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $config = $form->getData();
            $this->externalUserGroupSyncManager->setExternalSource($config['name'], $config);

            return $this->redirectToRoute('claro_admin_external_sync_config_index');
        }

        return ['form' => $form->createView()];
    }

    /**
     * @EXT\Route("/edit/{source}",
     *     options={"expose"=true},
     *     name="claro_admin_external_sync_edit_source_form"
     * )
     * @EXT\Template("ClarolineExternalSynchronizationBundle:Configuration:editSource.html.twig")
     */
    public function editSourceAction(Request $request, $source)
    {
        $sourceConfig = $this->externalUserGroupSyncManager->getExternalSource($source);
        $form = $this->createForm(new ExternalSourceConfigurationType(), $sourceConfig);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $config = $form->getData();
            $sources = $this->externalUserGroupSyncManager->getExternalSourcesNames();
            if ($form->isValid() && !in_array($config['name'], $sources)) {
                $this->externalUserGroupSyncManager->setExternalSource($config['name'], $config, $source);

                return $this->redirectToRoute('claro_admin_external_sync_config_index');
            } elseif (in_array($config['name'], $sources)) {
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', $this->translator->trans('name_not_unique', [], 'claro_external_user_group'));
            }
        }

        return [
            'sourceConfig' => $sourceConfig,
            'source' => $source,
            'form' => $form->createView(),
        ];
    }

    /**
     * @EXT\Route("/edit/user/{source}",
     *     options={"expose"=true},
     *     name="claro_admin_external_sync_source_user_configuration_form"
     * )
     * @EXT\Method({ "GET" })
     * @EXT\Template("ClarolineExternalSynchronizationBundle:Configuration:userConfiguration.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userConfigurationForSourceAction($source)
    {
        $sourceConfig = $this->externalUserGroupSyncManager->getExternalSource($source);
        $tableNames = $this->externalUserGroupSyncManager->getTableAndViewNames($source);
        $fieldNames = isset($sourceConfig['user_config']['table'])
            ? $this->externalUserGroupSyncManager->getColumnNamesForTable($source, $sourceConfig['user_config']['table'])
            : null;

        return [
            'fieldNames' => $fieldNames,
            'sourceConfig' => $sourceConfig,
            'source' => $source,
            'tableNames' => $tableNames,
        ];
    }

    /**
     * @EXT\Route("/edit/group/{source}",
     *     options={"expose"=true},
     *     name="claro_admin_external_sync_source_group_configuration_form"
     * )
     * @EXT\Method({ "GET" })
     * @EXT\Template("ClarolineExternalSynchronizationBundle:Configuration:groupConfiguration.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupConfigurationForSourceAction($source)
    {
        $sourceConfig = $this->externalUserGroupSyncManager->getExternalSource($source);
        $tableNames = $this->externalUserGroupSyncManager->getTableAndViewNames($source);
        $groupFieldNames = isset($sourceConfig['group_config']['table'])
            ? $this->externalUserGroupSyncManager->getColumnNamesForTable($source, $sourceConfig['group_config']['table'])
            : null;
        $userGroupFieldNames = isset($sourceConfig['group_config']['user_group_config']['table'])
            ? $this->externalUserGroupSyncManager->getColumnNamesForTable($source, $sourceConfig['group_config']['user_group_config']['table'])
            : null;

        return [
            'groupFieldNames' => $groupFieldNames,
            'userGroupFieldNames' => $userGroupFieldNames,
            'sourceConfig' => $sourceConfig,
            'source' => $source,
            'tableNames' => $tableNames,
        ];
    }

    /**
     * @EXT\Route("/edit/user/{source}",
     *     options={"expose"=true},
     *     name="claro_admin_external_sync_source_update_user_configuration")
     * @EXT\Method({ "POST" })
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateUserConfigurationForSourceAction(Request $request, $source)
    {
        return $this->updateConfigurationForSourceAction($request, $source, 'user_config_update_success');
    }

    /**
     * @EXT\Route("/edit/group/{source}",
     *     options={"expose"=true},
     *     name="claro_admin_external_sync_source_update_group_configuration")
     * @EXT\Method({ "POST" })
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateGroupConfigurationForSourceAction(Request $request, $source)
    {
        return $this->updateConfigurationForSourceAction($request, $source, 'group_config_update_success');
    }

    private function updateConfigurationForSourceAction(Request $request, $source, $message)
    {
        $sourceConfig = $request->get('sourceConfig');

        $this->externalUserGroupSyncManager->setExternalSource($sourceConfig['source'], $sourceConfig['data']);

        $request->getSession()
            ->getFlashBag()
            ->add('success', $this->translator->trans($message, [], 'claro_external_user_group'));

        return new JsonResponse(['updated' => true], 200);
    }

    /**
     * @EXT\Route("/delete/{source}",
     *     options={"expose"=true},
     *     name="claro_admin_external_sync_delete_source"
     * )
     * @EXT\Method({ "DELETE" })
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteSourceAction($source)
    {
        $deleted = $this->externalUserGroupSyncManager->deleteExternalSource($source);

        return new JsonResponse(['deleted' => true], !$deleted ? 500 : 200);
    }

    /**
     * @param $source
     * @param $table
     * @EXT\Route("/{source}/{table}/columns",
     *     options={"expose"=true},
     *     name="claro_admin_external_sync_table_columns"
     * )
     * @EXT\Method({ "GET" })
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTableColumnsForSourceAction($source, $table)
    {
        $columns = $this->externalUserGroupSyncManager->getColumnNamesForTable($source, $table);

        return new JsonResponse($columns);
    }

    /**
     * @EXT\Route("/synchronize/{source}",
     *     options={"expose"=true},
     *     name="claro_admin_external_sync_source_synchronize"
     * )
     * @EXT\Template("ClarolineExternalSynchronizationBundle:Configuration:synchronization.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function synchronizeSourceAction($source)
    {
        $sourceConfig = $this->externalUserGroupSyncManager->getExternalSource($source, true);
        $platformRoles = $this->roleManager->getAllPlatformRoleNamesAndKeys();
        $sourceConfig['totalExternalUsers'] = 0;
        if (!empty($sourceConfig['user_config'])) {
            $sourceConfig['totalExternalUsers'] = $this->externalUserGroupSyncManager->countExternalSourceUsers($sourceConfig);
        }

        return [
            'platformRoles' => $platformRoles,
            'source' => $source,
            'sourceConfig' => $sourceConfig,
        ];
    }

    /**
     * @EXT\Route("/{source}/users/page/{page}/max/{max}/order/{orderBy}/direction/{direction}/search/{search}",
     *     defaults={"page"=1, "max"=50, "orderBy"="username", "direction"="ASC", "search"=""},
     *     options={"expose"=true},
     *     name="claro_admin_external_sync_source_search_users"
     * )
     * @EXT\Method({ "GET" })
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchUsersForSourceAction($source, $page, $max, $orderBy, $direction, $search)
    {
        $totalItems = $this->externalUserManager->countExternalUsersForSourceAndSearch($source, $search);
        $items = $this
            ->externalUserManager
            ->searchExternalUsersForSource($source, $page, $max, $orderBy, $direction, $search);

        return new JsonResponse(['totalItems' => $totalItems, 'items' => $items]);
    }

    /**
     * @EXT\Route("/{source}/users/synchronize/batch/{batch}/cas/{cas}/{casField}/role/{role}",
     *     defaults={"batch"=1, "role"=null, "cas"=true, "casField"="username"},
     *     options={"expose"=true},
     *     name="claro_admin_external_sync_source_users_synchronize"
     * )
     * @EXT\Method({ "GET" })
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function synchronizeUsersForSourceAction($source, $batch, $cas, $casField, $role = null)
    {
        if (!is_null($role)) {
            $role = $this->roleManager->getRoleByName($role);
        }
        $cas = filter_var($cas, FILTER_VALIDATE_BOOLEAN);
        $syncObj = $this
            ->externalUserGroupSyncManager
            ->synchronizeUsersForExternalSource($source, $cas, $casField, $role, $batch);

        return new JsonResponse($syncObj);
    }

    /**
     * @EXT\Route("/{source}/groups/page/{page}/max/{max}/order/{orderBy}/direction/{direction}/search/{search}",
     *     defaults={"page"=1, "max"=50, "orderBy"="username", "direction"="ASC", "search"=""},
     *     options={"expose"=true},
     *     name="claro_admin_external_sync_source_search_groups"
     * )
     * @EXT\Method({ "GET" })
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchGroupsForSourceAction($source, $page, $max, $orderBy, $direction, $search)
    {
        $totalItems = $this->externalGroupManager->countExternalGroupsForSourceAndSearch($source, $search);
        $items = $this
            ->externalGroupManager
            ->searchExternalGroupsForSource($source, $page, $max, $orderBy, $direction, $search);

        return new JsonResponse(['totalItems' => $totalItems, 'items' => $items]);
    }

    /**
     * @EXT\Route("/{source}/groups/synchronize/{groupId}/unsubscribe/{unsubscribe}",
     *     defaults={"unsubscribe"=true},
     *     options={"expose"=true},
     *     name="claro_admin_external_sync_source_group_synchronize"
     * )
     * @EXT\ParamConverter(
     *      "externalGroup",
     *      class="ClarolineExternalSynchronizationBundle:ExternalGroup",
     *      options={"id" = "groupId"}
     * )
     * @EXT\Method({ "GET" })
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function synchronizeGroupForSourceAction($source, $externalGroup, $unsubscribe)
    {
        $unsubscribe = filter_var($unsubscribe, FILTER_VALIDATE_BOOLEAN);
        $this->externalUserGroupSyncManager->syncrhonizeGroupForExternalSource($source, $externalGroup, $unsubscribe);
        $userCount = $this->externalGroupManager->countUsersInExternalGroup($externalGroup);

        return  new JsonResponse(['synced' => true, 'userCount' => $userCount]);
    }
}
