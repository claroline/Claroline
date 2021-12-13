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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
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

/**
 * @Route("/profile")
 */
class ProfileController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var TempFileManager */
    private $tempManager;
    /** @var Crud */
    private $crud;
    /** @var SerializerProvider */
    private $serializer;
    /** @var ParametersSerializer */
    private $parametersSerializer;
    /** @var ProfileSerializer */
    private $profileSerializer;

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
     * @Route("/export", name="apiv2_profile_export", methods={"GET"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function exportAction(User $user): BinaryFileResponse
    {
        $pathArch = $this->tempManager->generate();

        $archive = new \ZipArchive();
        $archive->open($pathArch, \ZipArchive::CREATE);

        // add user json
        $archive->addFromString('user.json', json_encode($this->serializer->serialize($user), JSON_PRETTY_PRINT));

        $archive->close();

        $fileName = TextNormalizer::toKey($user->getUsername()).'.zip';

        return new BinaryFileResponse($pathArch, 200, [
            'Content-Disposition' => "attachment; filename={$fileName}",
        ]);
    }

    /**
     * @Route("/{username}", name="apiv2_profile_open", methods={"GET"})
     * @EXT\ParamConverter("user", options={"mapping": {"username": "username"}})
     */
    public function openAction(User $user)
    {
        $this->checkPermission('OPEN', $user, [], true);

        return new JsonResponse([
            'facets' => $this->profileSerializer->serialize(),
            'parameters' => $this->parametersSerializer->serialize()['profile'],
            'user' => $this->serializer->serialize($user, [Options::SERIALIZE_FACET]),
        ]);
    }

    /**
     * Updates the profile configuration for the current platform.
     *
     * @Route("", name="apiv2_profile_update", methods={"PUT"})
     */
    public function updateAction(Request $request): JsonResponse
    {
        $formData = $this->decodeRequest($request);

        // dump current profile configuration (to know what to remove later)
        /** @var Facet[] $facets */
        $facets = $this->om->getRepository(Facet::class)->findAll();

        $this->om->startFlushSuite();

        // updates facets data
        $updatedFacets = [];
        foreach ($formData as $facetData) {
            $updated = $this->crud->update(Facet::class, $facetData, [Options::DEEP_DESERIALIZE]);
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
