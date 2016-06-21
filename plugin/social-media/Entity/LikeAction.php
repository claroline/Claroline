<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/22/15
 */

namespace Icap\SocialmediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="icap__socialmedia_like")
 * @ORM\Entity(repositoryClass="Icap\SocialmediaBundle\Repository\LikeActionRepository")
 *
 * Class LikeAction
 */
class LikeAction extends ActionBase
{
}
