<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Controller;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Cryptography\CryptographicKey;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Claroline\OpenBadgeBundle\Serializer\CriteriaSerializer;
use Claroline\OpenBadgeBundle\Serializer\ImageSerializer;
use Claroline\OpenBadgeBundle\Serializer\Options;
use Claroline\OpenBadgeBundle\Serializer\ProfileSerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/openbadge2")
 */
class OpenBadgeController
{
    /** @var SerializerProvider */
    private $serializer;
    /** @var CriteriaSerializer */
    private $criteriaSerializer;
    /** @var ImageSerializer */
    private $imageSerializer;
    /** @var ProfileSerializer */
    private $profileSerializer;

    public function __construct(
        SerializerProvider $serializer,
        CriteriaSerializer $criteriaSerializer,
        ImageSerializer $imageSerializer,
        ProfileSerializer $profileSerializer
    ) {
        $this->serializer = $serializer;
        $this->criteriaSerializer = $criteriaSerializer;
        $this->imageSerializer = $imageSerializer;
        $this->profileSerializer = $profileSerializer;
    }

    /**
     * @Route("/criteria/{badge}", name="apiv2_open_badge__criteria", methods={"GET"})
     * @EXT\ParamConverter("badge", class="Claroline\OpenBadgeBundle\Entity\BadgeClass", options={"mapping": {"badge": "uuid"}})
     */
    public function getCriteriaAction(BadgeClass $badge): JsonResponse
    {
        return new JsonResponse($this->criteriaSerializer->serialize($badge));
    }

    /**
     * @Route("/image/{image}", name="apiv2_open_badge__image", methods={"GET"})
     * @EXT\ParamConverter("image", class="Claroline\CoreBundle\Entity\File\PublicFile", options={"mapping": {"image": "id"}})
     */
    public function getImage(PublicFile $image): JsonResponse
    {
        return new JsonResponse($this->imageSerializer->serialize($image));
    }

    /**
     * @Route("/profile/{profile}", name="apiv2_open_badge__profile", methods={"GET"})
     */
    public function getProfile($profile): JsonResponse
    {
        return new JsonResponse($this->profileSerializer->serialize($profile));
    }

    /**
     * @Route("/badge/{badge}", name="apiv2_open_badge__badge_class", methods={"GET"})
     * @EXT\ParamConverter("badge", class="Claroline\OpenBadgeBundle\Entity\BadgeClass", options={"mapping": {"badge": "uuid"}})
     */
    public function getBadgeAction(BadgeClass $badge): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($badge, [Options::ENFORCE_OPEN_BADGE_JSON]));
    }

    /**
     * @Route("/assertion/{assertion}.json", name="apiv2_open_badge__assertion", methods={"GET"})
     * @EXT\ParamConverter("assertion", class="Claroline\OpenBadgeBundle\Entity\Assertion", options={"mapping": {"assertion": "uuid"}})
     */
    public function getAssertionAction(Assertion $assertion): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($assertion, [Options::ENFORCE_OPEN_BADGE_JSON]));
    }

    /**
     * @Route("/evidence/{evidence}", name="apiv2_open_badge__evidence", methods={"GET"})
     * @EXT\ParamConverter("evidence", class="Claroline\OpenBadgeBundle\Entity\Evidence", options={"mapping": {"evidence": "uuid"}})
     */
    public function getEvidenceAction(Evidence $evidence): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($evidence, [Options::ENFORCE_OPEN_BADGE_JSON]));
    }

    /**
     * @Route("/crypto/{key}", name="apiv2_open_badge__cryptographic_key", methods={"GET"})
     * @EXT\ParamConverter("key", class="Claroline\CoreBundle\Entity\Cryptography\CryptographicKey", options={"mapping": {"key": "uuid"}})
     */
    public function getCryptographicKeyAction(CryptographicKey $key): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($key, [Options::ENFORCE_OPEN_BADGE_JSON]));
    }

    /**
     * @Route("/connect", name="apiv2_open_badge__connect", methods={"GET"})
     */
    public function connectBackPackAction(Request $request): JsonResponse
    {
        return new JsonResponse($request->query->all());
    }
}
