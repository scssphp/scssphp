<?php

namespace ScssPhp\ScssPhp\FileReader;

class FilesystemReader implements FileReaderInterface {
    public function isDirectory(string $key) : bool {
        return \is_dir($key);
    }

    public function isFile(string $key) : bool {
        return \is_file($key);
    }

    public function getContent(string $key) : string {
        return \file_get_contents($key);
    }

    public function getKey(string $key) : ?string {
        return \realpath($key);
    }

    public function getTimestamp(string $key) : int {
        return \filemtime($key);
    }
}
