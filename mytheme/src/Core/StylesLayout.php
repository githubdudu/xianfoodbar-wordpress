<?php

namespace App\Core;

use JsonSerializable;

class StylesLayout implements JsonSerializable
{
    // White
    public const White = "#ffffff";
    // Black
    public const Black = "#000000";
    // Red
    public const Red_1 = "#fff1f0";
    public const Red_2 = "#ffccc7";
    public const Red_3 = "#ffa39e";
    public const Red_4 = "#ff7875";
    public const Red_5 = "#ff4d4f";
    public const Red_6 = "#f5222d";
    public const Red_7 = "#cf1322";
    public const Red_8 = "#a8071a";
    public const Red_9 = "#820014";
    public const Red_10 = "#5c0011";
    // Volcano
    public const Volcano_1 = "#fff2e8";
    public const Volcano_2 = "#ffd8bf";
    public const Volcano_3 = "#ffbb96";
    public const Volcano_4 = "#ff9c6e";
    public const Volcano_5 = "#ff7a45";
    public const Volcano_6 = "#fa541c";
    public const Volcano_7 = "#d4380d";
    public const Volcano_8 = "#ad2102";
    public const Volcano_9 = "#871400";
    public const Volcano_10 = "#610b00";
    // Sunset Orange
    public const Orange_1 = "#fff7e6";
    public const Orange_2 = "#ffe7ba";
    public const Orange_3 = "#ffd591";
    public const Orange_4 = "#ffc069";
    public const Orange_5 = "#ffa940";
    public const Orange_6 = "#fa8c16";
    public const Orange_7 = "#d46b08";
    public const Orange_8 = "#ad4e00";
    public const Orange_9 = "#873800";
    public const Orange_10 = "#612500";
    // Calendula Gold
    public const Gold_1 = "#fffbe6";
    public const Gold_2 = "#fff1b8";
    public const Gold_3 = "#ffe58f";
    public const Gold_4 = "#ffd666";
    public const Gold_5 = "#ffc53d";
    public const Gold_6 = "#faad14";
    public const Gold_7 = "#d48806";
    public const Gold_8 = "#ad6800";
    public const Gold_9 = "#874d00";
    public const Gold_10 = "#613400";
    // Sunrise Yellow
    public const Yellow_1 = "#feffe6";
    public const Yellow_2 = "#ffffb8";
    public const Yellow_3 = "#fffb8f";
    public const Yellow_4 = "#fff566";
    public const Yellow_5 = "#ffec3d";
    public const Yellow_6 = "#fadb14";
    public const Yellow_7 = "#d4b106";
    public const Yellow_8 = "#ad8b00";
    public const Yellow_9 = "#876800";
    public const Yellow_10 = "#614700";
    // Lime
    public const Lime_1 = "#fcffe6";
    public const Lime_2 = "#f4ffb8";
    public const Lime_3 = "#eaff8f";
    public const Lime_4 = "#d3f261";
    public const Lime_5 = "#bae637";
    public const Lime_6 = "#a0d911";
    public const Lime_7 = "#7cb305";
    public const Lime_8 = "#5b8c00";
    public const Lime_9 = "#3f6600";
    public const Lime_10 = "#254000";
    // Polar Green
    public const Green_1 = "#f6ffed";
    public const Green_2 = "#d9f7be";
    public const Green_3 = "#b7eb8f";
    public const Green_4 = "#95de64";
    public const Green_5 = "#73d13d";
    public const Green_6 = "#52c41a";
    public const Green_7 = "#389e0d";
    public const Green_8 = "#237804";
    public const Green_9 = "#135200";
    public const Green_10 = "#092b00";
    // Cyan
    public const Cyan_1 = "#e6fffb";
    public const Cyan_2 = "#b5f5ec";
    public const Cyan_3 = "#87e8de";
    public const Cyan_4 = "#5cdbd3";
    public const Cyan_5 = "#36cfc9";
    public const Cyan_6 = "#13c2c2";
    public const Cyan_7 = "#08979c";
    public const Cyan_8 = "#006d75";
    public const Cyan_9 = "#00474f";
    public const Cyan_10 = "#002329";
    // Daybreak Blue
    public const Blue_1 = "#e6f7ff";
    public const Blue_2 = "#bae7ff";
    public const Blue_3 = "#91d5ff";
    public const Blue_4 = "#69c0ff";
    public const Blue_5 = "#40a9ff";
    public const Blue_6 = "#1890ff";
    public const Blue_7 = "#096dd9";
    public const Blue_8 = "#0050b3";
    public const Blue_9 = "#003a8c";
    public const Blue_10 = "#002766";
    // Geek Blue
    public const GeekBlue_1 = "#f0f5ff";
    public const GeekBlue_2 = "#d6e4ff";
    public const GeekBlue_3 = "#adc6ff";
    public const GeekBlue_4 = "#85a5ff";
    public const GeekBlue_5 = "#597ef7";
    public const GeekBlue_6 = "#2f54eb";
    public const GeekBlue_7 = "#1d39c4";
    public const GeekBlue_8 = "#10239e";
    public const GeekBlue_9 = "#061178";
    public const GeekBlue_10 = "#030852";
    // Golden Purple
    public const Purple_1 = "#f9f0ff";
    public const Purple_2 = "#efdbff";
    public const Purple_3 = "#d3adf7";
    public const Purple_4 = "#b37feb";
    public const Purple_5 = "#9254de";
    public const Purple_6 = "#722ed1";
    public const Purple_7 = "#531dab";
    public const Purple_8 = "#391085";
    public const Purple_9 = "#22075e";
    public const Purple_10 = "#120338";
    // Magenta
    public const Magenta_1 = "#fff0f6";
    public const Magenta_2 = "#ffd6e7";
    public const Magenta_3 = "#ffadd2";
    public const Magenta_4 = "#ff85c0";
    public const Magenta_5 = "#f759ab";
    public const Magenta_6 = "#eb2f96";
    public const Magenta_7 = "#c41d7f";
    public const Magenta_8 = "#9e1068";
    public const Magenta_9 = "#780650";
    public const Magenta_10 = "#520339";
    // Gray
    public const Gray_1 = "#fafafa";
    public const Gray_2 = "#f5f5f5";
    public const Gray_3 = "#f0f0f0";
    public const Gray_4 = "#d9d9d9";
    public const Gray_5 = "#bfbfbf";
    public const Gray_6 = "#8c8c8c";
    public const Gray_7 = "#595959";
    public const Gray_8 = "#434343";
    public const Gray_9 = "#262626";
    public const Gray_10 = "#1f1f1f";
    public const Gray_11 = "#141414";
    // Antd Color
    public const Color_1 = "#e6f7ff";
    public const Color_2 = "#bae7ff";
    public const Color_3 = "#91d5ff";
    public const Color_4 = "#69c0ff";
    public const Color_5 = "#40a9ff";
    public const Color_6 = "#1890ff";
    public const Color_7 = "#096dd9";
    public const Color_8 = "#0050b3";
    public const Color_9 = "#003a8c";
    public const Color_10 = "#002766";


    private array $json_data = [];

    // 随机颜色
    public static function randomColor()
    {
        $color = "";
        for($i = 0; $i < 6; $i ++) {
            $color .= dechex(rand(0, 15));
        }
        return '#' . $color;
    }

    public static function rgba(int $red, int $green, int $blue, float $alpha)
    {
        return "rgba({$red}, {$green}, {$blue}, {$alpha})";
    }

    public function setFontColor(string $color): StylesLayout
    {
        $this->json_data['color'] = $color;
        return $this;
    }

    public function setFontSize(int $size): StylesLayout
    {
        $this->json_data['fontSize'] = $size;
        return $this;
    }

    public function setBackgroundColor(string $color): StylesLayout
    {
        $this->json_data['backgroundColor'] = $color;
        return $this;
    }

    public function setBorderColor(string $color): StylesLayout
    {
        $this->json_data['borderColor'] = $color;
        return $this;
    }

    public function setBorder(int $size, string $type = "solid", string $color = self::Black, string $position = ""): StylesLayout
    {
        $name = 'border';
        if (!empty($position)) {
            $name .= '_' . $position;
        }

        if ($size === 0) {
            $this->json_data[$name] = sprintf("%d", $size);
        } else {
            $this->json_data[$name] = sprintf("%dpx %s %s", $size, $type, $color);
        }

        return $this;
    }



    public function jsonSerialize(): mixed
    {
        return $this->json_data;
    }
}
