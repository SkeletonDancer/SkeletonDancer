<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;
use Symfony\Component\Yaml\Yaml;

final class FilesystemProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            new ExpressionFunction(
                'file_exists',
                function ($filename) {
                    return sprintf('file_exists(%s)', $filename);
                },
                function (array $values, $filename) {
                    return file_exists($filename);
                }
            ),
            new ExpressionFunction(
                'read_yaml_file',
                function ($filename, $allowMissing = true) {
                    return sprintf(__CLASS__.'::readYamlFile(%s, %s)', $filename, $allowMissing ? 'true' : 'false');
                },
                function (array $values, $filename, $allowMissing = true) {
                    return static::readYamlFile($filename, $allowMissing);
                }
            ),
        ];
    }

    /** @internal */
    public static function readYamlFile($filename, $propertyPath = null, $allowUndefinedIndex = false)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException(
                sprintf('read_yaml_file: Unable to parse Yaml document. No such file: "%s".', $filename)
            );
        }

        try {
            $yaml = Yaml::parse(file_get_contents($filename), Yaml::PARSE_DATETIME);

            return PropertyAccess::createPropertyAccessorBuilder()
                ->enableExceptionOnInvalidIndex()
                ->getPropertyAccessor()
                ->getValue($yaml, $propertyPath);
        } catch (YamlParseException $e) {
            $e->setParsedFile($filename);

            throw new \InvalidArgumentException(
                sprintf('read_yaml_file: Unable to parse Yaml document. syntax error: "%s".', $e->getMessage()),
                0,
                $e
            );
        } catch (NoSuchIndexException $e) {
            if ($allowUndefinedIndex) {
                return;
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'read_yaml_file: Unable to read property from Yaml document "%s". no such index: "%s".',
                    $filename,
                    $e->getMessage()
                ),
                0,
                $e
            );
        }
    }
}
