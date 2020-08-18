<?php
/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp;

/**
 * CSS Colors
 *
 * @author Leaf Corcoran <leafot@gmail.com>
 */
class Colors
{
    /**
     * CSS Colors
     *
     * @see http://www.w3.org/TR/css3-color
     *
     * Note: these are in reverse alphabetical order so that colors with multiple
     * names will use the alphabetically first option in RGBaToColorName.
     *
     * @var array
     */
    protected static $cssColors = [
        'yellowgreen' => '154,205,50',
        'yellow' => '255,255,0',
        'whitesmoke' => '245,245,245',
        'white' => '255,255,255',
        'wheat' => '245,222,179',
        'violet' => '238,130,238',
        'turquoise' => '64,224,208',
        'transparent' => '0,0,0,0',
        'tomato' => '255,99,71',
        'thistle' => '216,191,216',
        'teal' => '0,128,128',
        'tan' => '210,180,140',
        'steelblue' => '70,130,180',
        'springgreen' => '0,255,127',
        'snow' => '255,250,250',
        'slategrey' => '112,128,144',
        'slategray' => '112,128,144',
        'slateblue' => '106,90,205',
        'skyblue' => '135,206,235',
        'silver' => '192,192,192',
        'sienna' => '160,82,45',
        'seashell' => '255,245,238',
        'seagreen' => '46,139,87',
        'sandybrown' => '244,164,96',
        'salmon' => '250,128,114',
        'saddlebrown' => '139,69,19',
        'royalblue' => '65,105,225',
        'rosybrown' => '188,143,143',
        'red' => '255,0,0',
        'rebeccapurple' => '102,51,153',
        'purple' => '128,0,128',
        'powderblue' => '176,224,230',
        'plum' => '221,160,221',
        'pink' => '255,192,203',
        'peru' => '205,133,63',
        'peachpuff' => '255,218,185',
        'papayawhip' => '255,239,213',
        'palevioletred' => '219,112,147',
        'paleturquoise' => '175,238,238',
        'palegreen' => '152,251,152',
        'palegoldenrod' => '238,232,170',
        'orchid' => '218,112,214',
        'orangered' => '255,69,0',
        'orange' => '255,165,0',
        'olivedrab' => '107,142,35',
        'olive' => '128,128,0',
        'oldlace' => '253,245,230',
        'navy' => '0,0,128',
        'navajowhite' => '255,222,173',
        'moccasin' => '255,228,181',
        'mistyrose' => '255,228,225',
        'mintcream' => '245,255,250',
        'midnightblue' => '25,25,112',
        'mediumvioletred' => '199,21,133',
        'mediumturquoise' => '72,209,204',
        'mediumspringgreen' => '0,250,154',
        'mediumslateblue' => '123,104,238',
        'mediumseagreen' => '60,179,113',
        'mediumpurple' => '147,112,219',
        'mediumorchid' => '186,85,211',
        'mediumblue' => '0,0,205',
        'mediumaquamarine' => '102,205,170',
        'maroon' => '128,0,0',
        'magenta' => '255,0,255',
        'linen' => '250,240,230',
        'limegreen' => '50,205,50',
        'lime' => '0,255,0',
        'lightyellow' => '255,255,224',
        'lightsteelblue' => '176,196,222',
        'lightslategrey' => '119,136,153',
        'lightslategray' => '119,136,153',
        'lightskyblue' => '135,206,250',
        'lightseagreen' => '32,178,170',
        'lightsalmon' => '255,160,122',
        'lightpink' => '255,182,193',
        'lightgrey' => '211,211,211',
        'lightgreen' => '144,238,144',
        'lightgray' => '211,211,211',
        'lightgoldenrodyellow' => '250,250,210',
        'lightcyan' => '224,255,255',
        'lightcoral' => '240,128,128',
        'lightblue' => '173,216,230',
        'lemonchiffon' => '255,250,205',
        'lawngreen' => '124,252,0',
        'lavenderblush' => '255,240,245',
        'lavender' => '230,230,250',
        'khaki' => '240,230,140',
        'ivory' => '255,255,240',
        'indigo' => '75,0,130',
        'indianred' => '205,92,92',
        'hotpink' => '255,105,180',
        'honeydew' => '240,255,240',
        'grey' => '128,128,128',
        'greenyellow' => '173,255,47',
        'green' => '0,128,0',
        'gray' => '128,128,128',
        'goldenrod' => '218,165,32',
        'gold' => '255,215,0',
        'ghostwhite' => '248,248,255',
        'gainsboro' => '220,220,220',
        'fuchsia' => '255,0,255',
        'forestgreen' => '34,139,34',
        'floralwhite' => '255,250,240',
        'firebrick' => '178,34,34',
        'dodgerblue' => '30,144,255',
        'dimgrey' => '105,105,105',
        'dimgray' => '105,105,105',
        'deepskyblue' => '0,191,255',
        'deeppink' => '255,20,147',
        'darkviolet' => '148,0,211',
        'darkturquoise' => '0,206,209',
        'darkslategrey' => '47,79,79',
        'darkslategray' => '47,79,79',
        'darkslateblue' => '72,61,139',
        'darkseagreen' => '143,188,143',
        'darksalmon' => '233,150,122',
        'darkred' => '139,0,0',
        'darkorchid' => '153,50,204',
        'darkorange' => '255,140,0',
        'darkolivegreen' => '85,107,47',
        'darkmagenta' => '139,0,139',
        'darkkhaki' => '189,183,107',
        'darkgrey' => '169,169,169',
        'darkgreen' => '0,100,0',
        'darkgray' => '169,169,169',
        'darkgoldenrod' => '184,134,11',
        'darkcyan' => '0,139,139',
        'darkblue' => '0,0,139',
        'cyan' => '0,255,255',
        'crimson' => '220,20,60',
        'cornsilk' => '255,248,220',
        'cornflowerblue' => '100,149,237',
        'coral' => '255,127,80',
        'chocolate' => '210,105,30',
        'chartreuse' => '127,255,0',
        'cadetblue' => '95,158,160',
        'burlywood' => '222,184,135',
        'brown' => '165,42,42',
        'blueviolet' => '138,43,226',
        'blue' => '0,0,255',
        'blanchedalmond' => '255,235,205',
        'black' => '0,0,0',
        'bisque' => '255,228,196',
        'beige' => '245,245,220',
        'azure' => '240,255,255',
        'aquamarine' => '127,255,212',
        'aqua' => '0,255,255',
        'antiquewhite' => '250,235,215',
        'aliceblue' => '240,248,255',
    ];

    /**
     * Convert named color in a [r,g,b[,a]] array
     *
     * @param string $colorName
     *
     * @return array|null
     */
    public static function colorNameToRGBa($colorName)
    {
        if (\is_string($colorName) && isset(static::$cssColors[$colorName])) {
            $rgba = explode(',', static::$cssColors[$colorName]);

            // only case with opacity is transparent, with opacity=0, so we can intval on opacity also
            $rgba = array_map('intval', $rgba);

            return $rgba;
        }

        return null;
    }

    /**
     * Reverse conversion : from RGBA to a color name if possible
     *
     * @param integer $r
     * @param integer $g
     * @param integer $b
     * @param integer $a
     *
     * @return string|null
     */
    public static function RGBaToColorName($r, $g, $b, $a = 1)
    {
        static $reverseColorTable = null;

        if (! is_numeric($r) || ! is_numeric($g) || ! is_numeric($b) || ! is_numeric($a)) {
            return null;
        }

        if ($a < 1) {
            return null;
        }

        if (\is_null($reverseColorTable)) {
            $reverseColorTable = [];

            foreach (static::$cssColors as $name => $rgb_str) {
                $rgb_str = explode(',', $rgb_str);

                if (\count($rgb_str) == 3) {
                    $reverseColorTable[\intval($rgb_str[0])][\intval($rgb_str[1])][\intval($rgb_str[2])] = $name;
                }
            }
        }

        if (isset($reverseColorTable[\intval($r)][\intval($g)][\intval($b)])) {
            return $reverseColorTable[\intval($r)][\intval($g)][\intval($b)];
        }

        return null;
    }
}
