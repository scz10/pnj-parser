<?php

define('PARSER_DEBUG', false);

class PNJParser {
    private $username;
    private $password;

    protected $curlHandle;
    public $_siteTargets = [
        'loginUrl' => 'https://old.pnj.ac.id/mahasiswa.html',
        'nilaiUrl' => 'https://old.pnj.ac.id/mahasiswa/nilai.html',
        'kompenUrl' => 'https://old.pnj.ac.id/mahasiswa/kompen.html'
    ];

    protected $isLoggedIn = false;

    public $_defaultHeaders = array(
        'POST /mahasiswa.html HTTP/1.1',
        'Host: old.pnj.ac.id',
        'Connection: keep-alive',
        'Cache-Control: no-cache',
        'Upgrade-Insecure-Requests: 1',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
        'Accept-Encoding: gzip, deflate, br',
        'Accept-Language: en-US,en;q=0.9,id;q=0.8'
    );

    public function __construct($username, $password)
	{
		if( PARSER_DEBUG == true ) error_reporting(E_ALL);
		$this->username = $username;
		$this->password = $password;
		$this->curlHandle = curl_init();
		$this->setupCurl();
		$this->login($this->username, $this->password);
    }

    public function exec()
	{
		$result = curl_exec($this->curlHandle);
		if( PARSER_DEBUG == true ) {
			$http_code = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
			print_r($result);

			if($http_code != 200) {
				echo 'Something went wrong, not return 200';
				exit;
			}

		}
		return $result;
    }
    
    protected function setupCurl()
	{
		curl_setopt( $this->curlHandle, CURLOPT_URL, $this->_siteTargets['loginUrl'] );
		curl_setopt( $this->curlHandle, CURLOPT_POST, 0 );
		curl_setopt( $this->curlHandle, CURLOPT_HTTPGET, 1 );
		curl_setopt( $this->curlHandle, CURLOPT_HTTPHEADER, $this->_defaultHeaders);
		curl_setopt( $this->curlHandle, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $this->curlHandle, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->curlHandle, CURLOPT_COOKIEFILE,'cookie' );
		curl_setopt( $this->curlHandle, CURLOPT_COOKIEJAR, 'cookiejar' );
    }
    
    protected function curlSetGet()
	{
		curl_setopt( $this->curlHandle, CURLOPT_POST, 0 );
		curl_setopt( $this->curlHandle, CURLOPT_HTTPGET, 1 );
    }
    
    protected function curlSetPost()
	{
		curl_setopt( $this->curlHandle, CURLOPT_POST, 1 );
		curl_setopt( $this->curlHandle, CURLOPT_HTTPGET, 0 );
    }
    
    private function login($username, $password){
        //Just to Get Cookies
		curl_setopt( $this->curlHandle, CURLOPT_URL, $this->_siteTargets['loginUrl'] );
		$this->curlSetGet();
        $this->exec();
        
        //Sending login Info
        $params = array(
			"username={$username}",
			"password={$password}",
			'submit=Login'
        );
        $params = implode( '&', $params );
        $this->curlSetPost();
        curl_setopt( $this->curlHandle, CURLOPT_URL, $this->_siteTargets['loginUrl'] );
		curl_setopt( $this->curlHandle, CURLOPT_REFERER, $this->_siteTargets['loginUrl'] );
        curl_setopt( $this->curlHandle, CURLOPT_POSTFIELDS, $params );
        $this->exec();

        curl_setopt( $this->curlHandle, CURLOPT_URL, $this->_siteTargets['loginUrl'] );
		$this->curlSetGet();
        $html = $this->exec();
        echo $html;
		$this->isLoggedIn = true;
    }

    public function getNilai(){
        if( !$this->isLoggedIn ) $this->login( $this->username, $this->password );

        $this->curlSetGet();
        curl_setopt( $this->curlHandle, CURLOPT_URL, $this->_siteTargets['nilaiUrl'] );
        curl_setopt( $this->curlHandle, CURLOPT_REFERER, $this->_siteTargets['loginUrl'] );
        
        $html = $this->exec();
        return $this->getNilaiTable($html);

    }

    private function getNilaiTable($html){
        $dom = new DOMDocument();
        if ( PARSER_DEBUG ) {
			$dom->loadHTML($html);	
		} else {
			@$dom->loadHTML($html);	
        }
        
        $table = $dom->getElementsByTagName('tbody')->item(0);
        return $dom->saveHTML($table);
        
    }

    
}
