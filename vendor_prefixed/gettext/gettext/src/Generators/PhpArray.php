<?php
/**
 * @license MIT
 *
 * Modified by gravityview on 07-November-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityView\Gettext\Generators;

use GravityKit\GravityView\Gettext\Translations;
use GravityKit\GravityView\Gettext\Utils\MultidimensionalArrayTrait;

class PhpArray extends Generator implements GeneratorInterface
{
    use MultidimensionalArrayTrait;

    public static $options = [
        'includeHeaders' => true,
    ];

    /**
     * {@inheritdoc}
     */
    public static function toString(Translations $translations, array $options = [])
    {
        $array = static::generate($translations, $options);

        return '<?php return '.var_export($array, true).';';
    }

    /**
     * Generates an array with the translations.
     *
     * @param Translations $translations
     * @param array        $options
     *
     * @return array
     */
    public static function generate(Translations $translations, array $options = [])
    {
        $options += static::$options;

        return static::toArray($translations, $options['includeHeaders'], true);
    }
}
