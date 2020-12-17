<?php

namespace HeVinci\UrlBundle\Model;

interface UrlInterface
{
    public function setUrl(string $url);

    public function getUrl(): string;

    public function setMode(string $mode);

    public function getMode(): string;

    public function setRatio(float $ratio);

    public function getRatio(): ?float;
}
