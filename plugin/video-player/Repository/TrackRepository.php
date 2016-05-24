<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\VideoPlayerBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Resource\File;

class TrackRepository extends EntityRepository
{
    public function findByVideo(File $video)
    {
        $dql = "
            SELECT track FROM Claroline\VideoPlayerBundle\Entity\Track track
            JOIN track.video video
            WHERE video.id = :videoId
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('videoId', $video->getId());

        return $query->getResult();
    }
}
