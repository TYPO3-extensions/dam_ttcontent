<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

//$tempColumns = array (
//	'tx_damttcontent_files' => $GLOBALS['T3_VAR']['ext']['dam']['TCA']['image_field']
//);
$tempColumns = array (
	'tx_damttcontent_files' => txdam_getMediaTCA('image_field', 'tx_damttcontent_files')
);


t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addTCAcolumns('tt_content',$tempColumns,1);


$tempSetup = $GLOBALS['T3_VAR']['ext']['dam_ttcontent']['setup'];

## CType image

if ($tempSetup['ctype_image_add_ref']) {

	if ($tempSetup['ctype_image_add_orig_field']) {
		t3lib_extMgm::addToAllTCAtypes('tt_content','tx_damttcontent_files','image','after:image');
	} else {
		$TCA['tt_content']['types']['image']['showitem'] = str_replace(', image;', ', tx_damttcontent_files;', $TCA['tt_content']['types']['image']['showitem']);
	}
}

## CType textpic

if ($tempSetup['ctype_textpic_add_ref']) {

	if ($tempSetup['ctype_textpic_add_orig_field']) {
		t3lib_extMgm::addToAllTCAtypes('tt_content','tx_damttcontent_files','textpic','after:image');
	} else {
		$TCA['tt_content']['types']['textpic']['showitem'] = str_replace(', image;', ', tx_damttcontent_files;', $TCA['tt_content']['types']['textpic']['showitem']);
	}
}

?>