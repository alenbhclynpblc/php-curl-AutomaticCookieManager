class Response{
	
	public $status;
	public $content;
	public $headers;
	
	function __construct($curlObject, $curlExecResult){
		$resultInfo = curl_getinfo($curlObject);
		$this->status = $resultInfo["http_code"];		
		$this->parseCurlResult($curlExecResult);
	}
	
	private function parseCurlResult($result){
		list($headers, $content) = explode("\r\n\r\n", $result, 2);
		$headers = $this->parseHeaders($headers);
		$firstLineOfHeaders = array_shift($headers);
		$this->content = $content;
		$this->headers = $headers;
	}
	
	private function parseHeaders($headersAsString){
		$headersAsString = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $headersAsString);	
		$headersAsLined = explode("\n", $headersAsString);
		$headers = [];
		foreach ($headersAsLined as $header) {
			$headerExploded = explode(':', $header, 2);
			if(count($headerExploded) < 2){
				continue;
			}
			
			$key = $headerExploded[0];
			$value = $headerExploded[1];
			
			if(isset($headers[$key])){
				if(!is_array($headers[$key])){
					$headers[$key] = [$headers[$key]];
				}
				
				$headers[$key][] = $value;	
			}else{
				$headers[$key] = $value; 
			}
		}
		
		return $headers;
	}
	
	public function getHeaders(){
		return $this->header;
	}
	
	public function getContent(){
		return $this->content;		
	}
}

