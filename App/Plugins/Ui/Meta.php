<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
 */

declare(strict_types=1);

namespace App\Plugins\Ui;

final class Meta
{
    public static function tag(string $attr, string $content): string
    {
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        return "<meta $attr content=\"$content\" />\n";
    }

    public static function name(string $name, string $content): string
    {
        return self::tag("name=\"$name\"", $content);
    }

    public static function property(string $property, string $content): string
    {
        return self::tag("property=\"$property\"", $content);
    }

    public static function og(string $key, string $value): string
    {
        return self::property("og:$key", $value);
    }

    public static function twitter(string $key, string $value): string
    {
        return self::name("twitter:$key", $value);
    }

    public static function generateHtmlTag(string $htmltag = '', bool $module = true)
    {
        $extension = pathinfo($htmltag, PATHINFO_EXTENSION);
        $modl = $module ? " type=\"module\"" : "";
        return match ($extension) {
            'css' => "<link rel=\"stylesheet\" href=\"$htmltag\" type=\"text/css\"/>\n",
            'js' => "<script src=\"$htmltag\"$modl></script>\n",
            default => null
        };
    }
}
