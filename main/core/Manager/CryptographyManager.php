<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Cryptography\CryptographicKey;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.cryptography_manager")
 */
class CryptographyManager
{
    /**
     * @param ObjectManager $persistence
     *
     * @DI\InjectParams({
     *     "persistence"    = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ObjectManager $persistence
    ) {
        $this->om = $persistence;
    }

    public function generatePair()
    {
        $config = [
            'digest_alg' => 'sha512',
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        // Extract the private key from $res to $privKey
        openssl_pkey_export($res, $privKey);
        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey['key'];

        $crypto = new CryptographicKey();
        $crypto->setPublicKeyParam($pubKey);
        $crypto->setPrivateKeyParam($privKey);

        $this->om->persist($crypto);
        $this->om->flush();

        return $crypto;
    }
}
