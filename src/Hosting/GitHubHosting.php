<?php

declare(strict_types=1);

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SkeletonDancer\Hosting;

use SkeletonDancer\Hosting;

/**
 * GitHub hosting-provider.
 *
 * This class doesn't use the GitHub API-SDK as we only check if something exists.
 */
final class GitHubHosting implements Hosting
{
    public function supports(string $name, ?string $version, &$message): bool
    {
        $name = trim($name, '/');

        if (!$this->remoteResourceExists($url = 'https://api.github.com/repos/'.$name.'.dance')) {
            $message = 'Repository does not exists (or is protected): '.$url;

            return false;
        }

        return true;
    }

    public function getRepositoryUrl(string $name): string
    {
        if (!$this->remoteResourceExists($url = 'https://api.github.com/repos/'.$name.'.dance')) {
            throw new \InvalidArgumentException('Dance "https://github.com/'.$name.'.dance" is not supported.');
        }

        return 'https://github.com/'.$name.'.dance';
    }

    public function getWebUrl(string $name): string
    {
        if (!$this->remoteResourceExists($url = 'https://api.github.com/repos/'.$name.'.dance')) {
            throw new \InvalidArgumentException('Dance "https://github.com/'.$name.'.dance" is not supported.');
        }

        return 'https://github.com/'.$name.'.dance.git';
    }

    private function remoteResourceExists(string $url): bool
    {
        $ch = curl_init($url);

        try {
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'SkeletonDancer downloader');
            curl_exec($ch);

            return 200 === (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } finally {
            curl_close($ch);
        }
    }
}
