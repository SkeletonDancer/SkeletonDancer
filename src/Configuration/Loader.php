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

namespace SkeletonDancer\Configuration;

use JsonSchema\Validator;
use Seld\JsonLint\JsonParser;
use SkeletonDancer\Dance;
use Symfony\Component\Filesystem\Exception\IOException;

class Loader
{
    const CONFIG_NAME = '.dance.json';

    private $jsonParser;
    private $validator;
    private $schema;

    public function __construct()
    {
        $this->jsonParser = new JsonParser();
        $this->validator = new Validator();
        $this->schema = json_decode(file_get_contents(__DIR__.'/schema.json'));
    }

    public function load(string $danceDirectory, string $name): Dance
    {
        $danceDirectory = rtrim(str_replace('\\', '/', $danceDirectory), '/');

        if (!file_exists($danceDirectory.'/'.self::CONFIG_NAME)) {
            throw new IOException(
                sprintf('Config file "%s" does not exist in "%s".', self::CONFIG_NAME, $danceDirectory)
            );
        }

        $configRaw = file_get_contents($danceDirectory.'/'.self::CONFIG_NAME);
        if ($error = $this->jsonParser->lint($configRaw)) {
            throw new \InvalidArgumentException($error->getMessage().PHP_EOL.'in '.$danceDirectory.'/'.self::CONFIG_NAME, $error->getCode(), $error);
        }

        // Validator expects an object.
        $this->validate(json_decode($configRaw), $danceDirectory.'/'.self::CONFIG_NAME);

        return $this->buildConfig($name, $danceDirectory, json_decode($configRaw, true));
    }

    private function validate(\stdClass $data, string $filename): void
    {
        $this->validator->reset();
        $this->validator->validate($data, $this->schema);

        if ($errors = $this->validator->getErrors()) {
            $errorString = [];

            foreach ($this->validator->getErrors() as $error) {
                $errorString[] = sprintf('  * [%s] %s', $error['property'], $error['message']);
            }

            throw new \InvalidArgumentException(
                sprintf('Invalid configuration in "%s":%s', $filename, PHP_EOL.implode(PHP_EOL, $errorString))
            );
        }
    }

    private function buildConfig(string $name, string $directory, array $config): Dance
    {
        $dance = new Dance($name, $directory, $config['questioners'] ?? [], $config['generators'] ?? []);
        $dance->title = $config['title'];
        $dance->description = $config['title'];
        $dance->autoloading = $config['autoloading'] ?? [];

        return $dance;
    }
}
