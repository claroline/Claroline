<?php

namespace Claroline\CoreBundle\Converter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\MissingObjectException;
use Claroline\CoreBundle\Persistence\ObjectManager;

/**
 * @DI\Service()
 * @DI\Tag("request.param_converter", attributes={"priority" = 500})
 *
 * Retreives a set of entities from an array of ids passed in the query string
 * (e.g.: "?ids[]=1&ids[]=2") and adds it to the request attributes.
 */
class MultipleIdsConverter implements ParamConverterInterface
{
    private $om;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @{inheritDoc}
     *
     * @throws InvalidConfigurationException if the name or class parameters are missing
     * @throws NotFoundHttpException         if one or more entities cannot be retreived
     * @throws BadRequestHttpException       if there is no "ids" array parameter in the query string
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        if (null === $parameter = $configuration->getName()) {
            throw new InvalidConfigurationException(InvalidConfigurationException::MISSING_NAME);
        }

        if (null === $entityClass = $configuration->getClass()) {
            throw new InvalidConfigurationException(InvalidConfigurationException::MISSING_CLASS);
        }

        if ($request->query->has('ids')) {
            if (is_array($ids = $request->query->get('ids'))) {
                try {
                    $entities = $this->om->findByIds($entityClass, $ids);
                    $request->attributes->set($parameter, $entities);

                    return true;
                } catch (MissingObjectException $ex) {
                    throw new NotFoundHttpException($ex->getMessage());
                }
            }

        }

        throw new BadRequestHttpException('An array of identifiers was expected');
    }

    /**
     * @{inheritDoc}
     */
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
