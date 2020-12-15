<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Tool;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Repository\Tool\ToolMaskDecoderRepository;

class ToolMaskDecoderManager
{
    /** @var ObjectManager */
    private $om;
    /** @var ToolMaskDecoderRepository */
    private $maskRepo;

    /**
     * ToolMaskDecoderManager constructor.
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->maskRepo = $om->getRepository(ToolMaskDecoder::class);
    }

    /**
     * Create a mask decoder with default actions for a tool.
     *
     * @param Tool $tool
     */
    public function createDefaultToolMaskDecoders(Tool $tool)
    {
        foreach (ToolMaskDecoder::$defaultActions as $action) {
            $maskDecoder = $this->maskRepo
                ->findMaskDecoderByToolAndName($tool, $action);

            if (is_null($maskDecoder)) {
                $maskDecoder = new ToolMaskDecoder();
                $maskDecoder->setTool($tool);
                $maskDecoder->setName($action);
                $maskDecoder->setValue(ToolMaskDecoder::$defaultValues[$action]);

                $this->om->persist($maskDecoder);
            }
        }
        $this->om->flush();
    }

    /**
     * Create a specific mask decoder for a tool.
     *
     * @param Tool   $tool
     * @param string $action
     * @param int    $value
     */
    public function createToolMaskDecoder(Tool $tool, $action, $value)
    {
        $maskDecoder = new ToolMaskDecoder();
        $maskDecoder->setTool($tool);
        $maskDecoder->setName($action);
        $maskDecoder->setValue($value);

        $this->om->persist($maskDecoder);
        $this->om->flush();
    }

    /**
     * Returns an array containing the permission for a mask and a tool.
     *
     * @param int  $mask
     * @param Tool $tool
     *
     * @return array
     */
    public function decodeMask($mask, Tool $tool)
    {
        $decoders = $this->maskRepo->findMaskDecodersByTool($tool);
        $perms = [];

        foreach ($decoders as $decoder) {
            $perms[$decoder->getName()] = ($mask & $decoder->getValue()) ? true : false;
        }

        return $perms;
    }

    /**
     * Encode a mask for an array of permission and a tool.
     * The array of permissions should be defined that way:.
     *
     * array('open' => true, 'edit' => false, ...)
     *
     * @param array $perms
     * @param Tool  $tool
     *
     * @return int
     */
    public function encodeMask(array $perms, Tool $tool)
    {
        $decoders = $this->maskRepo->findMaskDecodersByTool($tool);

        $mask = 0;
        foreach ($decoders as $decoder) {
            if (isset($perms[$decoder->getName()])) {
                $mask += $perms[$decoder->getName()] ? $decoder->getValue() : 0;
            }
        }

        return $mask;
    }

    /**
     * @param Tool $tool
     *
     * @return ToolMaskDecoder[]
     */
    public function getMaskDecodersByTool(Tool $tool)
    {
        return $this->maskRepo->findMaskDecodersByTool($tool);
    }

    /**
     * @param Tool   $tool
     * @param string $name
     *
     * @return ToolMaskDecoder
     */
    public function getMaskDecoderByToolAndName(Tool $tool, $name)
    {
        return $this->maskRepo->findMaskDecoderByToolAndName($tool, $name);
    }

    public function getCustomMaskDecodersByTool(Tool $tool)
    {
        return $this->maskRepo->findCustomMaskDecodersByTool($tool);
    }
}
