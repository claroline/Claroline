<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Controller;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\ThemeBundle\Entity\Icon\IconSet;
use Claroline\ThemeBundle\Manager\IconSetManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/icon_set")
 */
class IconSetController
{
    use PermissionCheckerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var IconSetManager */
    private $iconSetManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        IconSetManager $iconSetManager
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->iconSetManager = $iconSetManager;
    }

    /**
     * @Route("/", name="apiv2_icon_set_create", methods={"POST"})
     */
    public function createAction(Request $request): JsonResponse
    {
        $this->checkPermission('CREATE', new IconSet(), [], true);

        $setName = $request->request->get('name');
        $files = $request->files->all();

        $errors = [];
        if (empty($setName)) {
            $errors[] = ['path' => '/name', 'message' => 'value_not_blank'];
        }
        if (empty($files)) {
            $errors[] = ['path' => '/archive', 'message' => 'value_not_blank'];
        }

        $existingSets = $this->om->getRepository(IconSet::class)->count(['name' => $setName]);
        if (0 !== $existingSets) {
            $errors[] = ['path' => '/name', 'message' => 'value_not_unique'];
        }

        if ($errors) {
            throw new InvalidDataException('Invalid data sent.', $errors);
        }

        $createdSet = $this->iconSetManager->createSet($setName, array_shift($files));

        return new JsonResponse($createdSet, 201);
    }

    /**
     * @Route("/{iconSet}", name="apiv2_icon_set_get", methods={"GET"})
     */
    public function downloadAction(string $iconSet): BinaryFileResponse
    {
        return new BinaryFileResponse($this->iconSetManager->downloadSet($iconSet), 200, [
            'Content-Disposition' => "attachment; filename={$iconSet}.zip",
        ]);
    }

    /**
     * @Route("/{iconSet}", name="apiv2_icon_set_delete", methods={"DELETE"})
     */
    public function deleteAction(string $iconSet): JsonResponse
    {
        $existingSets = $this->om->getRepository(IconSet::class)->findBy(['name' => $iconSet]);
        if (!empty($existingSets)) {
            $this->checkPermission('DELETE', $existingSets[0], [], true);

            $this->iconSetManager->deleteSet($iconSet);
        }

        return new JsonResponse(null, 204);
    }
}
