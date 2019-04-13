<?php

namespace IndieHD\AudioManipulator\Converting;

interface ConverterInterface
{
    public function setSupportedOutputFormats(array $supportedOutputFormats): void;
}
