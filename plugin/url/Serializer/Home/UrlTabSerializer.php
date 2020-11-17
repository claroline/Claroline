<?php

namespace HeVinci\UrlBundle\Serializer\Home;

use HeVinci\UrlBundle\Entity\Home\UrlTab;
use HeVinci\UrlBundle\Serializer\AbstractUrlSerializer;

class UrlTabSerializer extends AbstractUrlSerializer
{
    public function getName()
    {
        return 'home_url_tab';
    }

    public function getClass()
    {
        return UrlTab::class;
    }
}
