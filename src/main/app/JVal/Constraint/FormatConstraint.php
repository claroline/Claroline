<?php

/*
 * This file is part of the JVal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\JVal\Constraint;

use DateTime;
use JVal\Constraint;
use JVal\Context;
use JVal\Exception\Constraint\InvalidTypeException;
use JVal\Types;
use JVal\Walker;
use stdClass;

/**
 * Constraint for the "format" keyword.
 */
class FormatConstraint implements Constraint
{
    /**
     * @see http://stackoverflow.com/a/1420225
     */
    const HOSTNAME_REGEX = '/^
      (?=.{1,255}$)
      [0-9a-z]
      (([0-9a-z]|-){0,61}[0-9a-z])?
      (\.[0-9a-z](?:(?:[0-9a-z]|-){0,61}[0-9a-z])?)*
      \.?
    $/ix';

    /**
     * @see http://tools.ietf.org/html/rfc3986#appendix-B
     *
     * Original regex has been modified to reject URI references. It just
     * enforces the general structure of the URI (each part, like scheme,
     * authority, etc. should be validated separately)
     */
    const URI_REGEX = '#^(([^:/?\#]+):)?//([^/?\#]*)(\?([^\#]*))?(\#(.*))?#ix';

    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['format'];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return Types::TYPE_STRING === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(stdClass $schema, Context $context, Walker $walker)
    {
        if (!is_string($schema->format)) {
            $context->enterNode('format');

            throw new InvalidTypeException($context, Types::TYPE_STRING);
        }

        // TODO: add option to treat unknown format as a schema error
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        if (!is_string($instance)) {
            $context->addViolation('should be a string');
        } elseif ('date-time' === $schema->format) {
            // PHP support for RFC3339 doesn't include fractional time
            // (milliseconds) so we must add another check if needed
            if (!$this->isDateTimeValid($instance, DATE_RFC3339)
                && !$this->isDateTimeValid($instance, 'Y-m-d\TH:i:s.uP')) {
                $context->addViolation('should be a valid date-time (RFC3339)');
            }
        } elseif ('email' === $schema->format) {
            if (!filter_var($instance, FILTER_VALIDATE_EMAIL)) {
                $context->addViolation('should be a valid email');
            }
        } elseif ('hostname' === $schema->format) {
            if (!preg_match(self::HOSTNAME_REGEX, $instance)) {
                $context->addViolation('should be a valid hostname');
            }
        } elseif ('ipv4' === $schema->format) {
            if (!filter_var($instance, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $context->addViolation('should be a valid IPv4 address');
            }
        } elseif ('ipv6' === $schema->format) {
            if (!filter_var($instance, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $context->addViolation('should be a valid IPv6 address');
            }
        } elseif ('uri' === $schema->format) {
            if (!preg_match(self::URI_REGEX, $instance)) {
                $context->addViolation('should be a valid URI (RFC3986)');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    private function isDateTimeValid($date, $format)
    {
        $dateTime = DateTime::createFromFormat($format, $date);

        if (!$dateTime) {
            return false;
        }

        $errors = DateTime::getLastErrors();

        return 0 === $errors['warning_count'] && 0 === $errors['error_count'];
    }
}
