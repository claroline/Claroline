<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.importer.rich_text_formatter")
 */
class RichTextFormatter {

    /**
     * @param $text
     * @param array $resources
     *
     * The $resource array MUST be formatter this way:
     * where the sub array key is an the element uid.
     * array(
     *      'directories' => array(1 => $directory, ...),
     *      'items'       => array(42 => $file, ...)
     * )
     */
    public function format($text, array $resources)
    {

    }
} 