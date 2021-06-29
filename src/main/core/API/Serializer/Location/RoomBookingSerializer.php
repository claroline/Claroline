<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Serializer\Location;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Location\RoomBooking;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;

class RoomBookingSerializer
{
    use SerializerTrait;

    public function serialize(RoomBooking $roomBooking, array $options = []): array
    {
        return [
            'id' => $roomBooking->getUuid(),
            'description' => $roomBooking->getDescription(),
            'dates' => DateRangeNormalizer::normalize($roomBooking->getStartDate(), $roomBooking->getEndDate()),
        ];
    }

    public function deserialize(array $data, RoomBooking $roomBooking, array $options): RoomBooking
    {
        $this->sipe('id', 'setUuid', $data, $roomBooking);
        $this->sipe('description', 'setDescription', $data, $roomBooking);
        $this->sipe('capacity', 'setCapacity', $data, $roomBooking);

        if (isset($data['dates'])) {
            $period = DateRangeNormalizer::denormalize($data['dates']);
            $roomBooking->setStartDate($period[0]);
            $roomBooking->setEndDate($period[1]);
        }

        return $roomBooking;
    }
}
