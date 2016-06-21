<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.tool_mask_decoder_manager")
 */
class ToolMaskDecoderManager
{
    private $maskRepo;
    private $om;

    /**
     * Constructor.
     *
     * @DI\InjectParams({"om" = @DI\Inject("claroline.persistence.object_manager")})
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->maskRepo = $om->getRepository('ClarolineCoreBundle:Tool\ToolMaskDecoder');
    }

    /**
     * Create a mask decoder with default actions for a tool.
     *
     * @param \Claroline\CoreBundle\Entity\Tool\Tool $tool
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
                $maskDecoder->setGrantedIconClass(
                    ToolMaskDecoder::$defaultGrantedIconClass[$action]
                );
                $maskDecoder->setDeniedIconClass(
                    ToolMaskDecoder::$defaultDeniedIconClass[$action]
                );
                $this->om->persist($maskDecoder);
            }
        }
        $this->om->flush();
    }

    /**
     * Create a specific mask decoder for a tool.
     *
     * @param \Claroline\CoreBundle\Entity\Tool\Tool $tool
     */
    public function createToolMaskDecoder(
        Tool $tool,
        $action,
        $value,
        $grantedIconClass,
        $deniedIconClass
    ) {
        $maskDecoder = new ToolMaskDecoder();
        $maskDecoder->setTool($tool);
        $maskDecoder->setName($action);
        $maskDecoder->setValue($value);
        $maskDecoder->setGrantedIconClass($grantedIconClass);
        $maskDecoder->setDeniedIconClass($deniedIconClass);
        $this->om->persist($maskDecoder);
        $this->om->flush();
    }

    /**
     * Returns an array containing the permission for a mask and a tool.
     *
     * @param int                                    $mask
     * @param \Claroline\CoreBundle\Entity\Tool\Tool $tool
     *
     * @return array
     */
    public function decodeMask($mask, Tool $tool)
    {
        $decoders = $this->maskRepo->findMaskDecodersByTool($tool);
        $perms = array();

        foreach ($decoders as $decoder) {
            $perms[$decoder->getName()] = ($mask & $decoder->getValue()) ?
                true :
                false;
        }

        return $perms;
    }

    public function decodeMaskWithDecoders($mask, array $decoders)
    {
        $perms = array();

        foreach ($decoders as $decoder) {
            $perms[$decoder->getName()] = ($mask & $decoder->getValue()) ?
                true :
                false;
        }

        return $perms;
    }

    /***** ToolRightsRepository access methods *****/

    public function getMaskDecodersByTool(Tool $tool, $executeQuery = true)
    {
        return $this->maskRepo->findMaskDecodersByTool($tool, $executeQuery);
    }

    public function getAllMaskDecoders($executeQuery = true)
    {
        return $this->maskRepo->findAllMaskDecoders($executeQuery);
    }

    public function getMaskDecoderByToolAndName(
        Tool $tool,
        $name,
        $executeQuery = true
    ) {
        return $this->maskRepo->findMaskDecoderByToolAndName(
            $tool,
            $name,
            $executeQuery
        );
    }

    public function getCustomMaskDecodersByTool(Tool $tool, $executeQuery = true)
    {
        return $this->maskRepo->findCustomMaskDecodersByTool($tool, $executeQuery);
    }
}
