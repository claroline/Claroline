<?php

namespace Claroline\CoreBundle\Library\Mailing;

class Message
{
    private $attributes = [];

    public function __construct()
    {
        $this->attributes['to'] = [];
        $this->attributes['cc'] = [];
        $this->attributes['bcc'] = [];
        $this->attributes['headers'] = null;
        $this->attributes['attachments'] = [];
    }

    public function to($address)
    {
        is_array($address) ?
            $this->attributes['to'] = $address :
            $this->attributes['to'][] = $address;
    }

    public function cc($address)
    {
        is_array($address) ?
          $this->attributes['cc'] = $address :
          $this->attributes['cc'][] = $address;
    }

    public function bcc($address)
    {
        is_array($address) ?
          $this->attributes['bcc'] = $address :
          $this->attributes['bcc'][] = $address;
    }

    public function from($address)
    {
        $this->attributes['from'] = $address;
    }

    public function sender($address)
    {
        $this->attributes['sender'] = $address;
    }

    public function subject($subject)
    {
        $this->attributes['subject'] = $subject;
    }

    public function tag($tag)
    {
        $this->attributes['tag'] = $tag;
    }

    public function replyTo($replyTo)
    {
        $this->attributes['reply_to'] = $replyTo;
    }

    public function body($content)
    {
        $this->attributes['body'] = $content;
    }

    public function header($key, $value)
    {
        $this->attributes['headers'][$key] = $value;
    }

    public function attach(string $name, string $url, ?string $contentType = 'application/octet-stream')
    {
        $attachment = [
            'name' => $name,
            'url' => $url,
            'type' => $contentType,
        ];

        $this->attributes['attachments'][] = $attachment;
    }

    public function getAttribute($attr)
    {
        return $this->attributes[$attr];
    }

    public function hasAttribute($attr)
    {
        return isset($this->attributes[$attr]) && !empty($this->attributes[$attr]);
    }
}
