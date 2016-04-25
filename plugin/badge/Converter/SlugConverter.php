<?php

namespace Icap\BadgeBundle\Converter;

use Icap\BadgeBundle\Repository\BadgeRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
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

    /** @var \Icap\BadgeBundle\Repository\BadgeRepository */
    private $badgeRepository;

    /**
     * @DI\InjectParams({
     *     "badgeRepository" = @DI\Inject("icap_badge.repository.badge"),
     *     "entityManager"   = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $entityManager, BadgeRepository $badgeRepository)
    {
        $this->entityManager = $entityManager;
        $this->badgeRepository = $badgeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigurationException if the parameter name is missing
     * @throws AccessDeniedHttpException     if the current user is not authenticated
     */
    public function apply(Request $request, ParamConverter $configuration)
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
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        if (!$configuration instanceof ParamConverter) {
            return false;
        }

        if ('badge_converter' === $configuration->getConverter()) {
            return true;
        }

        return false;
    }
}
