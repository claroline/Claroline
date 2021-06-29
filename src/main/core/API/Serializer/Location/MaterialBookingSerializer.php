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
use Claroline\CoreBundle\Entity\Location\MaterialBooking;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;

class MaterialBookingSerializer
{
    use SerializerTrait;

    public function serialize(MaterialBooking $materialBooking, array $options = []): array
    {
        return [
            'id' => $materialBooking->getUuid(),
            'description' => $materialBooking->getDescription(),
            'dates' => DateRangeNormalizer::normalize($materialBooking->getStartDate(), $materialBooking->getEndDate()),
        ];
    }

    public function deserialize(array $data, MaterialBooking $materialBooking, array $options): MaterialBooking
    {
        $this->sipe('id', 'setUuid', $data, $materialBooking);
        $this->sipe('description', 'setDescription', $data, $materialBooking);
        $this->sipe('capacity', 'setCapacity', $data, $materialBooking);

        if (isset($data['dates'])) {
            $period = DateRangeNormalizer::denormalize($data['dates']);
            $materialBooking->setStartDate($period[0]);
            $materialBooking->setEndDate($period[1]);
        }

        return $materialBooking;
    }
}
