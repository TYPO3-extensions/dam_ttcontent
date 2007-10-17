<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2006 Rene Fritz (r.fritz@colorcube.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/




/**
 * Class for updating the db
 *
 * @author	 Rene Fritz <r.fritz@colorcube.de>
 * @package TYPO3-DAM
 * @subpackage tx_dam
 */
class ext_update  {

	/**
	 * Main function, returning the HTML content of the module
	 *
	 * @return	string		HTML
	 */
	function main()	{

		$content = '';

		
		return $content;
	}

	/**
	 * Checks how many rows are found and returns true if there are any
	 *
	 * @return	boolean
	 */
	function access()	{
		
		$this->checkMMforeign();

		return true;
	}

	

	/**
	 * Checks if mmforeign or T3 V 4.1 is installed and print a waring in EM when needed
	 *
	 * @return	void
	 */
	function checkMMforeign()	{
		
		$mmforeign = t3lib_extMgm::isLoaded('mmforeign');
		$isFourOne = t3lib_div::int_from_ver(TYPO3_branch)>=t3lib_div::int_from_ver('4.1');

		if (!$mmforeign AND !$isFourOne) {
			$GLOBALS['SOBE']->content.=$GLOBALS['SOBE']->doc->section('WARNING: Extension \'mmforeign\' needs to be installed!','',0,1,3);
		} elseif ($mmforeign AND $isFourOne) {
			$GLOBALS['SOBE']->content.=$GLOBALS['SOBE']->doc->section('NOTE: Extension \'mmforeign\' may not be needed with TYPO3 V4.1!','',0,1,1);
		}
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_ttcontent/class.ext_update.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_ttcontent/class.ext_update.php']);
}


?>