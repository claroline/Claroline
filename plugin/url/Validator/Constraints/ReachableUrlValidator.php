<?php

namespace HeVinci\UrlBundle\Validator\Constraints;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\UrlValidator;

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
            $request = $client->head(
                $value,
                // make request with a fake user-agent header to avoid possible restrictions on curl requests
                ['User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/55.0.2883.87 Chrome/55.0.2883.87 Safari/537.36']
            );
            $response = $request->send();

            if (!$response->isSuccessful()) {
                $this->context->addViolation($constraint->clientError, [
                    '%errorCode%' => $response->getStatusCode(),
                ]);
            }
        } catch (CurlException $e) {
            $this->context->addViolation($constraint->websiteDoesntExist, [
                '%url' => $value,
            ]);
        } catch (ClientErrorResponseException $e) {
            $errorCode = $e->getResponse()->getStatusCode();

            if ($errorCode === 403) {
                $this->context->addViolation($constraint->accessDenied);
            } elseif ($errorCode === 404) {
                $this->context->addViolation($constraint->resNotFound);
            } elseif ($errorCode === 405) {
                $allow = $e->getResponse()->getHeaders()['allow'];
                if (!preg_match('#GET#', $allow)) {
                    $this->context->addViolation($constraint->methodNotAllowed);
                }
            } else {
                $this->context->addViolation($constraint->clientError, [
                    '%errorCode%' => $errorCode,
                ]);
            }
        } catch (ServerErrorResponseException $e) {
            $this->context->addViolation($constraint->serverError, [
                '%errorCode%' => $e->getResponse()->getStatusCode(),
            ]);
        }
    }
}
