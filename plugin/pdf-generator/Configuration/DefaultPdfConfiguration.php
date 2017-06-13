<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PdfGeneratorBundle\Configuration;

use Claroline\CoreBundle\Library\Configuration\ParameterProviderInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.configuration")
 */
class DefaultPdfConfiguration implements ParameterProviderInterface
{
    public function getDefaultParameters()
    {
        return [
            'knp_pdf_timeout' => 60,
        ];
    }
}
