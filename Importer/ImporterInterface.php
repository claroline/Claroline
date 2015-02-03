<?php

namespace Icap\PortfolioBundle\Importer;

use Claroline\CoreBundle\Entity\User;

interface ImporterInterface
{
    /**
     * @return string
     */
    public function getFormat();

    /**
     * @return string
     */
    public function getFormatLabel();

    /**
     * @param string $content
     * @param User    $user
     *
     * @return mixed
     */
    public function import($content, User $user);
}
