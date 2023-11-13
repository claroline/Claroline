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
    private ObjectManager $om;
    private ToolMaskDecoderRepository $maskRepo;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->maskRepo = $om->getRepository(ToolMaskDecoder::class);
    }

    /**
     * Create a mask decoder with default actions for a tool.
     */
    public function createDefaultToolMaskDecoders(string $toolName): void
    {
        foreach (ToolMaskDecoder::DEFAULT_ACTIONS as $action) {
            $maskDecoder = $this->maskRepo
                ->findMaskDecoderByToolAndName($toolName, $action);

            if (is_null($maskDecoder)) {
                $maskDecoder = new ToolMaskDecoder();
                $maskDecoder->setTool($toolName);
                $maskDecoder->setName($action);
                $maskDecoder->setValue(ToolMaskDecoder::DEFAULT_VALUES[$action]);

                $this->om->persist($maskDecoder);
            }
        }
        $this->om->flush();
    }

    /**
     * Create a specific mask decoder for a tool.
     */
    public function createToolMaskDecoder(string $toolName, string $action, int $value): void
    {
        $maskDecoder = new ToolMaskDecoder();
        $maskDecoder->setTool($toolName);
        $maskDecoder->setName($action);
        $maskDecoder->setValue($value);

        $this->om->persist($maskDecoder);
        $this->om->flush();
    }

    /**
     * Returns an array containing the permission for a mask and a tool.
     */
    public function decodeMask(int $mask, string $toolName): array
    {
        $perms = [];

        $decoders = $this->maskRepo->findMaskDecodersByTool($toolName);
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
     */
    public function encodeMask(array $perms, string $toolName): int
    {
        $mask = 0;

        $decoders = $this->maskRepo->findMaskDecodersByTool($toolName);

        foreach ($decoders as $decoder) {
            if (isset($perms[$decoder->getName()])) {
                $mask += $perms[$decoder->getName()] ? $decoder->getValue() : 0;
            }
        }

        return $mask;
    }

    /**
     * @return ToolMaskDecoder[]
     */
    public function getMaskDecodersByTool(string $toolName)
    {
        return $this->maskRepo->findMaskDecodersByTool($toolName);
    }

    public function getMaskDecoderByToolAndName(string $toolName, string $name): ?ToolMaskDecoder
    {
        return $this->maskRepo->findMaskDecoderByToolAndName($toolName, $name);
    }

    public function getCustomMaskDecodersByTool(string $toolName)
    {
        return $this->maskRepo->findCustomMaskDecodersByTool($toolName);
    }
}
