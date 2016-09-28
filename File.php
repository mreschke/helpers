<?php namespace Mreschke\Helpers;

/**
 * File and folder helpers.
 * @copyright 2014 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
 */
class File
{

    /**
     * Write data to a file
     * @param  string $file full path to file
     * @param  string $data
     * @return void
     */
    public static function write($file, $data)
    {
        file_put_contents($file, $data);
    }

    /**
     * Append $data to a file.
     * @param  string $file full path to file
     * @param  string $data to write
     * @return void
     */
    public static function append($file, $data)
    {
        $fp = fopen($file, 'a');
        fwrite($fp, $data);
        fclose($fp);
    }

    /**
     * Returns a string of a new random filename.
     * @param string $name Is prepended + _ if set
     * @param string $path Is prepended if set, defaults to /tmp
     * @param string $extension is appended if set, defaults to tmp (do not include leading .)
     * @return string
     */
    public static function getNewTmpFile($name = '', $path = '/tmp', $extension = 'tmp')
    {
        if ($name) {
            $name .= '_';
        }

        if (!$extension) {
            $extension = 'tmp';
        }

        if (substr($path, -1) != '/') {
            $path .= "/";
        }
        return $path.$name.Str::getMd5().".tmp";
    }

    /**
     * Get all open files in path (recursive), uses lsof, requres root privileges to get most lsof file results
     * @param  string $path
     * @return array
     */
    public static function getOpenFiles($path)
    {
        $cmd = "lsof -Fn | grep $path | sort -u | sed 's/^.//'";
        exec($cmd, $files);
        return $files;
    }
}
