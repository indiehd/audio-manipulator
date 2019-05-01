<?php

namespace IndieHD\AudioManipulator\CliCommand;

use IndieHD\AudioManipulator\CliCommand\CliCommandInterface;

interface Mid3v2CommandInterface extends CliCommandInterface
{
    public function input(string $inputFile): Mid3v2Command;

    public function quiet(): Mid3v2Command;

    public function song(string $value): Mid3v2Command;

    public function artist(string $value): Mid3v2Command;

    public function year(string $value): Mid3v2Command;

    public function comment(string $value): Mid3v2Command;

    public function album(string $value): Mid3v2Command;

    public function track(string $value): Mid3v2Command;

    public function genre(string $value): Mid3v2Command;

    public function deleteAll(): Mid3v2Command;

    public function picture(string $imageFile, string $audioFile): Mid3v2Command;

    public function removeArtwork(): Mid3v2Command;

    public function removeTags(array $tags): Mid3v2Command;
}
