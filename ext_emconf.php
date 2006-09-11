<?php

########################################################################
# Extension Manager/Repository config file for ext: "dam_ttcontent"
#
# Auto generated 31-08-2006 20:40
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Content/DAM reference usage',
	'description' => 'Modify the content types "Image" and "Text/Image" for usage of the DAM.',
	'category' => 'fe',
	'shy' => 0,
	'version' => '1.0.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'experimental',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_content',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Rene Fritz',
	'author_email' => 'r.fritz@colorcube.de',
	'author_company' => 'Colorcube - digital media lab, www.colorcube.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'dam' => '',
			'mmforeign' => '',
			'php' => '4.0.0-',
			'typo3' => '3.8.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:6:{s:21:"ext_conf_template.txt";s:4:"f401";s:12:"ext_icon.gif";s:4:"999b";s:17:"ext_localconf.php";s:4:"ce88";s:14:"ext_tables.php";s:4:"0feb";s:14:"ext_tables.sql";s:4:"aa4c";s:14:"doc/manual.sxw";s:4:"6056";}',
);

?>