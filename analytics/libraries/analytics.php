<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Google Analytics PHP API
 *
 * This class can be used to retrieve data from the Google Analytics API with PHP
 * It fetches data as array for use in applications or scripts
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * Credits: http://www.alexc.me/
 * parsing the profile XML to a PHP array
 *
 *
 * @link http://www.swis.nl
 * @copyright 2009 SWIS BV
 * @author Vincent Kleijnendorst - SWIS BV (vkleijnendorst [AT] swis [DOT] nl)
 *
 * @version 0.1
 */

class analytics
{
	private $_sUser;
	private $_sPass;
	private $_sAuth;
	private $_sProfileId;

	private $_sStartDate;
	private $_sEndDate;

	private $_bUseCache;
	private $_iCacheAge;

    protected $accountFeedUrl = 'https://www.google.com/analytics/feeds/accounts/default';
    protected $clientLoginUrl = 'https://www.google.com/accounts/ClientLogin';
    protected $dataFeedUrl    = 'https://www.google.com/analytics/feeds/data';

    protected $authString = 'Authorization: GoogleLogin auth=';
    protected $queryProfileKey   = 'ids';
    protected $queryStartDateKey = 'start-date';
    protected $queryEndDateKey   = 'end-date';


	/**
	 * Public constructor
	 *
	 * @return void
	 */
	public function __construct()
	{

	}

	/**
	 * Log in to Google Analytics
	 *
	 * @param	string	$sUser	Username
	 * @param	string	$sPass	Password
	 *
	 * @return void
	 */
	public function login($sUser, $sPass)
	{
		$this->_sUser	  = $sUser;
		$this->_sPass	  = $sPass;
		$this->_bUseCache = true;

		$this->auth();
	}

	/**
	 * Google Authentication, configures session when set
	 *
	 * @return void
	 */
	private function auth()
	{
		if (isset($_SESSION['auth'])) {
			$this->_sAuth = $_SESSION['auth'];
			return;
		}

		$aPost = array(
			'accountType' => 'GOOGLE',
			'Email'		  => $this->_sUser,
			'Passwd'	  => $this->_sPass,
			'service'	  => 'analytics',
			'source'	  => 'SWIS-Webbeheer-4.0',
		);

		$sResponse = $this->getUrl($this->clientLoginUrl, $aPost);
		$_SESSION['auth'] = '';

		if (strpos($sResponse, "\n") !== false) {
			$aResponse = explode("\n", $sResponse);
			foreach ($aResponse as $sResponse) {
				if (substr($sResponse, 0, 4) == 'Auth') {
					$_SESSION['auth'] = trim(substr($sResponse, 5));
				}
			}
		}

		if ($_SESSION['auth'] == '') {
			unset($_SESSION['auth']);
			throw new Exception('Retrieving Auth hash failed!');
		}

		$this->_sAuth = $_SESSION['auth'];
	}

	/**
	 * Configure storage of GA data in a session for a given period.
	 *
	 * @param bool $bCaching true to enable session caching, false to disable
	 * @param int $iCacheAge number of seconds to store the data (default: 10
	 * minutes - umm... looks more like 1 minute)
	 *
	 * @return void
	 */
	public function useCache($bCaching = true, $iCacheAge = 600)
	{
		$this->_bUseCache = $bCaching;
		$this->_iCacheAge = $iCacheAge;

		if ($bCaching && ! isset($_SESSION['cache'])) {
			$_SESSION['cache'] = array();
		}
	}

	/**
	 * Get GA XML with auth key.
	 *
	 * @param string $sUrl
	 *
	 * @return string XML
	 */
	private function getXml($sUrl)
	{
		return $this->getUrl($sUrl, array(), array($this->authString . $this->_sAuth));
	}

	/**
	 * Sets GA Profile ID.
	 *
	 * @param string $sProfileId The GA Profile ID (for example: ga:12345).
	 *
	 * @return void
	 */
	public function setProfileById($sProfileId)
	{
		$this->_sProfileId = $sProfileId;
	}

	/**
	 * Sets Profile ID by a given account name.
	 *
	 * @param string $sAccountName The account name.
	 *
	 * @return void
	 */
	public function setProfileByName($sAccountName)
	{
		if (isset($_SESSION['profile'])) {
			$this->_sProfileId = $_SESSION['profile'];
			return;
		}

		$this->_sProfileId = '';
		$sXml = $this->getXml($this->accountFeedUrl);
		$aAccounts = $this->parseAccountList($sXml);

		foreach ($aAccounts as $aAccount) {
			if (isset($aAccount['accountName'])
                && $aAccount['accountName'] == $sAccountName
                && isset($aAccount['tableId'])
            ) {
                $this->_sProfileId =  $aAccount['tableId'];
			}
		}

		if ($this->_sProfileId == '') {
			throw new Exception('No profile ID found!');
		}

		$_SESSION['profile'] = $this->_sProfileId;
	}

	/**
	 * Returns an array with profileID => accountName
	 */
	public function getProfileList()
	{
		$sXml = $this->getXml($this->accountFeedUrl);
		$aAccounts = $this->parseAccountList($sXml);
		$aReturn = array();

		foreach ($aAccounts as $aAccount) {
			$aReturn[$aAccount['tableId']] = $aAccount['title'];
		}

		return $aReturn;
	}

	/**
	 * Get results from cache if set and not older then cacheAge.
	 *
	 * @param string $sKey The key to retrieve from the cache.
	 *
	 * @return mixed Cached data or false if the item was not found (or was too
	 * old).
	 */
	private function getCache($sKey)
	{
		if ($this->_bUseCache === false) {
			return false;
		}

		if ( ! isset($_SESSION['cache'][$this->_sProfileId])) {
			$_SESSION['cache'][$this->_sProfileId] = array();
		}

		if (isset($_SESSION['cache'][$this->_sProfileId][$sKey])) {
			if (time() - $_SESSION['cache'][$this->_sProfileId][$sKey]['time'] < $this->_iCacheAge) {
				return $_SESSION['cache'][$this->_sProfileId][$sKey]['data'];
			}
		}

		return false;
    }

	/**
	 * Cache data in session.
	 *
	 * @param string $sKey The key to use to store/retrieve the data.
	 * @param mixed $mData The data to cache
	 *
	 * @return void
	 */
	private function setCache($sKey, $mData)
	{
		if ($this->_bUseCache === false) {
			return false;
		}

		if ( ! isset($_SESSION['cache'][$this->_sProfileId])) {
			$_SESSION['cache'][$this->_sProfileId] = array();
		}

		$_SESSION['cache'][$this->_sProfileId][$sKey] = array(
			'time'  => time(),
			'data'  => $mData
		);
	}

	/**
	 * Parses GA XML to an array (dimension => metric)
	 *
	 * @link http://code.google.com/intl/nl/apis/analytics/docs/gdata/gdataReferenceDimensionsMetrics.html
	 * Check the link for usage of dimensions and metrics
	 *
	 * @param array  $aProperties GA properties: metrics & dimensions
	 *
	 * @return array result
	 */
	public function getData($aProperties = array())
	{
        $aParams = array(
            "{$this->queryProfileKey}={$this->_sProfileId}",
            "{$this->queryStartDateKey}={$this->_sStartDate}",
            "{$this->queryEndDateKey}={$this->_sEndDate}",
        );
		foreach ($aProperties as $sKey => $sProperty) {
			$aParams[] = "{$sKey}={$sProperty}";
		}
		$sUrl = "{$this->dataFeedUrl}?" . implode('&', $aParams);

		$aCache = $this->getCache($sUrl);
		if ($aCache !== false) {
			return $aCache;
		}

		$sXml = $this->getXml($sUrl);
        $aResult = array();

        $oDoc = new DOMDocument();
		$oDoc->loadXML($sXml);
		$oEntries = $oDoc->getElementsByTagName('entry');
		foreach ($oEntries as $oEntry) {
			$oTitle = $oEntry->getElementsByTagName('title');
			$sTitle = $oTitle->item(0)->nodeValue;

			$oMetric = $oEntry->getElementsByTagName('metric');

			// Fix the array key when multiple dimensions are given
			if (strpos($sTitle, ' | ') !== false
                && strpos($aProperties['dimensions'], ',') !== false
            ) {
				$aDimensions = explode(',', $aProperties['dimensions']);
				$aDimensions[] = '|';
				$aDimensions[] = '=';
				$sTitle = preg_replace('/\s\s+/', ' ', trim(str_replace($aDimensions, '', $sTitle)));
			}
			$sTitle = str_replace($aProperties['dimensions'] . '=', '', $sTitle);
			$aResult[$sTitle] = $oMetric->item(0)->getAttribute('value');
		}

		// Cache the results
		$this->setCache($sUrl, $aResult);

		return $aResult;
	}

	/**
	 * Parse XML from account list.
	 *
	 * @param string $sXml
	 *
	 * @return array Result.
	 */
	private function parseAccountList($sXml)
	{
		$oDoc = new DOMDocument();
		$oDoc->loadXML($sXml);
		$oEntries = $oDoc->getElementsByTagName('entry');

		$i = 0;
		$aProfiles = array();
		foreach ($oEntries as $oEntry) {
			$aProfiles[$i] = array();
			$oTitle = $oEntry->getElementsByTagName('title');
			$aProfiles[$i]["title"] = $oTitle->item(0)->nodeValue;

			$oEntryId = $oEntry->getElementsByTagName('id');
			$aProfiles[$i]["entryid"] = $oEntryId->item(0)->nodeValue;
			$oProperties = $oEntry->getElementsByTagName('property');

			foreach ($oProperties as $oProperty) {
				if (strcmp($oProperty->getAttribute('name'), 'ga:accountId') == 0) {
					$aProfiles[$i]["accountId"] = $oProperty->getAttribute('value');
				}
				if (strcmp($oProperty->getAttribute('name'), 'ga:accountName') == 0) {
					$aProfiles[$i]["accountName"] = $oProperty->getAttribute('value');
				}
				if (strcmp($oProperty->getAttribute('name'), 'ga:profileId') == 0) {
					$aProfiles[$i]["profileId"] = $oProperty->getAttribute('value');
				}
				if (strcmp($oProperty->getAttribute('name'), 'ga:webPropertyId') == 0) {
					$aProfiles[$i]["webPropertyId"] = $oProperty->getAttribute('value');
				}
			}

			$oTableId = $oEntry->getElementsByTagName('tableId');
			$aProfiles[$i]["tableId"] = $oTableId->item(0)->nodeValue;

			$i++;
		}

		return $aProfiles;
	}

	/**
	 * Get data from given URL.
	 *
	 * Uses Curl if installed, falls back to file_get_contents if not.
	 *
	 * @param string $sUrl
	 * @param array $aPost
	 * @param array $aHeader
	 *
	 * @return string Response
	 */
	private function getUrl($sUrl, $aPost = array(), $aHeader = array())
	{
		if (count($aPost) > 0) {
			// Build POST query
			$sMethod = 'POST';
			$sPost = http_build_query($aPost);
			$aHeader[] = 'Content-type: application/x-www-form-urlencoded';
			$aHeader[] = 'Content-Length: ' . strlen($sPost);
			$sContent = $aPost;
		} else {
			$sMethod = 'GET';
			$sContent = null;
		}

		// If Curl is installed, use it!
		if (function_exists('curl_init')) {
			$rRequest = curl_init();

			// door frederik
			curl_setopt($rRequest, CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($rRequest, CURLOPT_CAINFO, APPPATH . '../modules/analytics/config/cacert.pem');
			curl_setopt($rRequest, CURLOPT_URL, $sUrl);
			curl_setopt($rRequest, CURLOPT_RETURNTRANSFER, 1);

			if ($sMethod == 'POST') {
				curl_setopt($rRequest, CURLOPT_POST, 1);
				curl_setopt($rRequest, CURLOPT_POSTFIELDS, $aPost);
			} else {
				curl_setopt($rRequest, CURLOPT_HTTPHEADER, $aHeader);
			}

			$sOutput = curl_exec($rRequest);
			if ($sOutput === false) {
				throw new Exception('Curl error (' . curl_error($rRequest) . ')');
			}

			$aInfo = curl_getinfo($rRequest);

			// Not a valid response from GA
			if ($aInfo['http_code'] != 200) {
                switch ($aInfo['http_code']) {
                    case 400:
                        throw new Exception("Bad request ({$aInfo['http_code']}) url: {$sUrl}");
                        break;

                    case 403:
                        throw new Exception("Access denied ({$aInfo['http_code']}) url: {$sUrl}");
                        break;

                    default:
                        throw new Exception("Not a valid response ({$aInfo['http_code']}) url: {$sUrl}");
                        break;
                }
			}

			curl_close($rRequest);
		}
		// Curl is not installed, use file_get_contents
		else {
			// Create headers and post
            $aContext = array(
                'http' => array(
                    'method'  => $sMethod,
                    'header'  => implode("\r\n", $aHeader) . "\r\n",
                    'content' => $sContent,
                ),
            );
			$rContext = stream_context_create($aContext);
			$sOutput  = @file_get_contents($sUrl, 0, $rContext);

			// Not a valid response from GA
			if (strpos($http_response_header[0], '200') === false) {
				throw new Exception("Not a valid response ({$http_response_header[0]}) url: {$sUrl}");
			}
		}

		return $sOutput;
	}

	/**
	 * Sets the date range for GA data
	 *
	 * @param string $sStartDate (YYY-MM-DD)
	 * @param string $sEndDate   (YYY-MM-DD)
	 *
	 * @return void
	 */
	public function setDateRange($sStartDate, $sEndDate)
	{
        $this->_sStartDate = $sStartDate;
        $this->_sEndDate   = $sEndDate;
	}

	/**
	 * Sets the data range to a given month
	 *
	 * @param int $iMonth
	 * @param int $iYear
	 *
	 * @return void
	 */
	public function setMonth($iMonth, $iYear)
	{
		$this->_sStartDate = date('Y-m-d', strtotime("{$iYear}-{$iMonth}-01"));
		$this->_sEndDate   = date('Y-m-d', strtotime("{$iYear}-{$iMonth}-" . date('t', strtotime("{$iYear}-{$iMonth}-01"))));
	}

	/**
	 * Get visitors for given period
	 *
	 * @return array result
	 */
	public function getVisitors()
	{
        return $this->getData(array(
            'dimensions' => 'ga:day',
            'metrics'    => 'ga:visits',
            'sort'       => 'ga:day',
        ));
	}

	/**
	 * Get pageviews for given period
	 *
	 * @return array result
	 */
	public function getPageviews()
	{
        return $this->getData(array(
            'dimensions' => 'ga:day',
            'metrics'    => 'ga:pageviews',
            'sort'       => 'ga:day',
        ));
	}

	/**
	 * Get visitors per hour for given period
	 *
	 * @return array result
	 */
	public function getVisitsPerHour()
	{
        return $this->getData(array(
            'dimensions' => 'ga:hour',
            'metrics'    => 'ga:visits',
            'sort'       => 'ga:hour',
        ));
	}

	/**
	 * Get Browsers for given period
	 *
	 * @return array Result
	 */
	public function getBrowsers()
	{
        $aData = $this->getData(array(
            'dimensions' => 'ga:browser,ga:browserVersion',
            'metrics'    => 'ga:visits',
            'sort'       => 'ga:visits',
        ));

        arsort($aData);
        return $aData;
	}

	/**
	 * Get Operating System for given period
	 *
	 * @return array Result
	 */
	public function getOperatingSystem()
	{
        $aData = $this->getData(array(
            'dimensions' => 'ga:operatingSystem',
            'metrics'    => 'ga:visits',
            'sort'       => 'ga:visits',
        ));

        // Sort descending by number of visits
        arsort($aData);
        return $aData;
	}

	/**
	 * Get screen resolution for given period.
	 *
	 * @return array Result.
	 */
	public function getScreenResolution()
	{
        $aData = $this->getData(array(
            'dimensions' => 'ga:screenResolution',
            'metrics'    => 'ga:visits',
            'sort'       => 'ga:visits',
        ));

        // Sort descending by number of visits
        arsort($aData);
        return $aData;
	}

	/**
	 * Get referrers for given period.
	 *
	 * @return array Result.
	 */
	public function getReferrers()
	{
        $aData = $this->getData(array(
            'dimensions' => 'ga:source',
            'metrics'    => 'ga:visits',
            'sort'       => 'ga:source',
        ));

        // Sort descending by number of visits
        arsort($aData);
        return $aData;
	}

	/**
	 * Get search words for given period
	 *
	 * @return array Result.
	 */
	public function getSearchWords()
	{
        $aData = $this->getData(array(
            'dimensions' => 'ga:keyword',
            'metrics'    => 'ga:visits',
            'sort'       => 'ga:keyword',
        ));

        // Sort descending by number of visits
        arsort($aData);
        return $aData;
	}

	// ga:visits, ga:bounces, ga:entrances, ga:pageviews, ga:timeOnSite, ga:newVisits
}
/* /analytics/libraries/analytics.php */