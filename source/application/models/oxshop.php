<?php
/**
 * This file is part of OXID eShop Community Edition.
 *
 * OXID eShop Community Edition is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Community Edition is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 * @version   OXID eShop CE
 */

/**
 * Shop manager.
 * Performs configuration and object loading or deletion.
 *
 */
class oxShop extends oxI18n
{
    /**
     * Name of current class.
     *
     * @var string
     */
    protected $_sClassName = 'oxshop';

    /**
     * Multi shop tables, set in config.
     *
     * @var array
     */
    protected $_aMultiShopTables = array();


    /**
     * @var $_aQueries array variable
     */
    protected $_aQueries = array();

    /**
     * @var $_aTables array variable
     */
    protected $_aTables = null;

    /**
     * $_aTables setter
     *
     * @param array $aTables
     */
    public function setTables($aTables)
    {
        $this->_aTables = $aTables;
    }

    /**
     * $_aTables getter
     *
     * @return array
     */
    public function getTables()
    {
        if (is_null($this->_aTables)) {
            $aMultilangTables = oxRegistry::getLang()->getMultiLangTables();
            $aMultishopTables = $this->getMultiShopTables();
            $this->setTables(array_unique(array_merge($aMultishopTables, $aMultilangTables)));
        }
        return $this->_aTables;
    }

    /**
     * $_aQueries setter
     *
     * @param array $aQueries
     */
    public function setQueries($aQueries)
    {
        $this->_aQueries = $aQueries;
    }

    /**
     * $_aQueries getter
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->_aQueries;
    }

    /**
     * Add a query to query array
     *
     * @param $sQuery
     */
    public function addQuery($sQuery)
    {
        $this->_aQueries[] = $sQuery;
    }
    /**
     * Class constructor, initiates parent constructor (parent::oxBase()).
     */
    public function __construct()
    {
        parent::__construct();


        $this->init( 'oxshops' );

        if ( $iMax = $this->getConfig()->getConfigParam('iMaxShopId') ) {
            $this->setMaxShopId( $iMax );
        }

    }

    /**
     * Sets multi shop tables
     *
     * @param string $aMultiShopTables multi shop tables
     *
     * @return null
     */
    public function setMultiShopTables( $aMultiShopTables )
    {
        $this->_aMultiShopTables = $aMultiShopTables;
    }

    /**
     * Get multishop table array
     *
     * @return array
     */
    public function getMultiShopTables()
    {
        if (is_null($this->_aMultiShopTables)) {
            $this->_aMultiShopTables = array();
        }

        return $this->_aMultiShopTables;
    }


    /**
     * (Re)generates shop views
     *
     * @param bool  $blMultishopInheritCategories config option blMultishopInherit
     * @param array $aMallInherit                 array of config options blMallInherit
     *
     * @return bool is all views generated successfully
     */
    public function generateViews( $blMultishopInheritCategories = false, $aMallInherit = null )
    {
        $this->_prepareViewsQueries($blMultishopInheritCategories, $aMallInherit);
        $bSuccess = $this->_runQueries();

        $this->_cleanInvalidViews();

        return $bSuccess;
    }

    /**
     * Returns table field name mapping sql section for single language views
     *
     * @param string $sTable table name
     * @param array  $iLang  language id
     *
     * @return string $sSQL
     */
    protected function _getViewSelect($sTable,$iLang)
    {
        $oMetaData = oxNew('oxDbMetaDataHandler');
        $aFields = $oMetaData->getSinglelangFields($sTable, $iLang);
        foreach ($aFields as $sCoreField => $sField) {
            if ($sCoreField !== $sField) {
                $aFields[$sCoreField] = $sField.' AS '.$sCoreField;
            }
        }

        return implode(',', $aFields);
    }

    /**
     * Returns all language table view JOIN section
     *
     * @param string $sTable table name
     *
     * @return string $sSQL
     */
    protected function _getViewJoinAll($sTable)
    {
        $sJoin = ' ';
        $oMetaData = oxNew('oxDbMetaDataHandler');
        $aTables = $oMetaData->getAllMultiTables($sTable);
        if (count($aTables)) {
            foreach ($aTables as $sTableKey => $sTableName) {
                $sJoin .= "LEFT JOIN {$sTableName} USING (OXID) ";
            }
        }
        return $sJoin;
    }

    /**
     * Returns language table view JOIN section
     *
     * @param string $sTable table name
     * @param array  $iLang  language id
     *
     * @return string $sSQL
     */
    protected function _getViewJoinLang($sTable,$iLang)
    {
        $sJoin = ' ';
        $sLangTable = getLangTableName($sTable, $iLang);
        if ($sLangTable && $sLangTable !== $sTable) {
            $sJoin .= "LEFT JOIN {$sLangTable} USING (OXID) ";
        }
        return $sJoin;
    }


    /**
     * Returns default category of the shop.
     *
     * @return string
     */
    public function getDefaultCategory()
    {
        return $this->oxshops__oxdefcat->value;
    }

    /**
     * Returns true if shop in productive mode
     *
     * @return bool
     */
    public function isProductiveMode()
    {
        return (bool) $this->oxshops__oxproductive->value;
    }

    /**
     * Gets all invalid views and drops them from database
     */
    protected function _cleanInvalidViews()
    {
        $oDb = oxDb::getDb();
        $oLang = oxRegistry::getLang();
        $aLanguages = $oLang->getLanguageIds($this->getId());

        $aMultilangTables = oxRegistry::getLang()->getMultiLangTables();
        $aMultishopTables = $this->getMultiShopTables();

        $oLang = oxRegistry::getLang();
        $aAllShopLanguages = $oLang->getAllShopLanguageIds();

        /** @var oxShopViewValidator $oViewsValidator */
        $oViewsValidator = oxNew('oxShopViewValidator');

        $oViewsValidator->setShopId($this->getId());
        $oViewsValidator->setLanguages($aLanguages);
        $oViewsValidator->setAllShopLanguages($aAllShopLanguages);
        $oViewsValidator->setMultiLangTables($aMultilangTables);
        $oViewsValidator->setMultiShopTables($aMultishopTables);

        $aViews = $oViewsValidator->getInvalidViews();

        foreach ($aViews as $sView) {
            $oDb->execute('DROP VIEW IF EXISTS ' . $sView);
        }
    }

    /**
     * @param $blMultishopInheritCategories
     * @param $aMallInherit
     *
     * @return array
     */
    protected function _prepareViewsQueries($blMultishopInheritCategories, $aMallInherit)
    {
        $oLang = oxRegistry::getLang();
        $aLanguages = $oLang->getLanguageIds($this->getId());

        // @todo move edition specific code to separate methods
        $aMultilangTables = oxRegistry::getLang()->getMultiLangTables();
        $aMultishopTables = $this->getMultiShopTables();
        $aTables = $this->getTables();

        foreach ($aTables as $sTable) {
            $this->addQuery(
            'CREATE OR REPLACE SQL SECURITY INVOKER VIEW oxv_' . $sTable
            . ' AS SELECT * FROM ' . $sTable . ' ' . $this->_getViewJoinAll($sTable)
            );

            if (in_array($sTable, $aMultilangTables)) {
                foreach ($aLanguages as $iLang => $sLang) {
                    $this->addQuery(
                    'CREATE OR REPLACE SQL SECURITY INVOKER VIEW oxv_' . $sTable . '_' . $sLang . ' AS SELECT ' . $this->_getViewSelect(
                    $sTable, $iLang
                    ) . ' FROM ' . $sTable . ' ' . $this->_getViewJoinLang($sTable, $iLang)
                    );
                }
            }
        }
    }

    /**
     * Runs stored queries
     * Returns false when any of the queries fail, otherwise return true
     *
     * @return bool
     */
    protected function _runQueries()
    {
        $oDb = oxDb::getDb();
        $aQueries = $this->getQueries();
        $bSuccess = true;
        foreach ($aQueries as $sQuery) {
            if (!$oDb->execute($sQuery)) {
                $bSuccess = false;
            }
        }

        return $bSuccess;
    }

}
