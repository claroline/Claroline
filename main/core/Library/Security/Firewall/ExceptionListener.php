<?php

namespace Claroline\CoreBundle\Library\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;

class ExceptionListener extends BaseExceptionListener
{
    protected function setTargetPath(Request $request)
    {
        // Do not save target path if is a resource inside home page or any other page of the platform
        // E.g. an img inside a page that anonymous has no right to see it
        // First test if referer header exists, if not then the resource is the main target and not some other page
        // Do this test only for media routes
        if (!empty($request->headers->get('referer')) && preg_match('/(claro_file_get_media)/', $request->get('_route'))) {
            // If request doesn't contain any of the below types then it is not a generic request, don't store it to path
            $genericRequestContentTypes = ['text/html', 'application/xhtml+xml', 'application/xml'];
            if (empty(array_intersect($request->getAcceptableContentTypes(), $genericRequestContentTypes))) {
                return;
            }
        }

        parent::setTargetPath($request);
    }
}
