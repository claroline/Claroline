<?php

namespace Claroline\CoreBundle\DependencyInjection\Compiler;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * Wires the session's save handler service based on platform options.
 */
final class SessionConfigPass implements CompilerPassInterface
{
    private const STORAGE_TYPES = ['native', 'file', 'redis'];

    public function process(ContainerBuilder $container)
    {
        /** @var PlatformConfigurationHandler $platformConfig */
        $platformConfig = $container->get(PlatformConfigurationHandler::class);
        $storageType = $platformConfig->getParameter('session.storage_type');

        if (!\in_array($storageType, self::STORAGE_TYPES)) {
            throw new LogicException(sprintf('Invalid value for platform option "session.storage_type". The value must be one of [%s], "%s" given.', implode(', ', self::STORAGE_TYPES), $platformConfig->getParameter('session.storage_type')));
        }

        if ('redis' !== $storageType) {
            // defaults to native file handler (see session.yml)
            $container->setAlias('session.handler', 'claroline.session_handler.file')->setPublic(false);
            // remove unneeded redis handler service from the container
            $container->removeDefinition('claroline.session_handler.redis');

            return;
        }

        $redisHost = $platformConfig->getParameter('session.redis_host');
        if (!$redisHost) {
            throw new LogicException('The "session.redis_host" platform option must not be empty.');
        }

        $redisPort = $platformConfig->getParameter('session.redis_port');
        if (!\is_int($redisPort)) {
            throw new LogicException('The "session.redis_port" platform option must be a valid port number.');
        }

        $redisDefinition = $container->getDefinition('.claroline.session_handler.redis.connection');
        $redisDefinition->addMethodCall('connect', [$redisHost, $redisPort]);

        $redisPassword = $platformConfig->getParameter('session.redis_password');

        if ($redisPassword) {
            $redisDefinition->addMethodCall('auth', [$redisPassword]);
        }

        $container->setAlias('session.handler', 'claroline.session_handler.redis')->setPublic(false);
    }
}
