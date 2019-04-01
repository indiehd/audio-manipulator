<?php

namespace IndieHD\AudioManipulator\Processing;

interface ProcessInterface
{
    public function run(callable $callback = null, $env = []);

    public function setTimeout(int $seconds);

    public function isSuccessful();

    public function getOutput();

    public function getErrorOutput();
}
