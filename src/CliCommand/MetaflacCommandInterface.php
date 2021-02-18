<?php

namespace IndieHD\AudioManipulator\CliCommand;

interface MetaflacCommandInterface extends CliCommandInterface
{
    public function input(string $inputFile): MetaflacCommand;

    public function output(string $outputFile): MetaflacCommand;

    public function removeAll(): MetaflacCommand;

    public function removeTags(array $tags): MetaflacCommand;

    public function setTag(string $field, string $value): MetaflacCommand;

    public function removeBlockType(array $types): MetaflacCommand;

    public function importPicture(string $fileOrSpecification): MetaflacCommand;
}
