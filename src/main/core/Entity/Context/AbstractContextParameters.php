<?php

namespace Claroline\CoreBundle\Entity\Context;

abstract class AbstractContextParameters
{
    private ?string $defaultOpening;
    private bool $brand = true;
    private bool $breadcrumbs = true;
    private ?string $menu;

    // contact email
    // help url
    // poster
    // shortcuts
    // footer
    // terms and service ?
}
