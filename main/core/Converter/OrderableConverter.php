<?php

namespace Claroline\CoreBundle\Converter;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("request.param_converter", attributes={"priority" = 500})
 */
class OrderableConverter implements ParamConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (null === $parameter = $configuration->getName()) {
            throw new InvalidConfigurationException(InvalidConfigurationException::MISSING_NAME);
        }

        if (null === $entityClass = $configuration->getClass()) {
            throw new InvalidConfigurationException(InvalidConfigurationException::MISSING_CLASS);
        }

        if (!class_exists($entityClass)) {
            throw new \Exception('The class '.$entityClass.' does not exists.');
        }

        $rClass = new \ReflectionClass($entityClass);

        if (!$rClass->implementsInterface('Claroline\CoreBundle\Entity\OrderableInterface')) {
            throw new \Exception($entityClass.' is not orderable.');
        }

        $orderableFields = $rClass->newInstanceWithoutConstructor()->getOrderableFields();

        if (in_array($request->attributes->get($parameter), $orderableFields)) {
            return true;
        } else {
            throw new BadRequestHttpException();
        }

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

        $options = $configuration->getOptions();

        if (isset($options['orderable']) && $options['orderable'] === true) {
            return true;
        }

        return false;
    }
}
