<?php

namespace Derhaeuptling\MobileContent\EventListener;

use Contao\Cache;
use Contao\Config;
use Contao\Database;
use Contao\Date;
use Contao\Environment;
use Contao\Input;
use Contao\LayoutModel;
use Contao\PageModel;

class PageListener
{
    /**
     * Set the mobile layout if the domain is mobile
     *
     * @param PageModel   $page
     * @param LayoutModel &$layout
     */
    public function onGetPageLayout(PageModel $page, LayoutModel &$layout)
    {
        if ($page->isMobile || !Cache::has('mobile_domain')) {
            return;
        }

        $page->isMobile = (bool) Cache::get('mobile_domain');

        // Set the mobile layout if it's not set already
        if ($page->mobileLayout && (int) $page->mobileLayout !== (int) $layout->id) {
            $layout = LayoutModel::findByPk($page->mobileLayout);
        }
    }

    /**
     * Check if there is a mobile domain for this URL
     *
     * @return PageModel|null
     */
    public function onGetRootPageFromUrl()
    {
        // Determine the language
        if (Config::get('addLanguageToUrl') && !empty($_GET['language'])) {
            $language = Input::get('language');
        } else {
            $language = Environment::get('httpAcceptLanguage');

            // Always load the language fall back root if "doNotRedirectEmpty" is enabled
            if (Config::get('addLanguageToUrl') && Config::get('doNotRedirectEmpty')) {
                $language = '-';
            }
        }

        // Mark the page as mobile
        if (($page = $this->findRootPage(Environment::get('host'), $language)) !== null) {
            Cache::set('mobile_domain', true);
        }

        return $page;
    }

    /**
     * Find the root page
     *
     * @param string $host
     * @param string $language
     *
     * @return PageModel|null
     */
    private function findRootPage($host, $language)
    {
        $t  = PageModel::getTable();
        $db = Database::getInstance();

        if (is_array($language)) {
            $columns = ["$t.type='root' AND $t.enableMobileDns=1 AND ($t.mobileDns=? OR $t.mobileDns='')"];

            if (!empty($language)) {
                $columns[] = "($t.language IN('".implode("','", $language)."') OR $t.fallback='1')";
            } else {
                $columns[] = "$t.fallback='1'";
            }

            if (!isset($options['order'])) {
                $options['order'] = "$t.dns DESC".(!empty($language) ? ", ".$db->findInSet("$t.language", array_reverse($language))." DESC" : "").", $t.sorting";
            }

            if (!BE_USER_LOGGED_IN) {
                $time      = Date::floorToMinute();
                $columns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
            }

            return PageModel::findOneBy($columns, $host, $options);
        } else {
            $columns = ["$t.type='root' AND $t.enableMobileDns=1 AND ($t.mobileDns=? OR $t.mobileDns='') AND ($t.language=? OR $t.fallback='1')"];
            $values  = [$host, $language];

            if (!isset($options['order'])) {
                $options['order'] = "$t.dns DESC, $t.fallback";
            }

            if (!BE_USER_LOGGED_IN) {
                $time      = Date::floorToMinute();
                $columns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
            }

            return PageModel::findOneBy($columns, $values, $options);
        }
    }
}