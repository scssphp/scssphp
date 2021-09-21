<?php

namespace ScssPhp\ScssPhp\FileReader;

class FilesystemReader implements FileReaderInterface
{
    public function isDirectory(string $key): bool
    {
        return \is_dir($key);
    }

    public function isFile(string $key): bool
    {
        return \is_file($key);
    }

    /**
     * @return string|false
     * */
    public function getContent(string $key)
    {
        return \file_get_contents($key);
    }

    /**
     * @return string|false
     * */
    public function getKey(string $key)
    {
        return \realpath($key);
    }

    /**
     * @return int|false
     * */
    public function getTimestamp(string $key)
    {
        return \filemtime($key);
    }
}
