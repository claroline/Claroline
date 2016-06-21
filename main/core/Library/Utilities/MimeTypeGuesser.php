<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Utilities;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.utilities.mime_type_guesser")
 */
class MimeTypeGuesser extends MimeTypeExtensionGuesser
{
    public function __construct()
    {
        $this->defaultExtensions['image/jpeg'] = 'jpg';
        $this->defaultExtensions['audio/mp3'] = 'mp3';
    }

    /**
     * @todo Use array_search instead of flipping the whole array
     */
    public function guess($extension)
    {
        $mimeArray = array_flip($this->defaultExtensions);

        return isset($mimeArray[$extension]) ? $mimeArray[$extension] : null;
    }
}
