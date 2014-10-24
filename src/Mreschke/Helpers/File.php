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
	 * @param string $path Is prepended if set, defaults to \Snippets\Config::TMP_DIR
	 * @param string $extension is appended if set, defaults to tmp (do not include leading .)
	 * @return string
	 */
	public static function getNewTmpFile($name = '', $path = Config::TMP_DIR, $extension = 'tmp') {
		if ($name) $name .= '_';

		if (!$extension) $extension = 'tmp';

		if (substr($path, -1) != '/') $path .= "/";
		return $path.$name.String::getMd5().".tmp";
	}

	/**
	 * Find all files in a directory.
	 * Optionally excludes opened files using lsof (feature requires root)
	 * lsof is quite slow, so instead of calling this multiple times per single path
	 * you should take advantage of the $path being multiple paths (space separated).
	 * @param string $path Is the path for the find command. Can be multiple paths (space separated)
	 * @param object $console is set will use console as output else will simply echo
	 * @param boolean $excludeOpenFiles If true will use linux lsof command
	 *        to see if file is opened by another program, if so it is excluded.
	 *        lsof required script to be run as root.  lsof command takes about
	 *        1-3 seconds to run no matter how many dirs you specify in $path.
	 *        So it's fastest to call this function just once with multiple
	 *        $paths defined ($path is space separated directories).
	 * @param int $maxDepth Is passed to the find command if > 0.  If 0 maxdepth is ignored
	 * @param string $commandName is name of command that has files open, like proftpd or samba,
	 *        this helps narrow the large list that lsof returns by files open by this program
	 * @param string $grepFilter helps narrow the lsof list down even further with a grep string
	 * @depends root access, lsof
	 * @return array of custom $file assoc array
	 */
	public static function findFiles($path, $console = null, $excludeOpenFiles = false, $maxDepth = null, $commandName = null, $grepFilter = null) {
		# Find all files in $path
		# Each line is full path, ex: /store/data/Production/ftp/aaa3075/Writer_3127_2013119236.CSV
		$files = array();
		$depth = '';	
		if (isset($maxDepth)) {
			if (is_Numeric($maxDepth) && $maxDepth > 0) $depth = "-maxdepth $maxDepth";
		}
		$tmpFiles = File::getNewTmpFile('vfi_files');
		Console::exec(Config::CMD_FIND." $path -type f $depth > $tmpFiles");

		# Get list of all open files using Linux LSOF command
		if ($excludeOpenFiles) {
			#$tmpLsof = File::getNewTmpFile('vfi_lsof');
			$cmd = Config::CMD_LSOF." -Fn ";
			if (isset($commandName)) $cmd .= "-c $commandName ";
			if (isset($grepFilter)) $cmd .= "| ".Config::CMD_GREP." '$grepFilter' ";
			$cmd .= "| sed 's/^.//'";
			$openFiles = Console::exec($cmd, true);
		}

		if ($fp = fopen($tmpFiles, 'r')) {
			while (($fullpath = fgets($fp)) !== false) {
				$fullpath = preg_replace("'\n'", '', $fullpath);
				if (file_exists($fullpath)) {
					$addfile = true;

					if ($excludeOpenFiles) {
						# Check if file is open from lsof output
						#if (Console::exec("grep '$fullpath' $tmpLsof")) {
						if (in_array($fullpath, $openFiles)) {
							if (isset($console)) {
								$console->notice("Open File Found: $fullpath");
							} else {
								echo "Open File Found: $fullpath\n";
							}
							$addfile = false;
						}
					}

					if ($addfile) {
						$file = array();
						$pathinfo = pathinfo($fullpath);
						$file['filename'] = $pathinfo['basename'];
						$file['filename_no_extension'] = $pathinfo['filename'];
						$file['file'] = $pathinfo['dirname'].'/'.$pathinfo['basename'];
						$file['extension'] = $pathinfo['extension'];
						$file['path'] = $pathinfo['dirname'].'/';
						$file['file'] = $fullpath;
						$file['size'] = filesize($fullpath);
						$file['created'] = date("Y-m-d H:i:s", filemtime($fullpath));
						$files[] = $file;
					}
				}
			}

		} else {
			if (isset($console)) {
				$console->error("Cannot read $tmpFiles");
			} else {
				echo "ERROR: Cannot read $tmpFiles\n";
			}
		}

		# Remove temp files
		unlink($tmpFiles);
		#if ($excludeOpenFiles) unlink($tmpLsof);

		return $files;
	}

}
