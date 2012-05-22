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
	'version' => '1.2.2-dev',
	'dependencies' => 'cms,dam',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_content',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'The DAM development team',
	'author_email' => 'typo3-project-dam@lists.netfielders.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'dam' => '1.3.0-',
			'php' => '4.0.0-',
			'typo3' => '4.5.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => '',
	'suggests' => array(
	),
);

?>
