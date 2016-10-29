class Request {

    public static $referer = null;
	public static $cookies = [];
	public $localCookies = [];

	public $customHeaders = [];
	public $curlOptions = [];
	
	function __construct(){ $this->debug(true); }
	
	private function _normalizeData($assocArray){
		$data = [];
		foreach ($assocArray as $dataName => $dataVal) {
			$data[] = urlencode($dataName).'='.urlencode($dataVal);
		}
		
		return implode('&', $data);
	}
	
	private function _normalizeUrl($fullUrl, $urlParams){
		$url = [];
		if(!is_null($urlParams)){
			foreach ($urlParams as $urlParamName => $urlParamVal) {
				$url[] = urlencode($urlParamName).'='.urlencode($urlParamVal);
			}
		}
		
		if(count($url)){
			$fullUrl .= '?'. implode('&', $url);
		}
		
		return $fullUrl;
	}
	
	public function addCustomHeader($header){
		$this->customHeaders += $header;
	}
	
	public function setCustomHeader($headerName, $headerValue){
		$this->customHeaders[$headerName] = $headerValue;
	}
	
	public function removeCustomHeader($headerName){
		if(isset($this->customHeaders[$headerName])){
			unset($this->customHeaders[$headerName]);
		}
	}
	
	public function getCustomHeader($headerName){
		if(isset($this->customHeaders[$headerName])){
			return $this->customHeaders[$headerName];
		}

		return null;
	}
	
	public function getCustomHeadersNonAssoc(){
		$result = [];
		foreach($this->customHeaders as $headerName => $headerValue){
			$result[] = $headerName.': '.$headerValue;
		}
		return $result;
	}

	public function getCurlOptions(){
		return $this->curlOptions;
	}	
	
	public function getCurlOption($curlOptionNo){
		if(isset($this->curlOptions[$curlOptionNo])){
			return $this->curlOptions[$curlOptionNo];
		}
		
		return null;
	}
	
	public function setCurlOption($curlOptionNo, $curlOptionValue){
		$this->curlOptions[$curlOptionNo] = $curlOptionValue;
	}
	
	public function xmlHttpRequest(){
		$this->setCustomHeader('X-Requested-With', 'XMLHttpRequest');
	}
	
	public function setOrigin($origin){
		$this->setCustomHeader('Origin', $origin);
	}
	
	public function setAcceptLanguage($language){
		$this->setCustomHeader('Accept-Language', $language);
	}
	
	public function setReferer($referer){
		$this->setCustomHeader('Referer', $referer);
	}
	
	public function setContentType($type){
		$this->setCustomHeader('Content-Type', $type);
	}
	
	public function setUserAgent($agent){
		$this->userAgentString = $agent;
	}
	
	public function clearCookie(){
		self::$cookies = [];
	}
	
	public function setCookie($cookieName, $cookieValue, $isGlobal = true){
		if($isGlobal){
			self::$cookies[$cookieName] = $cookieValue;
		}else{
			$this->localCookies[$cookieName] = $cookieValue;
		}
	}
	
	public function getCookie($cookieName){
		if(isset($this->localCookies[$cookieName])){
			return $this->localCookies[$cookieName];
		}elseif(isset(self::$cookies[$cookieName])){
			return self::$cookies[$cookieName];
		}

		return null;
	}
	
	public function getCookies(){
		return (self::$cookies + $this->localCookies);
	} 
	
	public function getCookiesSemicoloned(){
		$implodeable = [];
		foreach($this->getCookies() as $key => $value){
			$implodeable[] = $key.'='.$value;
		}
		
		return implode(';', $implodeable);
	}

	public function head($fullUrl, $urlParams = null){
		return $this->doRequest($this->_normalizeUrl($fullUrl, $urlParams), [
			CURLOPT_NOBODY => 1
		]);
	}
	
	public function get($fullUrl, $urlParams = null){
		return $this->doRequest($this->_normalizeUrl($fullUrl, $urlParams), [
			CURLOPT_HTTPGET => 1
		]);
	}
	
	public function post($fullUrl, $postParams, $urlParams = null){
		$postParams = (is_array($postParams)) ? $this->_normalizeData($postParams) : $postParams;
		return $this->doRequest($this->_normalizeUrl($fullUrl, $urlParams), [
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $postParams
		]);
	}
		
	public function doRequest($url, $opt = []){
		if(is_null($this->getCustomHeader('Referer')) && !is_null(self::$referer)){
			$this->setReferer(self::$referer);
		}
		
		$defaultUserAgent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
		if(is_null($this->getCustomHeader('User-Agent'))){
			$this->setUserAgent($defaultUserAgent);
		} elseif(is_null($this->getCurlOptions(CURLOPT_USERAGENT))) {
			$this->setCurlOption(CURLOPT_USERAGENT, $defaultUserAgent);
		}
		
		$headersNonAssoc = $this->getCustomHeadersNonAssoc();
		if(count($headersNonAssoc)){
			$this->setCurlOption(CURLOPT_HTTPHEADER, $headersNonAssoc);
		}
		
		$cookies = trim($this->getCookiesSemicoloned());
		if(strlen($cookies)){
			$this->setCurlOption(CURLOPT_COOKIE, $cookies);
		}
		
		self::$referer = $url;	
		$opt += [
			CURLOPT_HEADER => true,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE => true
		] + $this->getCurlOptions();
										
		$curl = curl_init($url);
		curl_setopt_array($curl, $opt);
		$result = curl_exec($curl);
		
		if(is_bool($result)){
			throw new Exception('Curl Failed');
		}
		
		$response = new Response($curl, $result);
		$this->registerNewCookies($response);		
		curl_close($curl);		
		
		return $response;
	}
	
	/**
	* @todo: Detect destroyed cookies!
	*/		
	private function registerNewCookies($response){
		if(isset($response->headers['Set-Cookie'])){
			if(!is_array($response->headers['Set-Cookie'])){
				$response->headers['Set-Cookie'] = [$this->headers['Set-Cookie']];
			}
			
			foreach($response->headers['Set-Cookie'] as $name => $value){
				$parsedCookie = explode(';', $value, 2);
				$parsedCookie = explode('=', $parsedCookie[0], 2);
				$parsedCookieName = trim($parsedCookie[0]);
				$parsedCookieValue = trim($parsedCookie[1]);
				self::$cookies[$parsedCookieName] = $parsedCookieValue;
			}
		}
	}
	
	public function debug($isOn=true){
		$this->setCurlOption(CURLOPT_VERBOSE, $isOn);
	}

}

