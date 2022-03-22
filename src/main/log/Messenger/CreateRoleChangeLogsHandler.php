<?php

namespace Claroline\LogBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\GeoIp\GeoIpInfoProviderInterface;
use Claroline\LogBundle\Entity\SecurityLog;
use Claroline\LogBundle\Messenger\Message\CreateRoleChangeLogs;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateRoleChangeLogsHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var TranslatorInterface */
    private $translator;
    /** @var GeoIpInfoProviderInterface|null */
    private $geoIpInfoProvider;

    public function __construct(
        ObjectManager $om,
        TranslatorInterface $translator,
        ?GeoIpInfoProviderInterface $geoIpInfoProvider = null
    ) {
        $this->om = $om;
        $this->translator = $translator;
        $this->geoIpInfoProvider = $geoIpInfoProvider;
    }

    public function __invoke(CreateRoleChangeLogs $createLog)
    {
        $role = $this->om->getRepository(Role::class)->find($createLog->getRoleId());
        if (empty($role)) {
            return;
        }

        $doer = null;
        if ($createLog->getDoerId()) {
            $doer = $this->om->getRepository(User::class)->find($createLog->getDoerId());
        }

        foreach ($createLog->getTargetIds() as $targetId) {
            $target = $this->om->getRepository(User::class)->find($targetId);
            if (empty($target)) {
                continue;
            }

            $logEntry = new SecurityLog();

            $logEntry->setDate($createLog->getDate());
            $logEntry->setEvent($createLog->getAction());

            // this should not be done by the handler
            $logEntry->setDetails(
                $this->translator->trans($createLog->getAction().'.desc', ['username' => $target->getUsername(), 'role' => $role->getName()], 'security')
            );
            $logEntry->setDoer($doer);
            $logEntry->setTarget($target);
            $logEntry->setDoerIp($createLog->getDoerIp());

            $doerLocation = $this->getDoerLocation($createLog->getDoerIp());

            $logEntry->setCountry($doerLocation['country']);
            $logEntry->setCity($doerLocation['city']);

            $this->om->persist($logEntry);
        }

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
