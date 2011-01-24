<?php
/* ------ Block plugin ------ */

/**
 * Dwoo block function, provides gettext support for dwoo.
 *
 * The block content is the text that should be translated.
 *
 * Any parameter that is sent to the function will be represented as %n in the translation text,
 * where n is 1 for the first parameter. The following parameters are reserved:
 * - plural - The plural version of the text (2nd parameter of ngettext())
 * - count - The item count for plural mode (3rd parameter of ngettext())
 * - escape - sets escape mode:
 *	- 'html' for HTML escaping, this is the default.
 *	- 'js' for javascript escaping.
 *	- 'url' for url escaping.
 * - lower - lowercases the output.
 */


function Dwoo_Plugin_t(Dwoo $dwoo, $text, $plural=null, $count=null, $escape=null, $lower=false, array $rest=array()) {
	if ($plural && $count) {
		$res = ngettext($text, $plural, $count);
	} else {
		$res = _gettext($text);
	}
	if ($rest) {
		$res = plugin_t_helper_replaceArgs($res, $rest);
	}
	
	if ($lower) {
		$res = strtolower($res);
	}
	
	if ($escape) {
		switch ($escape) {
			case 'javascript':
			case 'js':
				// javascript escape
				$res = str_replace('\'', '\\\'', stripslashes($res));
				break;
			case 'url':
				// url escape
				$res = urlencode($res);
				break;
			case 'html':
				//html escape
				$res= nl2br(htmlspecialchars($res));
				break;
		}
	}
	
	return $res;
}

function plugin_t_helper_replaceArgs($str, $args)
{
	$tr = array();
	$p = 0;

	foreach ($args as $arg) {
		$tr['%'.++$p] = $arg;
	}

	return strtr($str, $tr);
}
?>
