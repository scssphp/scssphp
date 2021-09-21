<?php

namespace ScssPhp\ScssPhp\FileReader;

interface FileReaderInterface {
    public function isDirectory(string $key) : bool;

    public function isFile(string $key) : bool;

    public function getContent(string $key) : string;

    public function getKey(string $key) : ?string;

    public function getTimestamp(string $key) : int;
}
