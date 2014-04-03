<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Converter\Badge;

use Claroline\CoreBundle\Repository\Badge\BadgeRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\User;

/**
 * @DI\Service()
 * @DI\Tag("request.param_converter", attributes={"priority" = 500, "converter" = "badge_converter"})
 */
class SlugConverter implements ParamConverterInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /** @var \Claroline\CoreBundle\Repository\Badge\BadgeRepository */
    private $badgeRepository;

    /**
     * @DI\InjectParams({
     *     "badgeRepository" = @DI\Inject("claroline.repository.badge"),
     *     "entityManager"   = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $entityManager, BadgeRepository $badgeRepository)
    {
        $this->entityManager   = $entityManager;
        $this->badgeRepository = $badgeRepository;
    }

    /**
     * @{inheritDoc}
     *
     * @throws InvalidConfigurationException if the parameter name is missing
     * @throws AccessDeniedHttpException     if the current user is not authenticated
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $slug = $request->attributes->get('slug');

        $options = $configuration->getOptions();
        if (isset($options['check_deleted']) && !$options['check_deleted']) {
            $this->entityManager->getFilters()->disable('softdeleteable');
        }

        $badge = $this->badgeRepository->findBySlug($slug);

        if (null === $badge) {
            throw new NotFoundHttpException();
        }

        $parameterName = $configuration->getName();
        $request->attributes->set($parameterName, $badge);

        return true;
    }

    /**
     * @{inheritDoc}
     */
    public function supports(ConfigurationInterface $configuration)
    {
        if (!$configuration instanceof ParamConverter) {
            return false;
        }

        if ("badge_converter" === $configuration->getConverter()) {
            return true;
        }

        return false;
    }
}
