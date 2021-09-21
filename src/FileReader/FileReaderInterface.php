<?php

namespace ScssPhp\ScssPhp\FileReader;

interface FileReaderInterface
{
    public function isDirectory(string $key): bool;

    public function isFile(string $key): bool;

    /**
     * @return string|false
     * */
    public function getContent(string $key);

    /**
     * @return string|false
     * */
    public function getKey(string $key);

    /**
     * @return int|false
     * */
    public function getTimestamp(string $key);
}
