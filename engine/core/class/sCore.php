<?php
/**
 * Shopware 4
 * Copyright © shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Deprecated Shopware Class providing helper functions for post dispatch url rewriting
 */
class sCore
{
    /**
     * Pointer to Shopware Core functions
     *
     * @var sSystem
     */
    public $sSYSTEM;

    /**
     * Pointer to the Router
     *
     * @var Enlight_Controller_Router
     */
    public $router;

    public function __construct(Enlight_Controller_Router $router)
    {
        $this->router = $router ? : Shopware()->Front()->Router();
    }

    /**
     * Creates query string for an url based on sVariables and sSYSTEM->_GET
     *
     * @param array $sVariables Variables that configure the generated url
     * @return string
     */
    public function sBuildLink($sVariables)
    {
        $url = array();
        $allowedCategoryVariables = array("sCategory", "sPage");

        $tempGET = $this->sSYSTEM->_GET;

        // If viewport is available, this will be the first variable
        if (!empty($tempGET["sViewport"])) {
            $url['sViewport'] = $tempGET["sViewport"];
            if ($url["sViewport"] === "cat") {
                foreach ($allowedCategoryVariables as $allowedVariable) {
                    if (!empty($tempGET[$allowedVariable])) {
                        $url[$allowedVariable] = $tempGET[$allowedVariable];
                        unset($tempGET[$allowedVariable]);
                    }
                }
                $tempGET = array();
            }
            unset($tempGET["sViewport"]);
        }

        // Strip new variables from _GET
        foreach ($sVariables as $getKey => $getValue) {
            $tempGET[$getKey] = $getValue;
        }

        // Strip session from array
        unset($tempGET['coreID']);
        unset($tempGET['sPartner']);

        foreach ($tempGET as $getKey => $getValue) {
            if ($getValue) {
                $url[$getKey] = $getValue;
            }
        }

        if(!empty($url)) {
            $queryString = '?'.http_build_query($url,"","&");
        } else {
            $queryString = '';
        }

        return $queryString;
    }

    /**
     * Tries to rewrite the provided link using SEO friendly urls
     *
     * @param string $link The link to rewrite.
     * @param string $title Title of the link or related element.
     * @return mixed|string Complete url, rewritten if possible
     */
    public function sRewriteLink($link = null, $title = null)
    {
        $url = str_replace(',', '=', $link);
        $url = html_entity_decode($url);
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $query);

        if (!empty($title)) {
            $query['title'] = $title;
        }
        $query['module'] = 'frontend';

        return $this->router->assemble($query);
    }

    /**
     * Same as sRewriteLink, but with a different argument structure.
     *
     * @param $args
     * @return mixed|string
     */
    public function rewriteLink($args)
    {
        return $this->sRewriteLink($args[2], empty($args[3]) ? null : $args[3]);
    }
}
