<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Menu;

use Claroline\CoreBundle\Entity\User;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\Event;

class ExceptionActionEvent extends Event
{
    private $factory;
    private $menu;
    private $user;
    private $message;
    private $exceptionClass;
    private $file;
    private $line;
    private $url;
    private $referer;
    private $httpCode;

    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param \Knp\Menu\ItemInterface    $menu
     * @param User                       $user
     * @param string                     $message
     * @param string                     $exceptionClass
     * @param string                     $file
     * @param string                     $line
     * @param string                     $url
     * @param string                     $referer
     * @param int                        $httpCode
     */
    public function __construct(
        FactoryInterface $factory,
        ItemInterface $menu,
        User $user,
        $message,
        $exceptionClass,
        $file,
        $line,
        $url,
        $referer,
        $httpCode = null
    ) {
        $this->factory = $factory;
        $this->menu = $menu;
        $this->user = $user;
        $this->message = $message;
        $this->exceptionClass = $exceptionClass;
        $this->file = $file;
        $this->line = $line;
        $this->url = $url;
        $this->referer = $referer;
        $this->httpCode = $httpCode;
    }

    /**
     * @return \Knp\Menu\FactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function getMenu()
    {
        return $this->menu;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getExceptionClass()
    {
        return $this->exceptionClass;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getReferer()
    {
        return $this->referer;
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }
}
