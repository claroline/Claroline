<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class RichTextFormatEvent extends Event
{
    private $text;

    public function __construct($text, &$_data = array(), &$_files = array())
    {
        $this->text = $text;
        $this->_data = $_data;
        $this->_files = $_files;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function setData($_data)
    {
        $this->_data = $_data;
    }

    public function getFiles()
    {
        return $this->_files;
    }

    public function setFiles($_files)
    {
        $this->_files = $_files;
    }
}
