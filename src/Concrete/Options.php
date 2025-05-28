<?php

namespace Concrete\Package\EasyImageGallery;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @readonly
 */
final class Options
{
    /**
     * @var string
     */
    public $lightbox;

    /**
     * @var bool
     */
    public $preloadImages;

    /**
     * @var bool
     */
    public $textFiltering;

    /**
     * @var bool
     */
    public $filtering;

    /**
     * @var int
     */
    public $galleryColumns;

    /**
     * @var bool
     */
    public $galleryTitle;

    /**
     * @var bool
     */
    public $galleryDescription;

    /**
     * @var bool
     */
    public $displayDate;

    /**
     * @var bool
     */
    public $lightboxTitle;

    /**
     * @var bool
     */
    public $lightboxDescription;

    /**
     * @var string
     */
    public $fancyOverlay;

    /**
     * @var float|int
     */
    public $fancyOverlayAlpha;

    /**
     * @var string
     */
    public $hoverColor;

    /**
     * @var string
     */
    public $hoverTitleColor;

    /**
     * @var string
     */
    public $dateFormat;

    private function __construct(array $data)
    {
        $this->setString($data, 'lightbox', 'lightbox');
        $this->setBool($data, 'preloadImages', false);
        $this->setBool($data, 'textFiltering', false);
        $this->setBool($data, 'filtering', false);
        $this->setInt($data, 'galleryColumns', 4, 1, 99);
        $this->setBool($data, 'galleryTitle', true);
        $this->setBool($data, 'galleryDescription', false);
        $this->setBool($data, 'displayDate', false);
        $this->setBool($data, 'lightboxTitle', true);
        $this->setBool($data, 'lightboxDescription', false);
        $this->setColor($data, 'fancyOverlay', '#f0f0f0');
        $this->setFloat($data, 'fancyOverlayAlpha', 0.9, 0, 1);
        $this->setColor($data, 'hoverColor', '#f0f0f0');
        $this->setColor($data, 'hoverTitleColor', '#333333');
        $this->setString($data, 'dateFormat', 'm - Y');
    }

    /**
     * @param string|null $json
     *
     * @return self
     */
    public static function fromJson($json)
    {
        $arr = $json ? json_decode($json, true) : null;

        return new self(is_array($arr) ? $arr : []);
    }

    /**
     * @return self
     */
    public static function fromUI(array $data)
    {
        return new self($data);
    }

    /**
     * @return string
     */
    public function getFancyOverlayCSSColor()
    {
        return self::getCSSColorWithAlpha($this->fancyOverlay, $this->fancyOverlayAlpha);
    }

    /**
     * @return string
     */
    public function export()
    {
        $data = (array) $this;

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param string $field
     * @param string $default
     */
    private function setString(array $data, $field, $default)
    {
        $str = $default;
        if (isset($data[$field])) {
            $value = $data[$field];
            if (is_string($value)) {
                $str = $value;
            }
        }
        $this->{$field} = $str;
    }

    /**
     * @param string $field
     */
    private function setColor(array $data, $field, $default)
    {
        $color = $default;
        if (isset($data[$field])) {
            $value = $data[$field];
            if (is_string($value) || preg_match('/^#([0-9a-f]{6}|[0-9a-f]{3})$/i', $value)) {
                $color = $value;
            }
        }

        $this->{$field} = $color;
    }

    /**
     * @param string $field
     * @param int $default
     * @param int|null $min
     * @param int|null $max
     */
    private function setInt(array $data, $field, $default, $min = null, $max = null)
    {
        $int = $default;
        if (isset($data[$field])) {
            $value = $data[$field];
            switch (gettype($value)) {
                case 'integer':
                    $int = $value;
                    break;
                case 'string':
                    if (is_string($value) && is_numeric($value)) {
                        $int = (int) $value;
                    }
                    break;
            }
            if (($min !== null && $int < $min) || ($max !== null && $int > $max)) {
                $int = $default;
            }
        }
        $this->{$field} = $int;
    }

    /**
     * @param string $field
     * @param float|int $default
     * @param float|int|null $min
     * @param float|int|null $max
     */
    private function setFloat(array $data, $field, $default, $min = null, $max = null)
    {
        $float = $default;
        if (isset($data[$field])) {
            $value = $data[$field];
            switch (gettype($value)) {
                case 'integer':
                case 'double':
                    $float = $value;
                    break;
                case 'string':
                    if (is_string($value) && is_numeric($value)) {
                        $float = (float) $value;
                    }
                    break;
            }
            if (($min !== null && $float < $min) || ($max !== null && $float > $max)) {
                $float = $default;
            }
        }
        $this->{$field} = $float;
    }

    /**
     * @param string $field
     * @param bool $default
     */
    private function setBool(array $data, $field, $default)
    {
        $bool = $default;
        if (isset($data[$field])) {
            $value = $data[$field];
            switch (gettype($value)) {
                case 'boolean':
                    $bool = $value;
                    break;
                case 'integer':
                    $bool = $value !== 0;
                    break;
                case 'string':
                    if ($value !== '') {
                        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    }
                    break;
            }
        }
        $this->{$field} = $bool;
    }

    /**
     * @param string $hexColor
     * @param float|int $alpha
     *
     * @return string
     */
    private static function getCSSColorWithAlpha($hexColor, $alpha)
    {
        $rgb = implode(',', self::hex2rgb($hexColor));

        return "rgba({$rgb}, {$alpha})";
    }

    /**
     * @param string $hex
     *
     * @return int[]
     */
    private static function hex2rgb($hex)
    {
        $m = null;
        if (preg_match('/^#?(?<r>[0-9a-f]{2})(?<g>[0-9a-f]{2})(?<b>[0-9a-f]{2})$/i', $hex, $m)) {
            return [
                hexdec($m['r']),
                hexdec($m['g']),
                hexdec($m['b'])
            ];
        }
        if (preg_match('/^#?(?<r>[0-9a-f]{1})(?<g>[0-9a-f]{1})(?<b>[0-9a-f]{1})$/i', $hex, $m)) {
            return [
                hexdec($m['r'] . $m['r']),
                hexdec($m['g'] . $m['g']),
                hexdec($m['b'] . $m['b']),
            ];
        }

        return [
            127,
            127,
            127,
        ];
    }
}
