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
use Claroline\OpenBadgeBundle\Serializer\CriteriaSerializer;
use Claroline\OpenBadgeBundle\Serializer\ImageSerializer;
use Claroline\OpenBadgeBundle\Serializer\Options;
use Claroline\OpenBadgeBundle\Serializer\ProfileSerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/openbadge2")
 */
class OpenBadgeController
{
    public function _construct(
        SerializerProvider $serializer,
        CriteriaSerializer $criteriaSerializer,
        ImageSerializer    $imageSerializer,
        ProfileSerializer  $profileSerializer
    ) {
        $this->serializer = $serializer;
        $this->criteriaSerializer = $criteriaSerializer;
        $this->imageSerializer = $imageSerializer;
        $this->profileSerializer = $profileSerializer;
    }

    /**
     * @EXT\Route("/criteria/{badge}", name="apiv2_open_badge__criteria")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("badge", class="ClarolineOpenBadgeBundle:BadgeClass", options={"mapping": {"badge": "uuid"}})
     *
     * @return JsonResponse
     */
    public function getCriteriaAction(BadgeClass $badge)
    {
        return new JsonResponse($this->criteriaSerializer->serialize($badge));
    }

    /**
     * @EXT\Route("/image/{image}", name="apiv2_open_badge__image")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("image", class="ClarolineCoreBundle:File\PublicFile", options={"mapping": {"image": "id"}})
     *
     * @return JsonResponse
     */
    public function getImage(PublicFile $image)
    {
        return new JsonResponse($this->imageSerializer->serialize($image));
    }

    /**
     * @EXT\Route("/profile/{profile}", name="apiv2_open_badge__profile")
     * @EXT\Method("GET")
     *
     * @return JsonResponse
     */
    public function getProfile($profile)
    {
        return new JsonResponse($this->profileSerializer->serialize($profile));
    }

    /**
     * @EXT\Route("/badge/{badge}", name="apiv2_open_badge__badge_class")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("badge", class="ClarolineOpenBadgeBundle:BadgeClass", options={"mapping": {"badge": "uuid"}})
     *
     * @return JsonResponse
     */
    public function getBadgeAction(BadgeClass $badge)
    {
        return new JsonResponse($this->serializer->serialize($badge, [Options::ENFORCE_OPEN_BADGE_JSON]));
    }

    /**
     * @EXT\Route("/assertion/{assertion}.json", name="apiv2_open_badge__assertion")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("assertion", class="ClarolineOpenBadgeBundle:Assertion", options={"mapping": {"assertion": "uuid"}})
     *
     * @return JsonResponse
     */
    public function getAssertionAction(Assertion $assertion)
    {
        return new JsonResponse($this->serializer->serialize($assertion, [Options::ENFORCE_OPEN_BADGE_JSON]));
    }

    /**
     * @EXT\Route("/evidence/{evidence}", name="apiv2_open_badge__evidence")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("evidence", class="ClarolineOpenBadgeBundle:Evidence", options={"mapping": {"evidence": "uuid"}})
     *
     * @return JsonResponse
     */
    public function getEvidenceAction(Evidence $evidence)
    {
        return new JsonResponse($this->serializer->serialize($evidence, [Options::ENFORCE_OPEN_BADGE_JSON]));
    }

    /**
     * @EXT\Route("/crypto/{key}", name="apiv2_open_badge__cryptographic_key")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("key", class="ClarolineCoreBundle:Cryptography\CryptographicKey", options={"mapping": {"key": "uuid"}})
     *
     * @return JsonResponse
     */
    public function getCryptographicKeyction(CryptographicKey $key)
    {
        return new JsonResponse($this->serializer->serialize($key, [Options::ENFORCE_OPEN_BADGE_JSON]));
    }

    /**
     * @EXT\Route("/connect", name="apiv2_open_badge__connect")
     * @EXT\Method("GET")
     *
     * @return JsonResponse
     */
    public function connectBackPackAction(Request $request)
    {
        return new JsonResponse($request->query->all());
    }
}
