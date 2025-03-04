<?php
/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *  
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
/*
 * Last Updated: $Date$
 *
 * @author 		$Author$
 * @author    Mikolaj Jedrzejak <mikolajj@op.pl>
 *
 * @package		kusaba
 * @subpackage	board
 *
 * @version		$Revision$
 *
 * This library is a modified version of ConvertCharset.class.php provided by Mikolaj Jedrzejak.
 *
 */

 
class kxCharset {

  /*
   * This value keeps information if output should be with numeric entities.
   *
   * @var 		boolean
   */
	protected $entities = false;
  
  /*
   * charset conversion method to use.
   * Valid values: tables, mbstrings, iconv, recode,
   *
   * @var 		string
   */
	public $encodingMethod = 'tables';
	
	/**
	 * Path where tables are located
	 *
	 * @var string
	 */
	public $charsetPath = '';
	/**
	 * Begins string conversion to another charset using the chosen method
	 *
	 * @param   string    Source string
	 * @param   string    Source string char set
	 * @param   string    Destination character set
	 * @return  string    Converted string
	 */
	public function convert($string, $sourceCharSet, $destCharSet='UTF-8')
	{
		$sourceCharSet  = strtolower($sourceCharSet);
    $destCharSet    = strtolower($destCharSet);
		
		//------------------------------------------------------------------
		// Do the checks on what's being passed to us. We don't bother to 
    // run anything if we don't have a valid source string, or a valid
    // source charset, or if the  source and dest. charsets are the same.
    // Just return the source string if that's the case.
		//-------------------------------------------------------------------
    
		if ( empty($string) || is_numeric($string) || 
         empty($destCharSet) || 
         $sourceCharSet == $destCharSet 
       ) {
        return $string;
      }
				
		//----------------------------------------------------------
		// If we made it here, then let's run the conversion script
		//----------------------------------------------------------
		
    if ($this->method != 'tables') {
      $charmaps = array(
                        'shift_jis'    => 'sjis',
                        'gbk'          => 'cp936'
                       );
    }
    else {
      $charmaps = array(
                        'gbk'          => 'cp936'
                       );
    }
    if (isset($charmaps[$sourceCharSet])) {
      $sourceCharSet = $charmaps[$sourceCharSet];
    }
    if (isset($charmaps[$destCharSet])) {
      $destCharSet = $charmaps[$destCharSet];
    }
    
		$method	= "_convertCharset" . ucfirst($this->method);
		$convertedString	= $this->$method($string, $sourceCharSet, $destCharSet);

    //-----------------------------------------------------------
		// If for some ungodly reason the conversion silently failed,
    // just return the source string.
		//-----------------------------------------------------------
		return $convertedString ? $convertedString : $string;
	}
  
	/**
	 * Uses mbstrings to convert a string to a different charset
   
	 * @link http://php.net/manual/en/ref.mbstring.php
	 * @param   string    Source string
	 * @param   string    Source string char set
	 * @param   string    Destination character set
	 * @return  string    Converted string
	 */
  protected function _convertCharsetMbstrings($string, $sourceCharSet, $destCharSet) {
    $newString = '';
    if (function_exists('mb_convert_encoding')) {
			$encodings	= array_map('strtolower', mb_list_encodings());
			if(in_array(strtolower($sourceCharSet), $encodings) && in_array(strtolower($destCharSet), $encodings)) {
				$newString = mb_convert_encoding($string, $destCharSet, $sourceCharSet );
			}
		}
		return $newString ? $newString : $string;
  }
  
	/**
	 * Uses iconv to convert a string to a different charset
   
	 * @link http://php.net/manual/en/ref.iconv.php
	 * @param   string    Source string
	 * @param   string    Source string char set
	 * @param   string    Destination character set
	 * @return  string    Converted string
	 */
  protected function _convertCharsetIconv($string, $sourceCharSet, $destCharSet) {
    $newString = '';
  	if (function_exists('iconv')){
			$newString = iconv($sourceCharSet, $destCharSet."//TRANSLIT", $string);
		}
    return $newString ? $newString : $string;
  }
  
	/**
	 * Uses recode to convert a string to a different charset
   
	 * @link http://php.net/manual/en/ref.recode.php
	 * @param   string    Source string
	 * @param   string    Source string char set
	 * @param   string    Destination character set
	 * @return  string    Converted string
	 */
  protected function _convertCharsetRecode($string, $sourceCharSet, $destCharSet) {
    $newString = '';
    if (function_exists('recode_string')){
			$newString = recode_string($sourceCharSet."..".$destCharSet, $string);
		}
    return $newString ? $newString : $string;
  }

	/**
	 * Uses the conversion tables to convert a string to a different charset, without the use of external libraries
	 * The majority of this code comes from the original convertCharset class by Mikolaj Jedrzejak
   
	 * @link http://mikolajj.republika.pl/
	 * @param	string		Text string
	 * @param	string		Text string char set (original)
	 * @param	string		Desired character set (destination)
	 * @return	string		Converted string
	 */
	protected function _convertCharsetTables ($string, $sourceCharSet, $destCharSet) {
    /**
		 * Now a few variables need to be set.
		 **/
		$newString   = '';
		$stringSave	 = $string;
		
		if(!$this->charsetPath) {
			$this->charsetPath	= KX_LIB . '/i18n/';
		}
    
		/**
		 * Now build table with both charsets for encoding change or one for utf-8.
		 **/
		if ($sourceCharSet == "utf-8") {
			$charsetTable = $this->_makeConvertTable ($destCharSet);
		}
		else if ($destCharSet == "utf-8") {
			$charsetTable = $this->_makeConvertTable ($sourceCharSet);
		}
		else {
			$charsetTable = $this->_makeConvertTable ($sourceCharSet, $destCharSet);
		}
    
		/**
		 * This divison was made to prevent errors during convertion to/from utf-8 with
		 * "entities" enabled, because we need to use proper destination(to)/source(from)
		 * encoding table to write proper entities.
		 *
		 * This is the first case. We are converting from 1byte chars...
		 **/
		if ($sourceCharSet != "utf-8")
		{
				/**
				 * For each char in a string...
				 **/

				for ($i = 0; $i < strlen($string); $i++) {
					$hexChar        = "";
					$unicodeHexChar = "";
					$hexChar        = strtoupper(dechex(ord($string[$i])));

					// This is fix from Mario Klingemann, it prevents
					// droping chars below 16 because of missing leading 0 [zeros]
					if (strlen($hexChar) == 1) {
            $hexChar = "0".$hexChar;
          }
					//end of fix by Mario Klingemann
          
					// This is quick fix of 10 chars in gsm0338
					// Thanks goes to Andrea Carpani who pointed on this problem
					// and solve it ;)
					if (($sourceCharSet == "gsm0338") && ($hexChar == '1B')) {
						$i++;
						$hexChar .= strtoupper(dechex(ord($string[$i])));
					}
					// end of workarround on 10 chars from gsm0338
          
          // Big5 has multibyte characters after 0x7F
          if ((($sourceCharSet == "big5" || $sourceCharSet == "gb2312") && (ord($string[$i]) > 0x7F)) || ($sourceCharSet == "cp936" &&  ord($string[$i]) > 0x80)){
            $i++;
            $hexChar .= strtoupper(dechex(ord($string[$i])));
          }
          // SJIS has multibyte characters as well
          if (($sourceCharSet == "shift_jis") && ((ord($string[$i]) > 0x7E) && !(ord($string[$i]) >= 0xA1 && ord($string[$i]) <= 0xDF))) {
            $i++;
            $hexChar .= strtoupper(dechex(ord($string[$i])));
          }

					if ($destCharSet != "utf-8") {
						if (in_array($hexChar, $charsetTable[$sourceCharSet])) {
							$unicodeHexChar = array_search($hexChar, $charsetTable[$sourceCharSet]);
							$unicodeHexChars = explode("+",$unicodeHexChar);
              
							for($unicodeHexCharElement = 0; $unicodeHexCharElement < count($unicodeHexChars); $unicodeHexCharElement++) {
							  if (array_key_exists($unicodeHexChars[$unicodeHexCharElement], $charsetTable[$destCharSet])) {
									if ($this->entities == true) {
										$newString .= $this->_unicodeEntity($this->_hexToUtf($unicodeHexChars[$unicodeHexCharElement]));
									}
									else {
										$newString .= chr(hexdec($charsetTable[$destCharSet][$unicodeHexChars[$unicodeHexCharElement]]));
									}
								}
							 	else {
										return $stringSave;
								}
							} //for($unicodeH...
						}
						else {
							return $stringSave;
						}
					}
					else {
						if (in_array("$hexChar", $charsetTable[$sourceCharSet])) {
							$unicodeHexChar = array_search($hexChar, $charsetTable[$sourceCharSet]);
							/**
					     * Sometimes there are two or more utf-8 chars per one regular char.
							 * Extream, example is polish old Mazovia encoding, where one char contains
							 * two lettes 007a (z) and 0142 (l slash), we need to figure out how to
							 * solve this problem.
							 * The letters are merge with "plus" sign, there can be more than two chars.
							 * In Mazowia we have 007A+0142, but sometimes it can look like this
							 * 0x007A+0x0142+0x2034 (that string means nothing, it just shows the possibility...)
					     **/
							$unicodeHexChars = explode("+",$unicodeHexChar);
							for($unicodeHexCharElement = 0; $unicodeHexCharElement < count($unicodeHexChars); $unicodeHexCharElement++) {
								if ($this->entities == true)	{
									$newString .= $this->_unicodeEntity($this->_hexToUtf($unicodeHexChars[$unicodeHexCharElement]));
								}
								else {
									$newString .= $this->_hexToUtf($unicodeHexChars[$unicodeHexCharElement]);
								}
							} // for
						}
						else {
							return $stringSave;
						}
					}
				}
		}
		/**
		 * This is second case. We are encoding from multibyte char string.
		 **/
		else if($sourceCharSet == "utf-8")
		{
			$hexChar = "";
			$unicodeHexChar = "";
			foreach ($charsetTable[$destCharSet] as $unicodeHexChar => $hexChar) {
					if ($this->entities == true) {
						$entitieOrChar = $this->_unicodeEntity($this->_hexToUtf($unicodeHexChar));
					}
					else {
						$entitieOrChar = pack("H*", $hexChar);
					}
					$string = str_replace($this->_hexToUtf($unicodeHexChar), $entitieOrChar, $string);
			}
			$newString = $string;
		}
    return $newString;
	}

	/**
	 * CharsetChange::NumUnicodeEntity()
	 *
	 * Unicode encoding bytes, bits representation.
	 * Each b represents a bit that can be used to store character data.
	 * - bytes, bits, binary representation
	 * - 1,   7,  0bbbbbbb
	 * - 2,  11,  110bbbbb 10bbbbbb
	 * - 3,  16,  1110bbbb 10bbbbbb 10bbbbbb
	 * - 4,  21,  11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
	 *
	 * This function is written in a "long" way, for everyone who woluld like to analize
	 * the process of unicode encoding and understand it. All other functions like HexToUtf
	 * will be written in a "shortest" way I can write tham :) it does'n mean thay are short
	 * of course. You can chech it in HexToUtf() (link below) - very similar function.
	 *
	 * IMPORTANT: Remember that $unicodeString input CANNOT have single byte upper half
	 * extended ASCII codes, why? Because there is a posibility that this function will eat
	 * the following char thinking it's multibyte unicode char.
	 *
	 * @param string $unicodeString Input Unicode string (1 char can take more than 1 byte)
	 * @return string This is an input string also with unicode chars, bus saved as entities
	 * @access private
	 * @see HexToUtf()
	 **/
    protected function _unicodeEntity($unicodeString) {
	  $outString  = "";
	  $stringLenght = strlen ($unicodeString);
	  for ($charPosition = 0; $charPosition < $stringLenght; $charPosition++) {
	    $char = $unicodeString [$charPosition];
	    $asciiChar = ord ($char);

	   if ($asciiChar < 128) { //1 7 0bbbbbbb (127)
		   $outString .= $char;
	   }
	   else if ($asciiChar >> 5 == 6) { //2 11 110bbbbb 10bbbbbb (2047)
		   $firstByte = ($asciiChar & 31);
		   $charPosition++;
		   $char = $unicodeString [$charPosition];
		   $asciiChar = ord ($char);
		   $secondByte = ($asciiChar & 63);
		   $asciiChar = ($firstByte * 64) + $secondByte;
		   $entity = sprintf ("&#%d;", $asciiChar);
		   $outString .= $entity;
	   }
	   else if ($asciiChar >> 4  == 14) { //3 16 1110bbbb 10bbbbbb 10bbbbbb
			$firstByte = ($asciiChar & 31);
			$charPosition++;
			$char = $unicodeString [$charPosition];
			$asciiChar = ord ($char);
			$secondByte = ($asciiChar & 63);
			$charPosition++;
			$char = $unicodeString [$charPosition];
			$asciiChar = ord ($char);
			$thidrByte = ($asciiChar & 63);
			$asciiChar = ((($firstByte * 64) + $secondByte) * 64) + $thidrByte;

			$entity = sprintf ("&#%d;", $asciiChar);
			$outString .= $entity;
	    }
		else if ($asciiChar >> 3 == 30) { //4 21 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
			$firstByte = ($asciiChar & 31);
			$charPosition++;
			$char = $unicodeString [$charPosition];
			$asciiChar = ord ($char);
			$secondByte = ($asciiChar & 63);
			$charPosition++;
			$char = $unicodeString [$charPosition];
			$asciiChar = ord ($char);
			$thidrByte = ($asciiChar & 63);
			$charPosition++;
			$char = $unicodeString [$charPosition];
			$asciiChar = ord ($char);
			$fourthByte = ($asciiChar & 63);
			$asciiChar = ((((($firstByte * 64) + $secondByte) * 64) + $thidrByte) * 64) + $fourthByte;

			$entity = sprintf ("&#%d;", $asciiChar);
			$outString .= $entity;
	    }
	  }
	  return $outString;
	}

	/**
	 * ConvertCharset::HexToUtf()
	 *
	 * This simple function gets unicode  char up to 4 bytes and return it as a regular char.
	 * It is very similar to  UnicodeEntity function (link below). There is one difference
	 * in returned format. This time it's a regular char(s), in most cases it will be one or two chars.
	 *
	 * @param string $utfCharInHex Hexadecimal value of a unicode char.
	 * @return string Encoded hexadecimal value as a regular char.
	 * @access private
	 * @see UnicodeEntity()
	 **/
	protected function _hexToUtf ($utfCharInHex)
	{
		$outputChar = "";
		$utfCharInDec = hexdec($utfCharInHex);
		if($utfCharInDec<128) $outputChar .= chr($utfCharInDec);
    	else if($utfCharInDec<2048)$outputChar .= chr(($utfCharInDec>>6)+192).chr(($utfCharInDec&63)+128);
    	else if($utfCharInDec<65536)$outputChar .= chr(($utfCharInDec>>12)+224).chr((($utfCharInDec>>6)&63)+128).chr(($utfCharInDec&63)+128);
   		else if($utfCharInDec<2097152)$outputChar .= chr($utfCharInDec>>18+240).chr((($utfCharInDec>>12)&63)+128).chr(($utfCharInDec>>6)&63+128). chr($utfCharInDec&63+128);
		return $outputChar;
	}


	/**
	 * CharsetChange::MakeConvertTable()
	 *
	 * This function creates table with two SBCS (Single Byte Character Set). Every conversion
	 * is through this table.
	 *
	 * - The file with encoding tables have to be save in "Format A" of unicode.org charset table format! This is usualy writen in a header of every charset file.
	 * - BOTH charsets MUST be SBCS
	 * - The files with encoding tables have to be complet (Non of chars can be missing, unles you are sure you are not going to use it)
	 *
	 * "Format A" encoding file, if you have to build it by yourself should aplly these rules:
	 * - you can comment everything with #
	 * - first column contains 1 byte chars in hex starting from 0x..
	 * - second column contains unicode equivalent in hex starting from 0x....
	 * - then every next column is optional, but in "Format A" it should contain unicode char name or/and your own comment
	 * - the columns can be splited by "spaces", "tabs", "," or any combination of these
	 * - below is an example
	 *
	 * <code>
	 * #
	 * #	The entries are in ANSI X3.4 order.
	 * #
	 * 0x00	0x0000	#	NULL end extra comment, if needed
	 * 0x01	0x0001	#	START OF HEADING
	 * # Oh, one more thing, you can make comments inside of a rows if you like.
	 * 0x02	0x0002	#	START OF TEXT
	 * 0x03	0x0003	#	END OF TEXT
	 * next line, and so on...
	 * </code>
	 *
	 * You can get full tables with encodings from http://www.unicode.org
	 *
	 * @param string $fromCharset Name of first encoding and first encoding filename (thay have to be the same)
	 * @param string $toCharset Name of second encoding and second encoding filename (thay have to be the same). Optional for building a joined table.
	 * @access private
	 * @return array Table necessary to change one encoding to another.
	 **/
	protected function _makeConvertTable ($fromCharset, $toCharset='') {
		$convertTable = array();
		for($i = 0; $i < func_num_args(); $i++)
		{
			/**
			 * Because func_*** can't be used inside of another function call
			 * we have to save it as a separate value.
			 **/
			$fileName = func_get_arg($i);
			if (!is_file($this->charsetPath . $fileName))
			{
					return;
			}

			$fileWithEncTabe = fopen($this->charsetPath . $fileName, "r") or die(); //This die(); is just to make sure...
		  while(!feof($fileWithEncTabe))
			{
				/**
				 * We asume that line is not longer
				 * than 1024 which is the default value for fgets function
				 **/
		   if($oneLine=trim(fgets($fileWithEncTabe, 1024)))
			 {
				/**
				 * We don't need all comment lines. I check only for "#" sign, because
				 * this is a way of making comments by unicode.org in thair encoding files
				 * and that's where the files are from :-)
				 **/
		   	if (substr($oneLine, 0, 1) != "#")
				{
					/**
					 * Sometimes inside the charset file the hex walues are separated by
					 * "space" and sometimes by "tab", the below preg_split can also be used
					 * to split files where separator is a ",", "\r", "\n" and "\f"
					 **/
					$hexValue = preg_split ("/[\s,]+/", $oneLine, 3);  //We need only first 2 values
						/**
						 * Sometimes char is UNDEFINED, or missing so we can't use it for convertion
						 **/
						if (substr($hexValue[1], 0, 1) != "#")
						{
								$arrayKey = strtoupper(str_replace(strtolower("0x"), "", $hexValue[1]));
								$arrayValue = strtoupper(str_replace(strtolower("0x"), "", $hexValue[0]));
								$convertTable[func_get_arg($i)][$arrayKey] = $arrayValue;
						}
				} //if (substr($oneLine,...
		   } //if($oneLine=trim(f...
		  } //while(!feof($firstFileWi...
		} //for($i = 0; $i < func_...
	/**
	 * The last thing is to check if by any reason both encoding tables are not the same.
	 * For example, it will happen when you save the encoding table file with a wrong name
	 *  - of another charset.
	 
	if(!is_array($convertTable[$fromCharset])) $convertTable[$fromCharset]=array();

	if ((func_num_args() > 1) && (count($convertTable[$fromCharset]) == count($convertTable[$toCharset])) && (count(array_diff_assoc($convertTable[$fromCharset], $convertTable[$toCharset])) == 0))
	{
	    print $this->DebugOutput(1, 1, "$fromCharset, $toCharset");
	}**/
    return $convertTable;
	}
} //class ends here
?>
