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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * By default throws an exception if no User authenticated.
 *
 * If anonymous must be allowed add `options={"allowAnonymous" = true}`,
 * in this case the converter will return `null`.
 */
class CurrentUserConverter implements ParamConverterInterface
{
    private $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigurationException if the parameter name is missing
     * @throws AccessDeniedException         if the current request is anonymous and `allowAnonymous` option is false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (null === $parameter = $configuration->getName()) {
            throw new InvalidConfigurationException(InvalidConfigurationException::MISSING_NAME);
        }

        // Check whether we need to let pass anonymous
        $allowAnonymous = false;
        $options = $configuration->getOptions();
        if ($options && isset($options['allowAnonymous']) && true === $options['allowAnonymous']) {
            $allowAnonymous = true;
        }

        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user instanceof User) {
                $request->attributes->set($parameter, $user);

                return true;
            } elseif ($allowAnonymous) {
                $request->attributes->set($parameter, null);

                return true;
            }
        }

        throw new AccessDeniedException();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        $options = $configuration->getOptions();

        if (isset($options['allowAnonymous'])) {
            return (is_bool($options['allowAnonymous'])) ? true : false;
        }

        return true;
    }
}
