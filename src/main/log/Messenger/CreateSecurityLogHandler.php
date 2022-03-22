<?php

namespace Claroline\LogBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\GeoIp\GeoIpInfoProviderInterface;
use Claroline\LogBundle\Entity\SecurityLog;
use Claroline\LogBundle\Messenger\Message\CreateSecurityLog;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CreateSecurityLogHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var GeoIpInfoProviderInterface|null */
    private $geoIpInfoProvider;

    public function __construct(
        ObjectManager $om,
        ?GeoIpInfoProviderInterface $geoIpInfoProvider = null
    ) {
        $this->om = $om;
        $this->geoIpInfoProvider = $geoIpInfoProvider;
    }

    public function __invoke(CreateSecurityLog $createLog)
    {
        $logEntry = new SecurityLog();

        $logEntry->setDate($createLog->getDate());
        $logEntry->setEvent($createLog->getAction());
        $logEntry->setDetails($createLog->getDetails());

        if ($createLog->getDoerId()) {
            $doer = $this->om->getRepository(User::class)->find($createLog->getDoerId());
            $logEntry->setDoer($doer);
        }

        if ($createLog->getTargetId()) {
            $target = $this->om->getRepository(User::class)->find($createLog->getTargetId());
            $logEntry->setTarget($target);
        }

        $logEntry->setDoerIp($createLog->getDoerIp());

        $doerLocation = $this->getDoerLocation($createLog->getDoerIp());

        $logEntry->setCountry($doerLocation['country']);
        $logEntry->setCity($doerLocation['city']);

        $this->om->persist($logEntry);
        $this->om->flush();
    }

    private function getDoerLocation(string $doerIp): array
    {
        $doerCountry = null;
        $doerCity = null;
        if ($this->geoIpInfoProvider && 'CLI' !== $doerIp) {
            $geoIpInfo = $this->geoIpInfoProvider->getGeoIpInfo($doerIp);

            if ($geoIpInfo) {
                $doerCountry = $geoIpInfo->getCountry();
                $doerCity = $geoIpInfo->getCity();
            }
        }

        return [
            'city' => $doerCity,
            'country' => $doerCountry,
        ];
    }
}
