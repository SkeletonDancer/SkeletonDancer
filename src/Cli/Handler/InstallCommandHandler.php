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

namespace SkeletonDancer\Cli\Handler;

use SkeletonDancer\Hosting;
use SkeletonDancer\Installer;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Console\Api\Args\Args;

final class InstallCommandHandler
{
    private $style;
    private $hosting;
    private $installer;

    public function __construct(SymfonyStyle $style, Hosting $hosting, Installer $installer)
    {
        $this->style = $style;
        $this->hosting = $hosting;
        $this->installer = $installer;
    }

    public function handle(Args $args)
    {
        foreach ($args->getArgument('name') as $name) {
            $this->install($name);
        }
    }

    private function install($name): void
    {
        if (!preg_match('%^(?P<name>[a-z._-]+/[a-z._-]+)(?::(?P<version>[a-z0-9._/\-]+))?%i', $name, $match)) {
            throw new \InvalidArgumentException(sprintf('Invalid name provided "%s", expected vendor-name/dance-name or vendor-name/dance-name:version', $name));
        }

        $name = preg_replace('/(\.dance)$/', '', $match['name']);
        $version = $match['version'] ?? null;

        $webUrl = $this->hosting->getWebUrl($name);
        $this->style->title('Installing "'.$name.'" ('.$webUrl.')'.(null !== $version ? ' with version '.$version : ''));

        $this->installer->install($name, $version);
        $this->style->success('Successfully installed dance "'.$name.'"');
    }
}
