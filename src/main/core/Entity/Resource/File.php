<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\FileRepository")
 * @ORM\Table(name="claro_file")
 */
class File extends AbstractResource
{
    // propose to download raw file when opening the resource
    const OPENING_DOWNLOAD = 'download';
    // try to use the browser player to display the file
    const OPENING_BROWSER = 'browser';
    // use the claroline file player to display the file
    const OPENING_PLAYER = 'player';

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $size;

    /**
     * @ORM\Column(name="hash_name")
     */
    protected $hashName;

    /**
     * @ORM\Column()
     */
    protected $opening = self::OPENING_PLAYER;

    /**
     * Returns the file size.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Sets the file size.
     *
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getOpening(): string
    {
        return $this->opening;
    }

    public function setOpening(string $opening)
    {
        $this->opening = $opening;
    }

    /**
     * Returns the name of the file actually stored in the file directory (as
     * opposed to the file original name, which is kept in the entity name
     * attribute).
     *
     * @return string
     */
    public function getHashName()
    {
        return $this->hashName;
    }

    /**
     * Sets the name of the physical file that will be stored in the file directory.
     * To prevent file name issues (e.g. with special characters), the original
     * file should be renamed with a standard unique identifier.
     *
     * @param string $hashName
     */
    public function setHashName($hashName)
    {
        $this->hashName = $hashName;
    }
}
