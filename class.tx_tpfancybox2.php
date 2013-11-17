<?php
    /***************************************************************
    *  Copyright notice
    *
    *  (c) 2010 Samuel Koch <sam@koch.is>
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
    * [CLASS/FUNCTION INDEX of SCRIPT]
    *
    * Hint: use extdeveval to insert/update function index above.
    */

    //require_once(PATH_tslib.'class.tslib_pibase.php');


    /**
    * Plugin 'FancyBox2' for the 'tp_fancybox2' extension.
    *
    * @author    Tobias Perschon <tofu@perschon.at> original by Samuel Koch <sam@koch.is>
    * @package    TYPO3
    * @subpackage    tx_tpfancybox2
    */
    class tx_tpfancybox2 extends tslib_pibase {
        var $prefixId      = 'tx_tpfancybox2';        // Same as class name
        var $scriptRelPath = 'class.tx_tpfancybox2.php';    // Path to this script relative to the extension dir.
        var $extKey        = 'tp_fancybox2';    // The extension key.
        var $pi_checkCHash = true;
        var $extPath;

        /**
        * The main method of the PlugIn
        *
        * @param    string        $content: The PlugIn content
        * @param    array        $conf: The PlugIn configuration
        * @return    The content that is displayed on the website
        */
        function main($content,$conf) {
            $this->conf = $conf;

            //only execute this function if static template is included
            if(!$this->conf['static_template_included']) {
                return false;
            }

            $this->pi_loadLL();
            $this->extPath = t3lib_extMgm::siteRelPath($this->extKey);

            //get extconf values
            $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tp_fancybox2']);

            //check whether to include jQuery library
            if (!$extConf['dontIncludeJquery']) {
                //check whether extension t3jquery is loaded
                if (t3lib_extMgm::isLoaded('t3jquery')) {
                    require_once(t3lib_extMgm::extPath('t3jquery').'class.tx_t3jquery.php');
                }
                //if t3jquery is loaded and the custom library has been created
                if (T3JQUERY === true) {
                    tx_t3jquery::addJqJS();
                }
                //otherwise include jQuery file
                else {
                    $addJsFiles[] = $this->extPath.'fancybox2/jquery-1.10.2.min.js';
                }
            }

            //check whether to include jQuery easing
            if ($extConf['includeJqueryEasing']) {
                $addJsFiles[] = $this->extPath.'fancybox2/jquery.easing.1.3.pack.js';
            }
            //check whether to include jQuery mousewheel
            if ($extConf['includeJqueryMousewheel']) {
                $addJsFiles[] = $this->extPath.'fancybox2/jquery.mousewheel.3.1.3.pack.js';
            }

            //check whether to include the FancyBox library
            if (!$extConf['dontIncludeFancyboxJs']) {
                $addJsFiles[] = $this->extPath.'fancybox2/jquery.fancybox.pack.js';
            }

            //check whether to include the FancyBox stylesheet
            if (strlen($this->conf['stylesheetPath']) > 0) {
                $addCssFiles[] = $this->conf['stylesheetPath'];
            }
			
			//fancybox2 helpers
			//check whether to include the thumbnail helper
            if (!$extConf['includeThumbnailHelper']) {
                $addJsFiles[] = $this->extPath.'fancybox2/helpers/jquery.fancybox-thumbs.js';
				$addCssFiles[] = $this->extPath.'fancybox2/helpers/jquery.fancybox-thumbs.css';
            }
			
			//check whether to include the media helper
			if (!$extConf['includeMediaHelper']) {
                $addJsFiles[] = $this->extPath.'fancybox2/helpers/jquery.fancybox-media.js';
            }
			
			//check whether to include the button helper
			if (!$extConf['includeButtonHelper']) {
                $addJsFiles[] = $this->extPath.'fancybox2/helpers/jquery.fancybox-buttons.js';
				$addCssFiles[] = $this->extPath.'fancybox2/helpers/jquery.fancybox-buttons.css';
            }

            //check TYPO3 version to see whether pagerenderer is available
            if(class_exists('t3lib_utility_VersionNumber')) {
                $typo3Version = t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version);
            }
            else {
                $typo3Version = t3lib_div::int_from_ver(TYPO3_version);
            }
            //check TYPO3 version to see whether pagerenderer is available
            if ($typo3Version >= 4003000) {
                //get pagerenderer
                $pagerender = $GLOBALS['TSFE']->getPageRenderer();

                //add JS and CSS files
                foreach($addJsFiles as $jsFile) {
                    $pagerender->addJsFile($jsFile);
                }
                foreach($addCssFiles as $CssFile) {
                    $pagerender->addCssFile($CssFile);
                }
            }
            //if pagerenderer is not available add JS and CSS files the old way
            else {
                foreach($addJsFiles as $jsFile) {
                    $headerParts .= '<script type="text/javascript" src="'.$jsFile.'"></script>';
                }
                foreach($addCssFiles as $CssFile) {
                    $headerParts .= '<link rel="stylesheet" href="'.$CssFile.'" type="text/css" media="screen"/>';
                }
                $GLOBALS['TSFE']->additionalHeaderData['EXT:' . $this->extKey] = $headerParts;
            }


            //process display of the title
            if($this->conf['titlePosition'] == "over") {
                $titleFormat = '
                function(title, currentArray, currentIndex, currentOpts) {
                if(title.length > 0 || currentArray.length > 1) {
                var preCaption = \'\';
                if(currentArray.length > 1) {
                preCaption = \''.$this->pi_getLL('image').' \' + (currentIndex + 1) + \' '.$this->pi_getLL('of').' \' + currentArray.length + (title.length ? \' : &nbsp; \' : \'\');
                }
                return \'<span id="fancybox-title-over">\' + preCaption + title + \'</span>\';
                }
                return false;
                }';
                $titleFormat = str_replace(array("\r", "\n"), '', $titleFormat);
            }
            else {
                $titleFormat = '"formatTitle"';
            }

            //set FancyBox options
			if (!$extConf['dontIncludeFancyBox2Call']) {
				$GLOBALS['TSFE']->inlineJS['tp_fancybox2'] = '
				jQuery(document).ready(function() {
	
				jQuery(".fancybox").fancybox({
				"padding": '.$this->conf['padding'].',
				"margin": '.$this->conf['margin'].',
				"width": '.$this->conf['width'].',
				"height": '.$this->conf['height'].',
				"minWidth": '.$this->conf['minWidth'].',
				"minHeight": '.$this->conf['minHeight'].',
				"maxWidth": '.$this->conf['maxWidth'].',
				"maxHeight": '.$this->conf['maxHeight'].',
				"autoSize": '.($this->conf['autoSize'] ? 'true' : 'false').',
				"loop": '.($this->conf['loop'] ? 'true' : 'false').',
				"scrolling": "'.$this->conf['scrolling'].'",
				"autoDimensions": '.($this->conf['autoDimensions'] ? 'true' : 'false').',
				"centerOnScroll": '.($this->conf['centerOnScroll'] ? 'true' : 'false').',
				"hideOnOverlayClick": '.($this->conf['hideOnOverlayClick'] ? 'true' : 'false').',
				"hideOnContentClick": '.($this->conf['hideOnContentClick'] ? 'true' : 'false').',
				"overlayShow": '.($this->conf['overlayShow'] ? 'true' : 'false').',
				"overlayOpacity": '.$this->conf['overlayOpacity'].',
				"overlayColor": "'.$this->conf['overlayColor'].'",
				"titleShow": '.($this->conf['titleShow'] ? 'true' : 'false').',
				'.($this->conf['titlePosition'] != 'outside-bar' ? '"titlePosition": "'.$this->conf['titlePosition'].'",' : '').'
				"titleFormat": '.$this->conf['titleFormat'].',
				"transitionIn": "'.$this->conf['transitionIn'].'",
				"transitionOut": "'.$this->conf['transitionOut'].'",
				"speedIn": '.$this->conf['speedIn'].',
				"speedOut": '.$this->conf['speedOut'].',
				"changeSpeed": '.$this->conf['changeSpeed'].',
				"changeFade": "'.$this->conf['changeFade'].'",
				"easingIn": "'.$this->conf['easingIn'].'",
				"easingOut": "'.$this->conf['easingOut'].'",
				"showCloseButton": '.($this->conf['showCloseButton'] ? 'true' : 'false').',
				"showNavArrows": '.($this->conf['showNavArrows'] ? 'true' : 'false').',
				"enableEscapeButton": '.($this->conf['enableEscapeButton'] ? 'true' : 'false').',
				"titleFormat": '.$titleFormat.'
				});
	
				});';
			}
        }
		


        /**
        * Function that sets the register var "IMAGE_NUM_CURRENT" to the the current image number.
        *
        * BEWARE: Since tt_news 3.0 this won't work until Rupert updates hooks for marker-processing
        *
        * @param    array        $paramArray: $markerArray and $config of the current news item in an array
        * @param    [type]        $conf: ...
        * @return    array        the processed markerArray
        */
        function ttnewsImageMarkerFunc($paramArray,$conf) {
            $markerArray = $paramArray[0];
            $lConf = $paramArray[1];
            $pObj = &$conf['parentObj'];
            $row = $pObj->local_cObj->data;



            $imageNum = isset($lConf['imageCount']) ? $lConf['imageCount']:1;
            $theImgCode = '';
            $imgs = t3lib_div::trimExplode(',', $row['image'], 1);
            $imgsCaptions = explode(chr(10), $row['imagecaption']);
            $imgsAltTexts = explode(chr(10), $row['imagealttext']);
            $imgsTitleTexts = explode(chr(10), $row['imagetitletext']);

            reset($imgs);

            $cc = 0;

            // BEN: We need to mark these items prior to arrayshifting
            if (count($imgs) == 1 &&
                $pObj->config['firstImageIsPreview'] &&
                $pObj->config['code'] == 'SINGLE' &&
                !$pObj->config['forceFirstImageIsPreview'])
            {
                $markedAsSpecial = 1;
            }
            // END.

            // remove first img from the image array in single view if the TSvar firstImageIsPreview is set
            if ((    (count($imgs) > 1 && $pObj->config['firstImageIsPreview'])
                ||
                (count($imgs) >= 1 && $pObj->config['forceFirstImageIsPreview'])
                ) && $pObj->config['code'] == 'SINGLE') {
                array_shift($imgs);
                array_shift($imgsCaptions);
                array_shift($imgsAltTexts);
                array_shift($imgsTitleTexts);
            }
            // get img array parts for single view pages
            if ($this->piVars[$pObj->config['singleViewPointerName']]) {
                $spage = $this->piVars[$pObj->config['singleViewPointerName']];
                $astart = $imageNum*$spage;
                $imgs = array_slice($imgs,$astart,$imageNum);
                $imgsCaptions = array_slice($imgsCaptions,$astart,$imageNum);
                $imgsAltTexts = array_slice($imgsAltTexts,$astart,$imageNum);
                $imgsTitleTexts = array_slice($imgsTitleTexts,$astart,$imageNum);
            }

            while (list(, $val) = each($imgs)) {
                if ($cc == $imageNum) break;
                if ($val) {

                    $lConf['image.']['altText'] = $imgsAltTexts[$cc];
                    $lConf['image.']['titleText'] = $imgsTitleTexts[$cc];
                    $lConf['image.']['file'] = 'uploads/pics/' . $val;
                    // BEN: We check count of images >(=) 0 here because the array got shifted before!!! (See above)
                    if ((    (count($imgs) > 0 && $pObj->config['firstImageIsPreview'])
                        ||
                        (count($imgs) >= 0 && $pObj->config['forceFirstImageIsPreview'])
                        ) && $pObj->config['code'] == 'SINGLE') {
                        // BEN: Additionally we need to check our special case
                        if (count($imgs) == 1 && $markedAsSpecial) {
                            $GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] = $cc;
                        } else {
                            $GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] = $cc+1;
                        }
                    } else {
                        $GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] = $cc;
                    }
                    // END.
                }

                $theImgCode .= $pObj->local_cObj->IMAGE($lConf['image.']) . $pObj->local_cObj->stdWrap($imgsCaptions[$cc], $lConf['caption_stdWrap.']);
                $cc++;
            }
            $markerArray['###NEWS_IMAGE###'] = '';
            if ($cc) {
                $markerArray['###NEWS_IMAGE###'] = $pObj->local_cObj->wrap(trim($theImgCode), $lConf['imageWrapIfAny']);
            } else {
                $markerArray['###NEWS_IMAGE###'] = $pObj->local_cObj->stdWrap($markerArray['###NEWS_IMAGE###'],$lConf['image.']['noImage_stdWrap.']);
            }
            return $markerArray;
        }

    }



    if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tp_fancybox2/class.tx_tpfancybox2.php'])    {
        include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tp_fancybox2/class.tx_tpfancybox2.php']);
    }

?>