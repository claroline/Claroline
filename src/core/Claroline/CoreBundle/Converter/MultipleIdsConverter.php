<?php

namespace Claroline\CoreBundle\Converter;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use JMS\DiExtraBundle\Annotation as DI;

use Claroline\CoreBundle\Database\GenericRepository;
use Claroline\CoreBundle\Database\MissingEntityException;

/**
 * @DI\Service()
 * @DI\Tag("request.param_converter", attributes={"priority" = 500})
 */
class MultipleIdsConverter implements ParamConverterInterface
{
    private $repo;

    /**
     * @DI\InjectParams({
     *     "repo" = @DI\Inject("claroline.database.generic_repository")
     * })
     */
    public function __construct(GenericRepository $repo)
    {
        $this->repo = $repo;
    }

    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        if (null === $configuration->getName()) {
            throw new ConfigurationException('the controller parameter name is mandatory');
        }

        if (null === $configuration->getClass()) {
            throw new ConfigurationException('the "class" field is mandatory');
        }

        if ($request->query->has('ids')) {
            if (is_array($ids = $request->query->get('ids'))) {
                try {
                    $entities = $this->repo->findByIds($configuration->getClass(), $ids);
                    $request->attributes->set($configuration->getName(), $entities);

                    return true;
                } catch (MissingEntityException $ex) {
                    throw new NotFoundHttpException($ex->getMessage());
                }
            }

        }

        throw new BadRequestHttpException('An array of identifiers was expected');
    }

    public function supports(ConfigurationInterface $configuration)
    {
        if (!$configuration instanceof ParamConverter) {
            return false;
        }

        $options = $configuration->getOptions();

        if (isset($options['multipleIds']) && $options['multipleIds'] === true) {
            return true;
        }

        return false;
    }
}