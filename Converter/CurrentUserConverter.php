<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Converter;

use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service
 * @DI\Tag("request.param_converter", attributes={"converter"="current_user"})
 */
class CurrentUserConverter implements ParamConverterInterface
{
    private $tokenStorage;

    /**
     * @DI\InjectParams({"tokenStorage" = @DI\Inject("security.token_storage")})
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidConfigurationException if the parameter name is missing
     * @throws AccessDeniedHttpException     if the current request is anonymous
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (null === $parameter = $configuration->getName()) {
            throw new InvalidConfigurationException(InvalidConfigurationException::MISSING_NAME);
        }

        $token = $this->tokenStorage->getToken();

        if ($token && ($user = $token->getUser()) instanceof User) {
            $request->attributes->set($parameter, $user);

            return true;
        }

        throw new AccessDeniedHttpException();
    }

    /**
     * {@inheritDoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'current_user';
    }
}

