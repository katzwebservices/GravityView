<?php
/**
 * @license MIT
 *
 * Modified by gravityview on 08-November-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityView\Gettext\Extractors;

use GravityKit\GravityView\Gettext\Translations;
use GravityKit\GravityView\Gettext\Utils\DictionaryTrait;
use Symfony\Component\Yaml\Yaml as YamlParser;

/**
 * Class to get gettext strings from yaml.
 */
class YamlDictionary extends Extractor implements ExtractorInterface
{
    use DictionaryTrait;

    /**
     * {@inheritdoc}
     */
    public static function fromString($string, Translations $translations, array $options = [])
    {
        $messages = YamlParser::parse($string);

        if (is_array($messages)) {
            static::fromArray($messages, $translations);
        }
    }
}
