<?php namespace Mreschke\Helpers;

/**
 * Command line concole helpers.
 * @copyright 2014 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
 */
class Console
{
    private $foregroundColors;
    private $backgroundColors;
    private $noColor;
    private $log;
    private $quiet;

    /**
     * Create a new Console instance
     * @param string  $log = null
     * @param boolean $quiet = false
     */
    public function __construct($log = null, $quiet = false)
    {
        $this->foregroundColors['default'] = '0;0';
        $this->foregroundColors['black'] = '0;30';
        $this->foregroundColors['dark_gray'] = '1;30';
        $this->foregroundColors['blue'] = '0;34';
        $this->foregroundColors['light_blue'] = '1;34';
        $this->foregroundColors['green'] = '0;32';
        $this->foregroundColors['light_green'] = '1;32';
        $this->foregroundColors['cyan'] = '0;36';
        $this->foregroundColors['light_cyan'] = '1;36';
        $this->foregroundColors['red'] = '0;31';
        $this->foregroundColors['light_red'] = '1;31';
        $this->foregroundColors['purple'] = '0;35';
        $this->foregroundColors['light_purple'] = '1;35';
        $this->foregroundColors['brown'] = '0;33';
        $this->foregroundColors['yellow'] = '1;33';
        $this->foregroundColors['light_gray'] = '0;37';
        $this->foregroundColors['white'] = '1;37';

        $this->backgroundColors['black'] = '40';
        $this->backgroundColors['red'] = '41';
        $this->backgroundColors['green'] = '42';
        $this->backgroundColors['yellow'] = '43';
        $this->backgroundColors['blue'] = '44';
        $this->backgroundColors['magenta'] = '45';
        $this->backgroundColors['cyan'] = '46';
        $this->backgroundColors['light_gray'] = '47';

        $this->log = $log;
        $this->quiet = $quiet;
    }

    /**
     * Write log file if enabled
     * @param  string $output
     * @param  string $summary = 'Main'
     * @param  string $type = 'log'
     * @param  string $action = 'next'
     * @return void
     */
    public function writeLog($output, $summary = 'Main', $type = 'log', $action = 'next')
    {
        // Only write log if logging to file is enabled
        if (isset($this->log)) {
            $this->log->write($output, $summary, $type, $action);
        }
    }

    /**
     * Output a line to the console and/or the log.
     * @param  string $output
     * @param  string $summary = 'Main'
     * @param  string $type = 'log'
     * @param  string $action = 'next'
     * @return void
     */
    public function out($output, $summary = 'Main', $type = 'log', $action = 'next')
    {
        if (!$this->quiet) {
            echo $output, PHP_EOL;
        }
        $this->writeLog($output, $summary, $type, $action);
    }

    /**
     * Output a header line to the console and/or the log file
     * @param  string $output
     * @param  string $summary = 'Main'
     * @param  string $type = 'log'
     * @param  string $action = 'next'
     * @return void
     */
    public function header($output, $summary = 'Main', $type = 'log', $action = 'next')
    {
        if (!$this->quiet) {
            echo $this->color(":: ", "yellow");
            echo $this->color($output, "light_green");
            echo $this->color(" ::", "yellow");
            echo PHP_EOL;
        }
        $this->writeLog($output, $summary, $type, $action);
    }

    /**
     * Output a line item with optional indentation to the console and/or the log file
     * @param  string $output
     * @param  integer $indent = 0
     * @param  string  $summary = 'Main'
     * @param  string  $type = 'log'
     * @param  string  $action = 'next'
     * @return void
     */
    public function item($output, $indent = 0, $summary = 'Main', $type = 'log', $action = 'next')
    {
        if (!$this->quiet) {
            $color = 'green';
            if ($indent > 0) {
                $indent = ($indent * 2);
                $color = 'blue';
            }
            echo $this->color(str_repeat(" ", $indent)."* ", $color) . $output . PHP_EOL;
        }
        $this->writeLog(str_repeat(" ", $indent).$output, $summary, $type, $action);
    }

    /**
     * Output a notice line to the console and/or the log file
     * @param  string $output
     * @param  string $summary = 'Main'
     * @param  string $type = 'unusual'
     * @param  string $action = 'next'
     * @return void
     */
    public function notice($output, $summary = 'Main', $type = 'unusual', $action = 'next')
    {
        if (!$this->quiet) {
            echo $this->color($output, "yellow") . PHP_EOL;
        }
        $this->writeLog($output, $summary, $type, $action);
    }

    /**
     * Output an error line to the console and/or the log file
     * @param  string $output
     * @param  string $summary = 'Main'
     * @param  string $action = 'next'
     * @return void
     */
    public function error($output, $summary = 'Main', $action = 'next')
    {
        if (!$this->quiet) {
            file_put_contents('php://stderr', $this->color($output, "red") . PHP_EOL);
        }
        $this->writeLog($output, $summary, 'error', $action);
    }

    /**
     * Output one or more lines with defined character (ex: $c->separator(1, '#', 'blue', 2))
     * @param  int $count = 1
     * @param  string $char = ''
     * @param  string $color = 'default'
     * @param  int $linesBeforeAfter = 0
     * @return void
     */
    public function separator($count = 1, $char = '', $color = 'default', $linesBeforeAfter = 0)
    {
        $width = $this->screenWidth();
        if ($linesBeforeAfter) echo str_repeat(PHP_EOL, $linesBeforeAfter);

        for ($i=1; $i <= $count; $i++) {
            if ($char) {
                echo $this->color(str_repeat($char, $width), $color) . PHP_EOL;
            } else {
                echo PHP_EOL;
            }
        }

        if ($linesBeforeAfter) echo str_repeat(PHP_EOL, $linesBeforeAfter);
    }

    /**
     * Execute a bash command.
     * @param  string  $cmd
     * @param  boolean $outputArray
     * @param  string  $outputSeparator = PHP_EOL
     * @return mixed
     */
    public static function exec($cmd, $outputArray = false, $outputSeparator = PHP_EOL)
    {
        exec("$cmd", $output);
        if ($outputArray) {
            return $output;
        } else {
            return implode($outputSeparator, $output);
        }
    }

    /**
     * Get terminal screen width
     * @return int
     */
    public function screenWidth()
    {
        return exec('tput cols');
        #$this->settings['screen']['height'] = exec('tput lines')
    }

    /**
     * Get terminal screen height
     * @return int
     */
    public function screenHeight()
    {
        return exec('tput lines');
    }

    /**
     * Get a terminal colored string
     * @param  string $string
     * @param  string $foreground_color
     * @param  string $background_color
     * @return string
     */
    public function color($string, $foreground_color = null, $background_color = null)
    {
        $coloredString = "";

        // Check if given foreground color found
        if (isset($this->foregroundColors[$foreground_color])) {
            $coloredString .= "\033[" . $this->foregroundColors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset($this->backgroundColors[$background_color])) {
            $coloredString .= "\033[" . $this->backgroundColors[$background_color] . "m";
        }

        // Add string and end coloring
        $coloredString .=  $string . "\033[0m";

        return $coloredString;
    }

    /**
     * Alias to color
     */
    public function getColoredString($string, $foreground_color = null, $background_color = null)
    {
        return $this->color($string, $foreground_color, $background_color);
    }

    // Returns all foreground color names
    public function getForegroundColors()
    {
        return array_keys($this->foregroundColors);
    }

    // Returns all background color names
    public function getBackgroundColors()
    {
        return array_keys($this->backgroundColors);
    }

    public function inlineSed($search, $replace, $file)
    {
        $this->exec("sed -i 's`$search`$replace`g' $file");
    }
}
