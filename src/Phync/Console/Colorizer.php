<?php
/**
 * This file is part of Phync.
 *
 * (c) Yuya Takeyama
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Phync_Console_Colorizer
{
    private static $styles = array(
        'off'        => 0,
        'bold'       => 1,
        'underscore' => 4,
        'blink'      => 5,
        'reverse'    => 7,
        'concealed'  => 8,
        'black'      => 30,
        'red'        => 31,
        'green'      => 32,
        'yellow'     => 33,
        'blue'       => 34,
        'magenta'    => 35,
        'cyan'       => 36,
        'white'      => 37,
        'on_black'   => 40,
        'on_red'     => 41,
        'on_green'   => 42,
        'on_yellow'  => 43,
        'on_blue'    => 44,
        'on_magenta' => 45,
        'on_cyan'    => 46,
        'on_white'   => 47,
    );

    public function color($text)
    {
        $args = func_get_args();
        $text = array_shift($args);
        $attrs = '';
        foreach ($args as $color) {
            if ($attrs !== '') {
                $attrs .= ';';
            }
            $attrs .= self::$styles[$color];
        }
        if ($attrs !== '') {
            return "\e[{$attrs}m{$text}\e[0m";
        } else {
            return $text;
        }
    }
}
