<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2005 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'Content rendering' for the 'css_styled_content' extension.
 *
 * $Id: class.tx_cssstyledcontent_pi1.php 1618 2006-07-10 17:24:44Z baschny $
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 *
 * TOTAL FUNCTIONS: 6
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(t3lib_extMgm::extPath('css_styled_content').'pi1/class.tx_cssstyledcontent_pi1.php');



/**
 * Plugin class - instantiated from TypoScript.
 * Rendering some content elements from tt_content table.
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_cssstyledcontent
 */
class tx_damttcontent_pi1 extends tx_cssstyledcontent_pi1 {


	function addMetaToData ($meta) {
		foreach ($meta as $key => $value) {
			$this->pObj->cObj->data['txdam_' . $key] = $value;
		}
	}

	function removeMetaFromData () {
		foreach ($this->pObj->cObj->data as $key => $value) {
			if (substr($key, 0, 6) == 'txdam_') {
				unset($this->pObj->cObj->data[$key]);
			}
		}
	}

	/**
	 * returns an array containing width relations for $colCount columns.
	 *
	 * tries to use "colRelations" setting given by TS.
	 * uses "1:1" column relations by default.
	 *
	 * @param array $conf TS configuration for img
	 * @param int $colCount number of columns
	 * @return array
	 */
	protected function getImgColumnRelations($conf, $colCount) {
		$relations = array();
		$equalRelations= array_fill(0, $colCount, 1);
		$colRelationsTypoScript = trim($this->pObj->cObj->stdWrap($conf['colRelations'], $conf['colRelations.']));

		if ($colRelationsTypoScript) {
				// try to use column width relations given by TS
			$relationParts = explode(':', $colRelationsTypoScript);
				// enough columns defined?
			if (count($relationParts) >= $colCount) {
				$out = array();
				for ($a = 0; $a < $colCount; $a++) {
					$currentRelationValue = intval($relationParts[$a]);
					if ($currentRelationValue >= 1) {
						$out[$a] = $currentRelationValue;
					} else {
						t3lib_div::devLog('colRelations used with a value smaller than 1 therefore colRelations setting is ignored.', $this->extKey, 2);
						unset($out);
						break;
					}
				}
				if (max($out) / min($out) <= 10) {
					$relations = $out;
				} else {
					t3lib_div::devLog('The difference in size between the largest and smallest colRelation was not within a factor of ten therefore colRelations setting is ignored..', $this->extKey, 2);
				}
			}
		}
		return $relations ? $relations : $equalRelations;
	}

	/**
	 * Rendering the IMGTEXT content element, called from TypoScript (tt_content.textpic.20)
	 *
	 * @param	string		Content input. Not used, ignore.
	 * @param	array		TypoScript configuration. See TSRef "IMGTEXT". This function aims to be compatible.
	 * @return	string		HTML output.
	 * @access private
	 * @coauthor	Ernesto Baschny <ernst@cron-it.de>
	 * @coauthor Patrick Broens <patrick@patrickbroens.nl>
	 */
	 function render_textpic($content, $conf)	{
			// Look for hook before running default code for function
		if (method_exists($this, 'hookRequest') && $hookObj = $this->hookRequest('render_textpic')) {
			return $hookObj->render_textpic($content,$conf);
		}

		$renderMethod = $this->pObj->cObj->stdWrap($conf['renderMethod'], $conf['renderMethod.']);

			// Render using the default IMGTEXT code (table-based)
		if (!$renderMethod || $renderMethod == 'table')	{
			return $this->pObj->cObj->IMGTEXT($conf);
		}

			// Specific configuration for the chosen rendering method
		if (is_array($conf['rendering.'][$renderMethod . '.']))	{
			$conf = $this->pObj->cObj->joinTSarrays($conf, $conf['rendering.'][$renderMethod . '.']);
		}

			// Image or Text with Image?
		if (is_array($conf['text.']))	{
			$content = $this->pObj->cObj->stdWrap($this->pObj->cObj->cObjGet($conf['text.'], 'text.'), $conf['text.']);
		}

		$imgList = trim($this->pObj->cObj->stdWrap($conf['imgList'], $conf['imgList.']));

		if (!$imgList)	{
				// No images, that's easy
			if (is_array($conf['stdWrap.']))	{
				return $this->pObj->cObj->stdWrap($content, $conf['stdWrap.']);
			}
			return $content;
		}

		$imgs = t3lib_div::trimExplode(',', $imgList);
		$imgStart = intval($this->pObj->cObj->stdWrap($conf['imgStart'], $conf['imgStart.']));
		$imgCount = count($imgs) - $imgStart;
		$imgMax = intval($this->pObj->cObj->stdWrap($conf['imgMax'], $conf['imgMax.']));
		if ($imgMax)	{
			$imgCount = tx_dam::forceIntegerInRange($imgCount, 0, $imgMax);	// reduce the number of images.
		}

		$imgPath = $this->pObj->cObj->stdWrap($conf['imgPath'], $conf['imgPath.']);

			// Does we need to render a "global caption" (below the whole image block)?
		$renderGlobalCaption = !$conf['captionEach'] && !$conf['captionSplit'] && !$conf['imageTextSplit'] && is_array($conf['caption.']);
		if ($imgCount == 1) {
				// If we just have one image, the caption relates to the image, so it is not "global"
			$renderGlobalCaption = FALSE;
		}

			// Use the calculated information (amount of images, if global caption is wanted) to choose a different rendering method for the images-block
		$GLOBALS['TSFE']->register['imageCount'] = $imgCount;
		$GLOBALS['TSFE']->register['renderGlobalCaption'] = $renderGlobalCaption;
		$fallbackRenderMethod = $this->pObj->cObj->cObjGetSingle($conf['fallbackRendering'], $conf['fallbackRendering.']);

			// Set the accessibility mode which uses a different type of markup, used 4.7+
		$accessibilityMode = FALSE;
		if (strpos(strtolower($renderMethod), 'caption') || strpos(strtolower($fallbackRenderMethod), 'caption')) {
			$accessibilityMode = TRUE;
		}

			// Global caption
		$globalCaption = '';
		if ($renderGlobalCaption)	{
			$globalCaption = $this->pObj->cObj->stdWrap($this->pObj->cObj->cObjGet($conf['caption.'], 'caption.'), $conf['caption.']);
		}

			// Positioning
		$position = $this->pObj->cObj->stdWrap($conf['textPos'], $conf['textPos.']);

		$imagePosition = $position&7;	// 0,1,2 = center,right,left
		$contentPosition = $position&24;	// 0,8,16,24 (above,below,intext,intext-wrap)
		$align = $this->pObj->cObj->align[$imagePosition];
		$textMargin = intval($this->pObj->cObj->stdWrap($conf['textMargin'],$conf['textMargin.']));
		if (!$conf['textMargin_outOfText'] && $contentPosition < 16)	{
			$textMargin = 0;
		}

		$colspacing = intval($this->pObj->cObj->stdWrap($conf['colSpace'], $conf['colSpace.']));
		$rowspacing = intval($this->pObj->cObj->stdWrap($conf['rowSpace'], $conf['rowSpace.']));

		$border = intval($this->pObj->cObj->stdWrap($conf['border'], $conf['border.'])) ? 1:0;
		$borderColor = $this->pObj->cObj->stdWrap($conf['borderCol'], $conf['borderCol.']);
		$borderThickness = intval($this->pObj->cObj->stdWrap($conf['borderThick'], $conf['borderThick.']));

		$borderColor = $borderColor?$borderColor:'black';
		$borderThickness = $borderThickness?$borderThickness:1;
		$borderSpace = (($conf['borderSpace']&&$border) ? intval($conf['borderSpace']) : 0);

			// Generate cols
		$cols = intval($this->pObj->cObj->stdWrap($conf['cols'],$conf['cols.']));
		$colCount = ($cols > 1) ? $cols : 1;
		if ($colCount > $imgCount)	{$colCount = $imgCount;}
		$rowCount = ceil($imgCount / $colCount);

			// Generate rows
		$rows = intval($this->pObj->cObj->stdWrap($conf['rows'],$conf['rows.']));
		if ($rows>1)	{
			$rowCount = $rows;
			if ($rowCount > $imgCount)	{$rowCount = $imgCount;}
			$colCount = ($rowCount>1) ? ceil($imgCount / $rowCount) : $imgCount;
		}

			// Max Width
		$maxW = intval($this->pObj->cObj->stdWrap($conf['maxW'], $conf['maxW.']));
		$maxWInText = intval($this->pObj->cObj->stdWrap($conf['maxWInText'], $conf['maxWInText.']));
		$fiftyPercentWidthInText = round($maxW / 100 * 50);

		if ($contentPosition>=16)	{	// in Text
			if (!$maxWInText)	{
					// If maxWInText is not set, it's calculated to the 50% of the max
				$maxW = $fiftyPercentWidthInText;
			} else {
				$maxW = $maxWInText;
			}
		}

			// Set the margin for image + text, no wrap always to avoid multiple stylesheets
		if ($accessibilityMode) {
			$noWrapMargin = (integer) (($maxWInText ? $maxWInText : $fiftyPercentWidthInText) +
				intval($this->pObj->cObj->stdWrap($conf['textMargin'],$conf['textMargin.'])));

			$this->addPageStyle(
				'.csc-textpic-intext-right-nowrap .csc-textpic-text',
				'margin-right: ' . $noWrapMargin . 'px;'
			);

			$this->addPageStyle(
				'.csc-textpic-intext-left-nowrap .csc-textpic-text',
				'margin-left: ' . $noWrapMargin . 'px;'
			);
		}

			// max usuable width for images (without spacers and borders)
		$netW = $maxW - $colspacing * ($colCount - 1) - $colCount * $border * ($borderThickness + $borderSpace) * 2;

			// Specify the maximum width for each column
		$columnWidths = $this->getImgColumnWidths($conf, $colCount, $netW);

		$image_compression = intval($this->pObj->cObj->stdWrap($conf['image_compression'],$conf['image_compression.']));
		$image_effects = intval($this->pObj->cObj->stdWrap($conf['image_effects'],$conf['image_effects.']));
		$image_frames = intval($this->pObj->cObj->stdWrap($conf['image_frames.']['key'],$conf['image_frames.']['key.']));

			// EqualHeight
		$equalHeight = intval($this->pObj->cObj->stdWrap($conf['equalH'],$conf['equalH.']));
		if ($equalHeight)	{
				// Initiate gifbuilder object in order to get dimensions AND calculate the imageWidth's
			$gifCreator = t3lib_div::makeInstance('tslib_gifbuilder');
			$gifCreator->init();
			$relations_cols = Array();
			$imgWidths = array(); // contains the individual width of all images after scaling to $equalHeight
			for ($a=0; $a<$imgCount; $a++)	{
				$imgKey = $a+$imgStart;
				$imgInfo = $gifCreator->getImageDimensions($imgPath.$imgs[$imgKey]);
				$rel = $imgInfo[1] / $equalHeight;	// relationship between the original height and the wished height
				if ($rel)	{	// if relations is zero, then the addition of this value is omitted as the image is not expected to display because of some error.
					$imgWidths[$a] = $imgInfo[0] / $rel;
					$relations_cols[floor($a/$colCount)] += $imgWidths[$a];	// counts the total width of the row with the new height taken into consideration.
				}
			}
		}

			// Fetches pictures
		$splitArr = array();
		$splitArr['imgObjNum'] = $conf['imgObjNum'];
		$splitArr = $GLOBALS['TSFE']->tmpl->splitConfArray($splitArr, $imgCount);

		$imageRowsFinalWidths = Array();	// contains the width of every image row
		$imgsTag = array();		// array index of $imgsTag will be the same as in $imgs, but $imgsTag only contains the images that are actually shown
		$origImages = array();
		$rowIdx = 0;
		for ($a=0; $a<$imgCount; $a++)	{
			$imgKey = $a+$imgStart;
			$totalImagePath = $imgPath.$imgs[$imgKey];

			$GLOBALS['TSFE']->register['IMAGE_NUM'] = $imgKey;	// register IMG_NUM is kept for backwards compatibility
			$GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] = $imgKey;
			$GLOBALS['TSFE']->register['ORIG_FILENAME'] = $totalImagePath;

			$this->pObj->cObj->data[$this->pObj->cObj->currentValKey] = $totalImagePath;

				// fetch DAM data and provide it as field data prefixed with txdam_
			$media = tx_dam::media_getForFile($totalImagePath, '*');
			if ($media->isAvailable) {
				$this->addMetaToData ($media->getMetaArray());
				$imgsExtraData[$imgKey] = $media->getMetaArray();
			} else {
				$this->removeMetaFromData();
				$imgsExtraData[$imgKey] = array();
			}
			unset($media);

			$imgObjNum = intval($splitArr[$a]['imgObjNum']);
			$imgConf = $conf[$imgObjNum.'.'];

			if ($equalHeight)	{

				if ($a % $colCount == 0) {
						// a new row startsS
					$accumWidth = 0; // reset accumulated net width
					$accumDesiredWidth = 0; // reset accumulated desired width
					$rowTotalMaxW = $relations_cols[$rowIdx];
					if ($rowTotalMaxW > $netW)	{
						$scale = $rowTotalMaxW / $netW;
					} else {
						$scale = 1;
					}
					$desiredHeight = $equalHeight / $scale;
					$rowIdx++;
				}

				$availableWidth= $netW - $accumWidth; // this much width is available for the remaining images in this row (int)
				$desiredWidth= $imgWidths[$a] / $scale; // theoretical width of resized image. (float)
				$accumDesiredWidth += $desiredWidth; // add this width. $accumDesiredWidth becomes the desired horizontal position
					// calculate width by comparing actual and desired horizontal position.
					// this evenly distributes rounding errors across all images in this row.
				$suggestedWidth = round($accumDesiredWidth - $accumWidth);
				$finalImgWidth = (int) min($availableWidth, $suggestedWidth); // finalImgWidth may not exceed $availableWidth
				$accumWidth += $finalImgWidth;
				$imgConf['file.']['width'] = $finalImgWidth;
				$imgConf['file.']['height'] = round($desiredHeight);

					// other stuff will be calculated accordingly:
				unset($imgConf['file.']['maxW']);
				unset($imgConf['file.']['maxH']);
				unset($imgConf['file.']['minW']);
				unset($imgConf['file.']['minH']);
				unset($imgConf['file.']['width.']);
				unset($imgConf['file.']['maxW.']);
				unset($imgConf['file.']['maxH.']);
				unset($imgConf['file.']['minW.']);
				unset($imgConf['file.']['minH.']);
			} else {
				$imgConf['file.']['maxW'] = $columnWidths[($a%$colCount)];
			}

			$titleInLink = $this->pObj->cObj->stdWrap($imgConf['titleInLink'], $imgConf['titleInLink.']);
			$titleInLinkAndImg = $this->pObj->cObj->stdWrap($imgConf['titleInLinkAndImg'], $imgConf['titleInLinkAndImg.']);
			$oldATagParms = $GLOBALS['TSFE']->ATagParams;
			if ($titleInLink)	{
					// Title in A-tag instead of IMG-tag
				$titleText = trim($this->pObj->cObj->stdWrap($imgConf['titleText'], $imgConf['titleText.']));
				if ($titleText)	{
						// This will be used by the IMAGE call later:
					$GLOBALS['TSFE']->ATagParams .= ' title="'. $titleText .'"';
				}
			}

			if ($imgConf || $imgConf['file'])	{
				if ($this->pObj->cObj->image_effects[$image_effects])	{
					$imgConf['file.']['params'] .= ' '.$this->pObj->cObj->image_effects[$image_effects];
				}
				if ($image_frames)	{
					if (is_array($conf['image_frames.'][$image_frames.'.']))	{
						$imgConf['file.']['m.'] = $conf['image_frames.'][$image_frames.'.'];
					}
				}
				if ($image_compression && $imgConf['file'] != 'GIFBUILDER')	{
					if ($image_compression == 1)	{
						$tempImport = $imgConf['file.']['import'];
						$tempImport_dot = $imgConf['file.']['import.'];
						unset($imgConf['file.']);
						$imgConf['file.']['import'] = $tempImport;
						$imgConf['file.']['import.'] = $tempImport_dot;
					} elseif (isset($this->pObj->cObj->image_compression[$image_compression])) {
						$imgConf['file.']['params'] .= ' '.$this->pObj->cObj->image_compression[$image_compression]['params'];
						$imgConf['file.']['ext'] = $this->pObj->cObj->image_compression[$image_compression]['ext'];
						unset($imgConf['file.']['ext.']);
					}
				}
				if ($titleInLink && ! $titleInLinkAndImg)	{
						// Check if the image will be linked
					$link = $this->pObj->cObj->imageLinkWrap('', $totalImagePath, $imgConf['imageLinkWrap.']);
					if ($link)	{
							// Title in A-tag only (set above: ATagParams), not in IMG-tag
						unset($imgConf['titleText']);
						unset($imgConf['titleText.']);
						$imgConf['emptyTitleHandling'] = 'removeAttr';
					}
				}
				$imgsTag[$imgKey] = $this->pObj->cObj->IMAGE($imgConf);
			} else {
				$imgsTag[$imgKey] = $this->pObj->cObj->IMAGE(Array('file' => $totalImagePath)); 	// currentValKey !!!
			}
				// Restore our ATagParams
			$GLOBALS['TSFE']->ATagParams = $oldATagParms;
				// Store the original filepath
			$origImages[$imgKey] = $GLOBALS['TSFE']->lastImageInfo;

			if ($GLOBALS['TSFE']->lastImageInfo[0]==0) {
				$imageRowsFinalWidths[floor($a/$colCount)] += $this->pObj->cObj->data['imagewidth'];
			} else {
				$imageRowsFinalWidths[floor($a/$colCount)] += $GLOBALS['TSFE']->lastImageInfo[0];
 			}

		}
			// How much space will the image-block occupy?
		$imageBlockWidth = max($imageRowsFinalWidths)+ $colspacing*($colCount-1) + $colCount*$border*($borderSpace+$borderThickness)*2;
		$GLOBALS['TSFE']->register['rowwidth'] = $imageBlockWidth;
		$GLOBALS['TSFE']->register['rowWidthPlusTextMargin'] = $imageBlockWidth + $textMargin;

			// noRows is in fact just one ROW, with the amount of columns specified, where the images are placed in.
			// noCols is just one COLUMN, each images placed side by side on each row
		$noRows = $this->pObj->cObj->stdWrap($conf['noRows'],$conf['noRows.']);
		$noCols = $this->pObj->cObj->stdWrap($conf['noCols'],$conf['noCols.']);
		if ($noRows) {$noCols=0;}	// noRows overrides noCols. They cannot exist at the same time.

		$rowCount_temp = 1;
		$colCount_temp = $colCount;
		if ($noRows)	{
			$rowCount_temp = $rowCount;
			$rowCount = 1;
		}
		if ($noCols)	{
			$colCount = 1;
			$columnWidths = array();
		}

			// Edit icons:
		if (!is_array($conf['editIcons.'])) {
			$conf['editIcons.'] = array();
		}
		$editIconsHTML = $conf['editIcons']&&$GLOBALS['TSFE']->beUserLogin ? $this->pObj->cObj->editIcons('',$conf['editIcons'],$conf['editIcons.']) : '';

			// If noRows, we need multiple imagecolumn wraps
		$imageWrapCols = 1;
		if ($noRows)	{ $imageWrapCols = $colCount; }

			// User wants to separate the rows, but only do that if we do have rows
		$separateRows = $this->pObj->cObj->stdWrap($conf['separateRows'], $conf['separateRows.']);
		if ($noRows)	{ $separateRows = 0; }
		if ($rowCount == 1)	{ $separateRows = 0; }

		if ($accessibilityMode) {
			$imagesInColumns = round(($imgCount / ($rowCount * $colCount)), 0 , PHP_ROUND_HALF_UP);

				// Apply optionSplit to the list of classes that we want to add to each column
			$addClassesCol = $conf['addClassesCol'];
			if (isset($conf['addClassesCol.'])) {
				$addClassesCol = $this->pObj->cObj->stdWrap($addClassesCol, $conf['addClassesCol.']);
			}
			$addClassesColConf = $GLOBALS['TSFE']->tmpl->splitConfArray(array('addClassesCol' => $addClassesCol), $colCount);

				// Apply optionSplit to the list of classes that we want to add to each image
			$addClassesImage = $conf['addClassesImage'];
			if (isset($conf['addClassesImage.'])) {
				$addClassesImage = $this->pObj->cObj->stdWrap($addClassesImage, $conf['addClassesImage.']);
			}
			$addClassesImageConf = $GLOBALS['TSFE']->tmpl->splitConfArray(array('addClassesImage' => $addClassesImage), $imagesInColumns);

			$rows = array();
			$currentImage = 0;

				// Set the class for the caption (split or global)
			$classCaptionAlign = array(
				'center' => 'csc-textpic-caption-c',
				'right' => 'csc-textpic-caption-r',
				'left' => 'csc-textpic-caption-l',
			);

			$captionAlign = $this->pObj->cObj->stdWrap($conf['captionAlign'], $conf['captionAlign.']);

				// Iterate over the rows
			for ($rowCounter = 1; $rowCounter <= $rowCount; $rowCounter++) {
				$rowColumns = array();
					// Iterate over the columns
				for ($columnCounter = 1; $columnCounter <= $colCount; $columnCounter++) {
					$columnImages = array();
						// Iterate over the amount of images allowed in a column
					for ($imagesCounter = 1; $imagesCounter <= $imagesInColumns; $imagesCounter++) {
						$image = NULL;
						$splitCaption = NULL;

							// add DAM metadata to current object
						$this->addMetaToData($imgsExtraData[$currentImage]);

						$imageMarkers = $captionMarkers = array();
						$single = '&nbsp;';

							// Set the key of the current image
						$imageKey = $currentImage + $imgStart;

							// Register IMAGE_NUM_CURRENT for the caption
						$GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] = $imageKey;
						$this->pObj->cObj->data[$this->pObj->cObj->currentValKey] = $origImages[$imageKey]['origFile'];

							// Get the image if not an empty cell
						if (isset($imgsTag[$imageKey])) {
							$image = $this->pObj->cObj->stdWrap($imgsTag[$imageKey], $conf['imgTagStdWrap.']);

								// Add the edit icons
							if ($editIconsHTML) {
								$image .= $this->pObj->cObj->stdWrap($editIconsHTML, $conf['editIconsStdWrap.']);
							}

								// Wrap the single image
							$single = $this->pObj->cObj->stdWrap($image, $conf['singleStdWrap.']);

								// Get the caption
							if (!$renderGlobalCaption) {
								$imageMarkers['caption'] = $this->pObj->cObj->stdWrap(
									$this->pObj->cObj->cObjGet($conf['caption.'], 'caption.'), $conf['caption.']
								);

								if ($captionAlign) {
									$captionMarkers['classes'] = ' ' . $classCaptionAlign[$captionAlign];
								}

								$imageMarkers['caption'] = $this->pObj->cObj->substituteMarkerArray(
									$imageMarkers['caption'],
									$captionMarkers,
									'###|###',
									1,
									1
								);
							}

							if ($addClassesImageConf[$imagesCounter - 1]['addClassesImage']) {
								$imageMarkers['classes'] = ' ' . $addClassesImageConf[$imagesCounter - 1]['addClassesImage'];
							}
						}

						$columnImages[] = $this->pObj->cObj->substituteMarkerArray(
							$single,
							$imageMarkers,
							'###|###',
							1,
							1
						);

						$currentImage++;
					}

					$rowColumn = $this->pObj->cObj->stdWrap(
						implode(LF, $columnImages),
						$conf['columnStdWrap.']
					);

						// Start filling the markers for columnStdWrap
					$columnMarkers = array();

					if ($addClassesColConf[$columnCounter - 1]['addClassesCol']) {
						$columnMarkers['classes'] = ' ' . $addClassesColConf[$columnCounter - 1]['addClassesCol'];
					}

					$rowColumns[] = $this->pObj->cObj->substituteMarkerArray(
						$rowColumn,
						$columnMarkers,
						'###|###',
						1,
						1
					);
				}
				if ($noRows) {
					$rowConfiguration = $conf['noRowsStdWrap.'];
				} elseif ($rowCounter == $rowCount) {
					$rowConfiguration = $conf['lastRowStdWrap.'];
				} else {
					$rowConfiguration = $conf['rowStdWrap.'];
				}

				$row = $this->pObj->cObj->stdWrap(
					implode(LF, $rowColumns),
					$rowConfiguration
				);

					// Start filling the markers for columnStdWrap
				$rowMarkers = array();

				$rows[] = $this->pObj->cObj->substituteMarkerArray(
					$row,
					$rowMarkers,
					'###|###',
					1,
					1
				);
			}

			$images = $this->pObj->cObj->stdWrap(
				implode(LF, $rows),
				$conf['allStdWrap.']
			);
				// Start filling the markers for allStdWrap
			$allMarkers = array();
			$classes = array();

				// Add the global caption to the allStdWrap marker array if set
			if ($globalCaption) {
				$allMarkers['caption'] = $globalCaption;
				if ($captionAlign) {
					$classes[] = $classCaptionAlign[$captionAlign];
				}
			}

				// Add the border class if needed
			if ($border){
				$classes[] = $conf['borderClass'] ? $conf['borderClass'] : 'csc-textpic-border';
			}

				// Add the class for equal height if needed
			if ($equalHeight) {
				$classes[] = 'csc-textpic-equalheight';
			}

			$addClasses = $this->pObj->cObj->stdWrap($conf['addClasses'], $conf['addClasses.']);
			if ($addClasses) {
				$classes[] = $addClasses;
			}

			if ($classes) {
				$class = ' ' . implode(' ', $classes);
			}

				// Fill the markers for the allStdWrap
			$images = $this->pObj->cObj->substituteMarkerArray(
				$images,
				$allMarkers,
				'###|###',
				1,
				1
			);
		} else {
				// Apply optionSplit to the list of classes that we want to add to each image
			$addClassesImage = $conf['addClassesImage'];
			if (isset($conf['addClassesImage.'])) {
				$addClassesImage = $this->pObj->cObj->stdWrap($addClassesImage, $conf['addClassesImage.']);
			}
			$addClassesImageConf = $GLOBALS['TSFE']->tmpl->splitConfArray(array('addClassesImage' => $addClassesImage), $colCount);

				// Render the images
			$images = '';
			for ($c = 0; $c < $imageWrapCols; $c++) {
				$tmpColspacing = $colspacing;
				if (($c == $imageWrapCols - 1 && $imagePosition == 2) || ($c == 0 && ($imagePosition == 1 || $imagePosition == 0))) {
						// Do not add spacing after column if we are first column (left) or last column (center/right)
					$tmpColspacing = 0;
				}

				$thisImages = '';
				$allRows = '';
				$maxImageSpace = 0;
				for ($i = $c; $i < count($imgsTag); $i = $i + $imageWrapCols) {
					$imgKey = $i + $imgStart;
					$colPos = $i % $colCount;
					if ($separateRows && $colPos == 0) {
						$thisRow = '';
					}

							// add DAM metadata to current object
					$this->addMetaToData($imgsExtraData[$i]);

						// Render one image
					if($origImages[$imgKey][0]==0) {
						$imageSpace = $this->pObj->cObj->data['imagewidth'] + $border * ($borderSpace + $borderThickness) * 2;
					} else {
						$imageSpace = $origImages[$imgKey][0] + $border * ($borderSpace + $borderThickness) * 2;
					}

					$GLOBALS['TSFE']->register['IMAGE_NUM'] = $imgKey;
					$GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] = $imgKey;
					$GLOBALS['TSFE']->register['ORIG_FILENAME'] = $origImages[$imgKey]['origFile'];
					$GLOBALS['TSFE']->register['imagewidth'] = $origImages[$imgKey][0];
					$GLOBALS['TSFE']->register['imagespace'] = $imageSpace;
					$GLOBALS['TSFE']->register['imageheight'] = $origImages[$imgKey][1];
					if ($imageSpace > $maxImageSpace) {
						$maxImageSpace = $imageSpace;
					}
					$thisImage = '';
					$thisImage .= $this->pObj->cObj->stdWrap($imgsTag[$imgKey], $conf['imgTagStdWrap.']);

					if (!$renderGlobalCaption) {
						$thisImage .= $this->pObj->cObj->stdWrap($this->pObj->cObj->cObjGet($conf['caption.'], 'caption.'), $conf['caption.']);
					}
					if ($editIconsHTML) {
						$thisImage .= $this->pObj->cObj->stdWrap($editIconsHTML, $conf['editIconsStdWrap.']);
					}
					$thisImage = $this->pObj->cObj->stdWrap($thisImage, $conf['oneImageStdWrap.']);
					$classes = '';
					if ($addClassesImageConf[$colPos]['addClassesImage']) {
						$classes = ' ' . $addClassesImageConf[$colPos]['addClassesImage'];
					}
					$thisImage = str_replace('###CLASSES###', $classes, $thisImage);

					if ($separateRows) {
						$thisRow .= $thisImage;
					} else {
						$allRows .= $thisImage;
					}
					$GLOBALS['TSFE']->register['columnwidth'] = $maxImageSpace + $tmpColspacing;


						// Close this row at the end (colCount), or the last row at the final end
					if ($separateRows && ($i + 1 == count($imgsTag))) {
							// Close the very last row with either normal configuration or lastRow stdWrap
						$allRows .= $this->pObj->cObj->stdWrap($thisRow, (is_array($conf['imageLastRowStdWrap.']) ? $conf['imageLastRowStdWrap.'] : $conf['imageRowStdWrap.']));
					} elseif ($separateRows && $colPos == $colCount - 1) {
						$allRows .= $this->pObj->cObj->stdWrap($thisRow, $conf['imageRowStdWrap.']);
					}
				}
				if ($separateRows) {
					$thisImages .= $allRows;
				} else {
					$thisImages .= $this->pObj->cObj->stdWrap($allRows, $conf['noRowsStdWrap.']);
				}
				if ($noRows) {
						// Only needed to make columns, rather than rows:
					$images .= $this->pObj->cObj->stdWrap($thisImages, $conf['imageColumnStdWrap.']);
				} else {
					$images .= $thisImages;
				}
			}

				// Add the global caption, if not split
			if ($globalCaption) {
				$images .= $globalCaption;
			}

				// CSS-classes
			$captionClass = '';
			$classCaptionAlign = array(
				'center' => 'csc-textpic-caption-c',
				'right' => 'csc-textpic-caption-r',
				'left' => 'csc-textpic-caption-l',
			);
			$captionAlign = $this->pObj->cObj->stdWrap($conf['captionAlign'], $conf['captionAlign.']);
			if ($captionAlign) {
				$captionClass = $classCaptionAlign[$captionAlign];
			}
			$borderClass = '';
			if ($border) {
				$borderClass = $conf['borderClass'] ? $conf['borderClass'] : 'csc-textpic-border';
			}

				// Multiple classes with all properties, to be styled in CSS
			$class = '';
			$class .= ($borderClass ? ' ' . $borderClass : '');
			$class .= ($captionClass ? ' ' . $captionClass : '');
			$class .= ($equalHeight ? ' csc-textpic-equalheight' : '');
			$addClasses = $this->pObj->cObj->stdWrap($conf['addClasses'], $conf['addClasses.']);
			$class .= ($addClasses ? ' ' . $addClasses : '');

				// Do we need a width in our wrap around images?
			$imgWrapWidth = '';
			if ($position == 0 || $position == 8) {
					// For 'center' we always need a width: without one, the margin:auto trick won't work
				$imgWrapWidth = $imageBlockWidth;
			}
			if ($rowCount > 1) {
					// For multiple rows we also need a width, so that the images will wrap
				$imgWrapWidth = $imageBlockWidth;
			}
			if ($globalCaption) {
					// If we have a global caption, we need the width so that the caption will wrap
				$imgWrapWidth = $imageBlockWidth;
			}

				// Wrap around the whole image block
			$GLOBALS['TSFE']->register['totalwidth'] = $imgWrapWidth;
			if ($imgWrapWidth) {
				$images = $this->pObj->cObj->stdWrap($images, $conf['imageStdWrap.']);
			} else {
				$images = $this->pObj->cObj->stdWrap($images, $conf['imageStdWrapNoWidth.']);
			}
		}

		$output = $this->pObj->cObj->cObjGetSingle($conf['layout'], $conf['layout.']);
		$output = str_replace('###TEXT###', $content, $output);
		$output = str_replace('###IMAGES###', $images, $output);
		$output = str_replace('###CLASSES###', $class, $output);

		if ($conf['stdWrap.'])	{
			$output = $this->pObj->cObj->stdWrap($output, $conf['stdWrap.']);
		}

		$this->removeMetaFromData();

		return $output;
	}




	/**
	 * Returns an object reference to the hook object if any
	 *
	 * @param	string		Name of the function you want to call / hook key
	 * @return	object		Hook object, if any. Otherwise null.
	 */
	function &hookRequest($functionName)	{
		global $TYPO3_CONF_VARS;

			// Hook: menuConfig_preProcessModMenu
		if ($TYPO3_CONF_VARS['EXTCONF']['dam_ttcontent']['pi1_hooks'][$functionName]) {
			$hookObj = &t3lib_div::getUserObj($TYPO3_CONF_VARS['EXTCONF']['dam_ttcontent']['pi1_hooks'][$functionName]);
			if (method_exists ($hookObj, $functionName)) {
				$hookObj->pObj = &$this;
				return $hookObj;
			}
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_ttcontent/pi_cssstyledcontent/class.tx_damttcontent_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_ttcontent/pi_cssstyledcontent/class.tx_damttcontent_pi1.php']);
}
?>