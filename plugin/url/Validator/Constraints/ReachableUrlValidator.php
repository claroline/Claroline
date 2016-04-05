<?php

namespace HeVinci\UrlBundle\Validator\Constraints;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\UrlValidator;
use Guzzle\Http\Client;
use JMS\DiExtraBundle\Annotation as DI;


/**
 * @DI\Validator("url_validator")
 */
class ReachableUrlValidator extends UrlValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        $previousViolationsCount = $this->context->getViolations()->count();
        parent::validate($value, $constraint);

        if ($previousViolationsCount < $this->context->getViolations()->count()) {
            return;
        }

        $client = new Client();
        try {
            $request = $client->head($value);
            $response = $request->send();

            if (!$response->isSuccessful()) {
                $this->context->addViolation($constraint->clientError, array(
                    '%errorCode%' => $response->getStatusCode()
                ));
            }
        } catch (CurlException $e) {
            $this->context->addViolation($constraint->websiteDoesntExist, array(
                '%url' => $value
            ));
        } catch (ClientErrorResponseException $e) {
            $errorCode = $e->getResponse()->getStatusCode();

            if ($errorCode == 403) {
                $this->context->addViolation($constraint->accessDenied);
            } elseif ($errorCode == 404) {
                $this->context->addViolation($constraint->resNotFound);
            } elseif ($errorCode == 405) {
                $allow = $e->getResponse()->getHeaders()['allow'];
                if (!preg_match('#GET#', $allow)) {
                    $this->context->addViolation($constraint->methodNotAllowed);
                }
            } else {
                $this->context->addViolation($constraint->clientError, array(
                    '%errorCode%' => $errorCode
                ));
            }
        } catch (ServerErrorResponseException $e) {
            $this->context->addViolation($constraint->serverError, array(
                '%errorCode%' => $e->getResponse()->getStatusCode()
            ));
        }
    }
}