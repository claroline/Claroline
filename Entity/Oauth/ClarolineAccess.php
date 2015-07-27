<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Oauth;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_api_claroline_access")
 * @ORM\Entity
 */
class ClarolineAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Oauth\Client", inversedBy="authCodes")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $client;

    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", nullable=false)
     */
    protected $accessToken;

    public function getId()
    {
        return $this->id;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }
}
