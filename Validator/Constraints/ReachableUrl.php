<?php

namespace HeVinci\UrlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ReachableUrl extends Constraint
{
    public $websiteDoesntExist = 'website_doesnt_exist %url%';
    public $clientError= 'client_error %errorCode%';
    public $serverError = 'server_error';
    public $accessDenied = 'access_denied';
    public $resNotFound = 'res_not_found';
    public $methodNotAllowed = 'method_not_allowed';

    public function validateBy()
    {
        return 'url_validator';
    }
}