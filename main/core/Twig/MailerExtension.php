<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class MailerExtension extends \Twig_Extension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'is_mailer_available' => new \Twig_SimpleFunction('is_mailer_available', [$this, 'isMailerAvailable']),
        ];
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
