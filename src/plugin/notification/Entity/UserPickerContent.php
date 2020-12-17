<?php

namespace Icap\NotificationBundle\Entity;

class UserPickerContent
{
    private $originalText = '';
    private $finalText = '';
    private $userIds = array();

    public function __construct($text)
    {
        $this->originalText = $text;

        $usersPattern = '#<user[^>]*id=["\'](.*?)["\']>(.*?)<\/user>#i';
        $this->finalText = preg_replace_callback(
            $usersPattern,
            function ($matches) {
                array_push($this->userIds, $matches[1]);

                return $matches[2];
            },
            $text
        );
    }

    public function getOriginalText()
    {
        return $this->originalText;
    }

    public function getFinalText()
    {
        return $this->finalText;
    }

    public function getUserIds()
    {
        return $this->userIds;
    }
}
