<?php

namespace Icap\BibliographyBundle\Twig;

use Symfony\Component\Locale\Locale;

class BibliographyExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'country_extension';
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('locale', [$this, 'codeToLocale']),
        ];
    }

    public function codeToLocale($countryCode, $locale = 'en')
    {
        $c = Locale::getDisplayLanguages($locale);

        return array_key_exists($countryCode, $c)
            ? $c[$countryCode]
            : $countryCode;
    }
}
