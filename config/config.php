<?php

/**
 * mobilecontent extension for Contao Open Source CMS
 *
 * @author  Kamil Kuzminski <https://github.com/qzminski>
 * @author  derhaeuptling <https://derhaeuptling.com>
 * @author  Martin Schwenzer <mail@derhaeuptling.com>
 * @license LGPL
 */

/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['application']['mobile_switch'] = 'Derhaeuptling\MobileContent\FrontendModule\MobileSwitchModule';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['getPageLayout'][] = ['Derhaeuptling\MobileContent\EventListener\PageListener', 'onGetPageLayout'];
$GLOBALS['TL_HOOKS']['getRootPageFromUrl'][] = ['Derhaeuptling\MobileContent\EventListener\PageListener', 'onGetRootPageFromUrl'];
$GLOBALS['TL_HOOKS']['isVisibleElement'][] = ['Derhaeuptling\MobileContent\EventListener\ElementListener', 'onIsVisibleElement'];
$GLOBALS['TL_HOOKS']['initializeSystem'][] = ['Derhaeuptling\MobileContent\EventListener\PageListener', 'onInitializeSystem'];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = ['Derhaeuptling\MobileContent\EventListener\InsertTagsListener', 'onReplace'];

if (!is_array($GLOBALS['TL_HOOKS']['parseTemplate'])) {
    $GLOBALS['TL_HOOKS']['parseTemplate'] = [];
}

array_unshift($GLOBALS['TL_HOOKS']['parseTemplate'], ['Derhaeuptling\MobileContent\EventListener\TemplateListener', 'onParse']);
