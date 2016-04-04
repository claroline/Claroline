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

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class SendDatasConfirmExtension extends \Twig_Extension
{
    protected $platformConfigHandler;

    /**
     * @DI\InjectParams({
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(PlatformConfigurationHandler $configHandler)
    {
        $this->platformConfigHandler = $configHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'is_send_datas_confirmed' => new \Twig_Function_Method($this, 'isSendDatasConfirmed'),
        );
    }

    public function isSendDatasConfirmed()
    {
        return !is_null($this->platformConfigHandler->getParameter('confirm_send_datas'));
    }

    /**
     * Get the name of the twig extention.
     *
     * @return \String
     */
    public function getName()
    {
        return 'is_send_datas_confirmed_extension';
    }
}
