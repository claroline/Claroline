<?php

namespace Claroline\CoreBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class MailerExtension extends \Twig_Extension
{
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'is_mailer_available' => new \Twig_Function_Method($this, 'isMailerAvailable'),
        );
    }

    public function isMailerAvailable()
    {
        return $this->container->get('claroline.manager.mail_manager')->isMailerAvailable();
    }

    /**
     * Get the name of the twig extention.
     *
     * @return \String
     */
    public function getName()
    {
        return 'is_mailer_available_extension';
    }
}
