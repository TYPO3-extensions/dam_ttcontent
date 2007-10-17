<?php

$GLOBALS['T3_VAR']['ext']['dam_ttcontent']['setup'] = unserialize($_EXTCONF);


if ($GLOBALS['T3_VAR']['ext']['dam_ttcontent']['setup']['ctype_image_add_ref']) {

	t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
		includeLibs.tx_damttcontent = EXT:dam/lib/class.tx_dam_tsfe.php

		temp.tx_dam.fileList < tt_content.image.20.imgList

		tt_content.image.20.imgList >
		tt_content.image.20.imgList.cObject = USER
		tt_content.image.20.imgList.cObject {
			userFunc = tx_dam_tsfe->fetchFileList

			refField = tx_damttcontent_files
			refTable = tt_content

			additional.fileList < temp.tx_dam.fileList
			additional.filePath < tt_content.image.20.imgPath
			'.($GLOBALS['T3_VAR']['ext']['dam_ttcontent']['setup']['ctype_image_add_orig_field']?'':'additional >').'
		}
		tt_content.image.20.imgPath >
		tt_content.image.20.imgPath =

	',43);
}

if ($GLOBALS['T3_VAR']['ext']['dam_ttcontent']['setup']['ctype_textpic_add_ref']) {

	t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
		includeLibs.tx_damttcontent = EXT:dam/lib/class.tx_dam_tsfe.php

		temp.tx_dam.fileList < tt_content.textpic.20.imgList

		tt_content.textpic.20.imgList >
		tt_content.textpic.20.imgList.cObject = USER
		tt_content.textpic.20.imgList.cObject {
			userFunc = tx_dam_tsfe->fetchFileList

			refField = tx_damttcontent_files
			refTable = tt_content

			additional.fileList < temp.tx_dam.fileList
			additional.filePath < tt_content.textpic.20.imgPath
			'.($GLOBALS['T3_VAR']['ext']['dam_ttcontent']['setup']['ctype_textpic_add_orig_field']?'':'additional >').'
		}
		tt_content.textpic.20.imgPath >
		tt_content.textpic.20.imgPath =

	',43);
}


if ($GLOBALS['T3_VAR']['ext']['dam_ttcontent']['setup']['add_css_styled_hook']) {
	$TYPO3_CONF_VARS['EXTCONF']['css_styled_content']['pi1_hooks']['render_textpic'] = 'EXT:dam_ttcontent/pi_cssstyledcontent/class.tx_damttcontent_pi1.php:&tx_damttcontent_pi1';
}

?>