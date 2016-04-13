<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\TwigBundle\Controller\ExceptionController;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpFoundation\Request;

class CustomExceptionController extends ExceptionController
{
    /**
     * {@inheritdoc}
     */
    protected function findTemplate(Request $request, $format, $code, $debug)
    {
        if (!$debug && $format === 'html') {
            $code = in_array($code, array(400, 403, 404, 503)) ? $code : 500;
            $template = new TemplateReference('ClarolineCoreBundle', 'Exception', 'error'.$code, 'html', 'twig');

            if ($this->templateExists($template)) {
                return $template;
            }
        }

        return parent::findTemplate($request, $format, $code, $debug);
    }
}
