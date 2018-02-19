<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\User;

use Claroline\CoreBundle\Annotations\ApiMeta;
use Claroline\CoreBundle\API\Options;
use Claroline\CoreBundle\Controller\APINew\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasRolesTrait;
use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiMeta(class="Claroline\CoreBundle\Entity\User")
 * @Route("/user")
 */
class UserController extends AbstractCrudController
{
    public function getName()
    {
        return 'user';
    }

    use HasRolesTrait;
    use HasOrganizationsTrait;
    use HasGroupsTrait;

    /**
     * @Route("/{id}/pws/create", name="apiv2_user_pws_create")
     * @Method("POST")
     * @ParamConverter("user", options={"mapping": {"id": "uuid"}})
     */
    public function createPersonalWorkspaceAction(User $user)
    {
        if (!$user->getPersonalWorkspace()) {
            $this->container->get('claroline.manager.user_manager')
              ->setPersonalWorkspace($user);
        } else {
            throw new \Exception('Workspace already exists');
        }

        return new JsonResponse($this->serializer->get('Claroline\CoreBundle\Entity\User')->serialize($user));
    }

    /**
     * @Route("/{id}/pws/delete", name="apiv2_user_pws_delete")
     * @Method("DELETE")
     * @ParamConverter("user", options={"mapping": {"id": "uuid"}})
     */
    public function deletePersonalWorkspaceAction(User $user)
    {
        $personalWorkspace = $user->getPersonalWorkspace();
        $this->container->get('claroline.manager.workspace_manager')->deleteWorkspace($personalWorkspace);

        return new JsonResponse($this->serializer->get('Claroline\CoreBundle\Entity\User')->serialize($user));
    }

    /**
     * @Route("/user/login", name="apiv2_user_create_and_login")
     * @Method("POST")
     */
    public function createAndLoginAction(Request $request)
    {
        //there is a little bit of computation involved here (ie, do we need to validate the account or stuff like this)
        //but keep it easy for now because an other route could be relevant
        $selfLog = true;
        $autoOrganization = $this->container
            ->get('claroline.config.platform_config_handler')
            ->getParameter('force_organization_creation');

        $organizationRepository = $this->container->get('claroline.persistence.object_manager')
            ->getRepository('ClarolineCoreBundle:Organization\Organization');

        //step one: creation the organization if it's here. If it exists, we fetch it.
        $data = $this->decodeRequest($request);

        if ($selfLog && 'anon.' === $this->container->get('security.token_storage')->getToken()->getUser()) {
            $this->options['create'][] = Options::USER_SELF_LOG;
        }

        $organization = null;

        if ($autoOrganization) {
            //try to find orga first
            //first find by vat
            if (isset($data['mainOrganization'])) {
                if (isset($data['mainOrganization']['vat']) && $data['mainOrganization']['vat'] !== null) {
                    $organization = $organizationRepository
                      ->findOneByVat($data['mainOrganization']['vat']);
                //then by code
                } else {
                    $organization = $organizationRepository
                      ->findOneByCode($data['mainOrganization']['code']);
                }
            }

            if (!$organization && isset($data['mainOrganization'])) {
                $organization = $this->crud->create(
                    'Claroline\CoreBundle\Entity\Organization\Organization',
                    $data['mainOrganization']
                );
            }

            //error handling
            if (is_array($organization)) {
                return new JsonResponse($organization, 400);
            }
        }

        $user = $this->crud->create(
           'Claroline\CoreBundle\Entity\User',
            $this->decodeRequest($request)
        );

        //error handling
        if (is_array($user)) {
            return new JsonResponse($user, 400);
        }

        if ($organization) {
            $this->crud->replace($user, 'mainOrganization', $organization);
        }

        return new JsonResponse(
            $this->serializer->serialize($user, $this->options['get']),
            201
        );
    }

    public function getOptions()
    {
        $create = [
            //maybe move these options in an other class
            Options::SEND_EMAIL,
            Options::ADD_NOTIFICATIONS,
            Options::ADD_PERSONAL_WORKSPACE,
        ];

        return [
            'deleteBulk' => [Options::SOFT_DELETE],
            'create' => $create,
            'get' => [Options::SERIALIZE_FACET],
        ];
    }

    /**
     * @Route(
     *    "/currentworkspaces",
     *    name="apiv2_user_currentworkspace"
     * )
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @Method("GET")
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function getCurrentWorkspacesAction(User $user)
    {
        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Workspace\Workspace',
            ['filters' => ['user' => $user->getUuid()]],
            $this->options['list']
        ));
    }
}
