<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PdfPlayerBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\PdfPlayerBundle\Entity\Pdf;

class PdfSerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return Pdf::class;
    }

    public function getName(): string
    {
        return 'pdf';
    }

    public function serialize(Pdf $pdf, array $options = []): array
    {
        return [
            'url' => $pdf->getUrl(),
            'display' => [
                'scrollMode' => $pdf->getScrollMode(),
            ],
        ];
    }

    public function deserialize(array $data, Pdf $pdf, array $options = []): Pdf
    {
        $this->sipe('url', 'setUrl', $data, $pdf);
        $this->sipe('display.scrollMode', 'setScrollMode', $data, $pdf);

        return $pdf;
    }
}
