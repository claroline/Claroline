<?php

/**
 * Use this class to create an .ics file.
 *
 * Usage
 * -----------------------------------------------------------------------------
 * Basic usage - generate ics file contents (see below for available properties):
 *   $ics = new ICS($props);
 *   $ics_file_contents = $ics->toString();
 *
 * Available properties
 * -----------------------------------------------------------------------------
 * description
 *   String description of the event.
 * dtend
 *   A date/time stamp designating the end of the event. You can use either a
 *   DateTime object or a PHP datetime format string (e.g. "now + 1 hour").
 * dtstart
 *   A date/time stamp designating the start of the event. You can use either a
 *   DateTime object or a PHP datetime format string (e.g. "now + 1 hour").
 * location
 *   String address or description of the location of the event.
 * summary
 *   String short summary of the event - usually used as the title.
 * url
 *   A url to attach to the event. Make sure to add the protocol (http://
 *   or https://).
 */

namespace Claroline\CoreBundle\Library\ICS;

class ICS
{
    public const DT_FORMAT = 'Ymd\THis\Z';

    private array $properties = [];

    private array $availableProperties = [
        'description',
        'dtend',
        'dtstart',
        'location',
        'summary',
        'url',
    ];

    public function __construct(array $props)
    {
        foreach ($props as $k => $v) {
            if (in_array($k, $this->availableProperties)) {
                $this->properties[$k] = $this->sanitize($v, $k);
            }
        }
    }

    public function toString(): string
    {
        $rows = $this->build();

        return implode("\r\n", $rows);
    }

    private function build(): array
    {
        // Build ICS properties - add header
        $icsProps = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//hacksw/handcal//NONSGML v1.0//EN',
            'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
        ];

        // Build ICS properties - add header
        $props = [];
        foreach ($this->properties as $k => $v) {
            $props[strtoupper($k.('url' === $k ? ';VALUE=URI' : ''))] = $v;
            if ('description' === $k) {
                // this is required for Outlook. It doesn't interpret HTML otherwise
                $props['X-ALT-DESC;FMTTYPE=text/html'] = $v;
            }
        }

        // Set some default values
        $props['DTSTAMP'] = $this->formatTimestamp('now');
        $props['UID'] = uniqid();

        // Append properties
        foreach ($props as $k => $v) {
            $icsProps[] = "$k:$v";
        }

        // Build ICS properties - add footer
        $icsProps[] = 'END:VEVENT';
        $icsProps[] = 'END:VCALENDAR';

        return $icsProps;
    }

    private function sanitize($val, $key = false): ?string
    {
        switch ($key) {
            case 'dtend':
            case 'dtstamp':
            case 'dtstart':
                $val = $this->formatTimestamp($val);
                break;
        }

        return $val;
    }

    private function formatTimestamp($timestamp): string
    {
        $dt = new \DateTime($timestamp);

        return $dt->format(self::DT_FORMAT);
    }
}
