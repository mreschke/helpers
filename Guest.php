<?php namespace Mreschke\Helpers;

/**
 * Guest (remote user) helpers.
 * @copyright 2014 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
 */
Class Guest
{
	
	/**
	 * Get the users operating system.
	 * Uses the HTTP_USER_AGENT server variable
	 * @author http://www.geekpedia.com/code47_Detect-operating-system-from-user-agent-string.html
	 * @return [type] [description]
	 */
	public static function getOs()
	{
		$osList = array (
			'Windows 3.11' => 'Win16',
			'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
			'Windows 98' => '(Windows 98)|(Win98)',
			'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
			'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
			'Windows Server 2003' => '(Windows NT 5.2)',
			'Windows Vista' => '(Windows NT 6.0)',
			'Windows 7' => '(Windows NT 7.0)',
			'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
			'Windows ME' => 'Windows ME',
			'Open BSD' => 'OpenBSD',
			'Sun OS' => 'SunOS',
			'Android' => 'Android',
			'Linux' => '(Linux)|(X11)',
			'Mac OS' => '(Mac_PowerPC)|(Macintosh)',
			'QNX' => 'QNX',
			'BeOS' => 'BeOS',
			'OS/2' => 'OS/2',
			'Search Bot'=>'(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp)|(MSNBot)|(Ask Jeeves/Teoma)|(ia_archiver)'
		);
		$os = 'unknown';
		foreach($osList as $os => $match) {
			if (preg_match("'".$match."'", @$_SERVER['HTTP_USER_AGENT'])) {
				break;
			}
		}
		return $os;
	}

	/**
	 * Gets the users browser and optionally name and version (like Firefox 6.0.1).
	 * @param  boolean $includeVersion
	 * @return string
	 */
	public static function getBrowser($includeVersion = false)
	{
		$browsers = array(
			"firefox", "msie", "opera", "chrome", "safari",
			"mozilla", "seamonkey", "konqueror", "netscape",
			"gecko", "navigator", "mosaic", "lynx", "amaya",
			"omniweb", "avant", "camino", "flock", "aol",
			"wget", "curl", "cadaver"
		);
		$agent = strtolower(@$_SERVER['HTTP_USER_AGENT']);
        $name = '';
        $version = '';
		foreach($browsers as $browser) {
			if (preg_match("#($browser)[/ ]?([0-9.]*)#i", $agent, $match)) {
				$name = $match[1] ;
				$version = $match[2] ;
				break ;
			}
		}
		if (strlen($name) > 0) $name = strtoupper(substr($name, 0, 1)) . substr($name, 1);
		if ($includeVersion) {
			return $name.' '.$version;	
		} else {
			return $name;
		}
	}

}
