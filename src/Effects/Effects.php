<?php

namespace IndieHD\AudioManipulator\Effects;

use IndieHD\AudioManipulator\Effects\EffectInterface;
use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

use IndieHD\AudioManipulator\Flac\FlacEffectInterface;
use IndieHD\AudioManipulator\Alac\AlacEffectInterface;

class Effects implements EffectInterface
{
    protected $flac;
    protected $alac;

    public function getCommand(): CliCommandInterface
    {
        return $this->command;
    }

    public function setFlac(FlacEffectInterface $flac)
    {
        $this->flac = $flac;
    }

    public function setAlac(AlacEffectInterface $alac)
    {
        $this->alac = $alac;
    }

    public function flac()
    {
        return $this->flac;
    }

    public function alac()
    {
        return $this->alac;
    }
}
