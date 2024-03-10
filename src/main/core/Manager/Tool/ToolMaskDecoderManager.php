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
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;

class ToolMaskDecoderManager
{
    private array $maskDecoders = [];

    public function __construct(
        private readonly ObjectManager $om
    ) {
        // loads all mask decoders only once
        // they cannot be changed at the runtime for now and it will save some DB calls
        $maskDecoders = $this->om->getRepository(ToolMaskDecoder::class)->findAll();
        foreach ($maskDecoders as $maskDecoder) {
            if (empty($this->maskDecoders[$maskDecoder->getTool()])) {
                $this->maskDecoders[$maskDecoder->getTool()] = [];
            }
            $this->maskDecoders[$maskDecoder->getTool()][] = $maskDecoder;
        }
    }

    /**
     * Create a mask decoder with default actions for a tool.
     */
    public function createDefaultToolMaskDecoders(string $toolName): void
    {
        foreach (ToolMaskDecoder::DEFAULT_ACTIONS as $action) {
            $maskDecoder = $this->getMaskDecoderByToolAndName($toolName, $action);

            if (empty($maskDecoder)) {
                $maskDecoder = new ToolMaskDecoder();
                $maskDecoder->setTool($toolName);
                $maskDecoder->setName($action);
                $maskDecoder->setValue(ToolMaskDecoder::DEFAULT_VALUES[$action]);

                $this->maskDecoders[$toolName] = $maskDecoder;

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

        $this->maskDecoders[$toolName] = $maskDecoder;

        $this->om->persist($maskDecoder);
        $this->om->flush();
    }

    /**
     * Returns an array containing the permission for a mask and a tool.
     */
    public function decodeMask(int $mask, string $toolName): array
    {
        $perms = [];

        $decoders = $this->getMaskDecodersByTool($toolName);
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

        $decoders = $this->getMaskDecodersByTool($toolName);
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
    public function getMaskDecodersByTool(string $toolName): array
    {
        return $this->maskDecoders[$toolName] ?? [];
    }

    public function getMaskDecoderByToolAndName(string $toolName, string $name): ?ToolMaskDecoder
    {
        $toolDecoders = $this->getMaskDecodersByTool($toolName);
        foreach ($toolDecoders as $toolDecoder) {
            if ($toolDecoder->getName() === $name) {
                return $toolDecoder;
            }
        }

        return null;
    }

    /**
     * @return ToolMaskDecoder[]
     */
    public function getCustomMaskDecodersByTool(string $toolName): array
    {
        $toolDecoders = $this->getMaskDecodersByTool($toolName);

        return array_filter($toolDecoders, function (ToolMaskDecoder $maskDecoder) {
            return !in_array($maskDecoder->getName(), ToolMaskDecoder::DEFAULT_ACTIONS);
        });
    }
}
