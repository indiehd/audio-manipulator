<?php

namespace IndieHD\AudioManipulator\Effects;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface EffectInterface
{
    public function getCommand(): CliCommandInterface;
}
