<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\RssBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\RssBundle\Entity\Resource\RssFeed;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rss_feed")
 */
class RssFeedController extends AbstractCrudController
{
    public function getClass(): string
    {
        return RssFeed::class;
    }

    public function getName(): string
    {
        return 'rss_feed';
    }
}
