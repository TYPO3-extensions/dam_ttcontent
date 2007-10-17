<?php

########################################################################
# Extension Manager/Repository config file for ext: "dam_ttcontent"
#
# Auto generated 22-11-2006 18:39
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'DAM for Content Elements',
	'description' => 'Enhance some of the default content elements to make use of DAM functionality. Eg. modify the content types "Image" and "Text/Image" for usage of the DAM.',
	'category' => 'fe',
	'shy' => 0,
	'version' => '1.0.100',
	'dependencies' => 'cms,dam,mmforeign',
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
	'_md5_values_when_last_written' => 'a:9:{s:21:"ext_conf_template.txt";s:4:"fd44";s:12:"ext_icon.gif";s:4:"999b";s:17:"ext_localconf.php";s:4:"b3ab";s:14:"ext_tables.php";s:4:"ed83";s:14:"ext_tables.sql";s:4:"aa4c";s:14:"doc/manual.sxw";s:4:"5512";s:49:"pi_cssstyledcontent/class.tx_damttcontent_pi1.php";s:4:"0344";s:40:"pi_cssstyledcontent/static/constants.txt";s:4:"5c90";s:36:"pi_cssstyledcontent/static/setup.txt";s:4:"2823";}',
	'suggests' => array(
	),
);

?>