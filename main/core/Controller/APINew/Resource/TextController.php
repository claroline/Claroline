<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Resource;

use Claroline\CoreBundle\Entity\Resource\Text;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\Response;

/**
 * @EXT\Route("resource/text")
 */
class TextController
{
    /**
     * @EXT\Route(
     *     "/{id}/content",
     *     name="apiv2_resource_text_content"
     * )
     *
     * @param Text $text
     *
     * @return string
     */
    public function getContentAction(Text $text)
    {
        return new Response(
            $text->getContent()
        );
    }
}
