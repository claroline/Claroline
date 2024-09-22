<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Controller;

use ZipArchive;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\ProfileSerializer;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/profile')]
class ProfileController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    private ObjectManager $om;
    private TempFileManager $tempManager;
    private Crud $crud;
    private SerializerProvider $serializer;
    private ParametersSerializer $parametersSerializer;
    private ProfileSerializer $profileSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        TempFileManager $tempManager,
        Crud $crud,
        SerializerProvider $serializer,
        ParametersSerializer $parametersSerializer,
        ProfileSerializer $profileSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->tempManager = $tempManager;
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->parametersSerializer = $parametersSerializer;
        $this->profileSerializer = $profileSerializer;
    }

    public function getName()
    {
        return 'profile';
    }

    /**
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    #[Route(path: '/export', name: 'apiv2_profile_export', methods: ['GET'])]
    public function exportAction(User $user): BinaryFileResponse
    {
        $pathArch = $this->tempManager->generate();

        $archive = new ZipArchive();
        $archive->open($pathArch, ZipArchive::CREATE);

        // add user json
        $archive->addFromString('user.json', json_encode($this->serializer->serialize($user), JSON_PRETTY_PRINT));

        $archive->close();

        $fileName = TextNormalizer::toKey($user->getUsername()).'.zip';

        return new BinaryFileResponse($pathArch, 200, [
            'Content-Disposition' => "attachment; filename={$fileName}",
        ]);
    }

    /**
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    #[Route(path: '/status/{status}', name: 'apiv2_user_change_status', methods: ['PUT'])]
    public function changeStatusAction(User $user, string $status): JsonResponse
    {
        if ('online' === $status) {
            $status = null;
        }
        $user->setStatus($status);

        $this->om->persist($user);
        $this->om->flush();

        return new JsonResponse($user->getStatus());
    }

    #[Route(path: '', name: 'apiv2_profile_open', methods: ['GET'])]
    public function openAction(): JsonResponse
    {
        return new JsonResponse([
            'facets' => $this->profileSerializer->serialize(),
            'parameters' => $this->parametersSerializer->serialize()['profile'],
        ]);
    }

    /**
     * Updates the profile configuration for the current platform.
     */
    #[Route(path: '', name: 'apiv2_profile_configure', methods: ['PUT'])]
    public function configureAction(Request $request): JsonResponse
    {
        $formData = $this->decodeRequest($request);

        // dump current profile configuration (to know what to remove later)
        /** @var Facet[] $facets */
        $facets = $this->om->getRepository(Facet::class)->findAll();

        $this->om->startFlushSuite();

        // updates facets data
        $updatedFacets = [];
        foreach ($formData as $facetData) {
            $updated = $this->crud->update(Facet::class, $facetData);
            $updatedFacets[$updated->getId()] = $updated;
        }

        // removes deleted facets
        foreach ($facets as $facet) {
            if (empty($updatedFacets[$facet->getId()])) {
                $this->crud->delete($facet);
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(
            $this->profileSerializer->serialize()
        );
    }
}
