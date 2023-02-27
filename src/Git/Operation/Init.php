<?php

namespace Util\Git\Operation;

class Init
{
    public function execute(array $args): void
    {
        if (!file_exists('dotgit')) {
            mkdir('dotgit');
        }

        if (!file_exists('dotgit/objects')) {
            mkdir('dotgit/objects');
        }

        if (!file_exists('dotgit/index')) {
            $fp = fopen('dotgit/index', 'w');
            fwrite($fp, json_encode([]));
            fclose($fp);
        }

        if (!file_exists('dotgit/HEAD')) {
            $fp = fopen('dotgit/HEAD', 'w');
            fwrite($fp, 'refs/heads/master');
            fclose($fp);
        }

        if (!file_exists('dotgit/refs')) {
            mkdir('dotgit/refs');
        }

        if (!file_exists('dotgit/refs/heads')) {
            mkdir('dotgit/refs/heads');
        }
    }
}
