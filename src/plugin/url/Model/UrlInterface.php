<?php

namespace HeVinci\UrlBundle\Model;

interface UrlInterface
{
    public function setUrl(string $url): void;

    public function getUrl(): ?string;

    public function setMode(string $mode): void;

    public function getMode(): string;

    public function setRatio(float $ratio): void;

    public function getRatio(): ?float;
}
