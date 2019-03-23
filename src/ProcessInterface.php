<?php

namespace IndieHD\AudioManipulator;

interface ProcessInterface
{
    public function run(string $command);

    public function setTimeout(int $seconds);

    public function isSuccessful();

    public function getOutput();
}
