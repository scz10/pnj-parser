<?php

define('PARSER_DEBUG', false);

class PNJParser
{
    private $username;
    private $password;

    protected $curlHandle;
    public $_siteTargets = [
        'loginUrl' => 'https://old.pnj.ac.id/mahasiswa.html',
        'nilaiUrl' => 'https://old.pnj.ac.id/mahasiswa/nilai.html',
        'kompenUrl' => 'https://old.pnj.ac.id/mahasiswa/kompen.html',
        'logoutUrl' => 'https://old.pnj.ac.id/mahasiswa/logout.html'
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
        if (PARSER_DEBUG == true) error_reporting(E_ALL);
        $this->username = $username;
        $this->password = $password;
        $this->curlHandle = curl_init();
        $this->setupCurl();
        $this->login($this->username, $this->password);
    }

    public function exec()
    {
        $result = curl_exec($this->curlHandle);
        if (PARSER_DEBUG == true) {
            $http_code = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
            print_r($result);

            if ($http_code != 200) {
                echo 'Something went wrong, not return 200';
                exit;
            }
        }
        return $result;
    }

    protected function setupCurl()
    {
        curl_setopt($this->curlHandle, CURLOPT_URL, $this->_siteTargets['loginUrl']);
        curl_setopt($this->curlHandle, CURLOPT_POST, 0);
        curl_setopt($this->curlHandle, CURLOPT_HTTPGET, 1);
        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $this->_defaultHeaders);
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandle, CURLOPT_COOKIEFILE, 'cookie');
        curl_setopt($this->curlHandle, CURLOPT_COOKIEJAR, 'cookiejar');
    }

    protected function curlSetGet()
    {
        curl_setopt($this->curlHandle, CURLOPT_POST, 0);
        curl_setopt($this->curlHandle, CURLOPT_HTTPGET, 1);
    }

    protected function curlSetPost()
    {
        curl_setopt($this->curlHandle, CURLOPT_POST, 1);
        curl_setopt($this->curlHandle, CURLOPT_HTTPGET, 0);
    }

    private function login($username, $password)
    {
        //Just to Get Cookies
        curl_setopt($this->curlHandle, CURLOPT_URL, $this->_siteTargets['loginUrl']);
        $this->curlSetGet();
        $this->exec();

        //Sending login Info
        $params = array(
            "username={$username}",
            "password={$password}",
            'submit=Login'
        );
        $params = implode('&', $params);
        $this->curlSetPost();
        curl_setopt($this->curlHandle, CURLOPT_URL, $this->_siteTargets['loginUrl']);
        curl_setopt($this->curlHandle, CURLOPT_REFERER, $this->_siteTargets['loginUrl']);
        curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $params);
        $this->exec();


        $this->isLoggedIn = true;
    }

    public function getDataMahasiswa()
    {
        if (!$this->isLoggedIn) $this->login($this->username, $this->password);

        curl_setopt($this->curlHandle, CURLOPT_URL, $this->_siteTargets['loginUrl']);

        $this->curlSetGet();

        $result = $this->exec();
        $result = $this->getParseDataMahasiswa($result);

        return $result;
    }

    private function getParseDataMahasiswa($html)
    {
        $dom = new DOMDocument();

        if (PARSER_DEBUG) {
            $dom->loadHTML($html);
        } else {
            @$dom->loadHTML($html);
        }

        $xpath = new DOMXPath($dom);

        $data = [];
        for ($i = 1; $i <= 10; $i++) {
            array_push($data, $xpath->query('//*[@id="artikel_tengah"]/div[2]/text()[' . $i . ']')->item(0)->nodeValue);
        }

        return $data;
    }

    public function getKompenMahasiswa()
    {
        if (!$this->isLoggedIn) $this->login($this->username, $this->password);

        curl_setopt($this->curlHandle, CURLOPT_URL, $this->_siteTargets['kompenUrl']);

        $this->curlSetGet();

        $result = $this->exec();
        $result = $this->getParseKompenMahasiswa($result);

        return $result;
    }

    private function getParseKompenMahasiswa($html)
    {
        $dom = new DOMDocument();

        if (PARSER_DEBUG) {
            $dom->loadHTML($html);
        } else {
            @$dom->loadHTML($html);
        }

        $xpath = new DOMXPath($dom);

        $data = [];
        for ($i = 3; $i <= 6; $i++) {
            array_push($data, $xpath->query('//*[@id="artikel_tengah"]/div[2]/table/tbody/tr/td[' . $i . ']')->item(0)->nodeValue);
        }

        return $data;
    }

    public function getNilaiMahasiswa()
    {
        if (!$this->isLoggedIn) $this->login($this->username, $this->password);

        curl_setopt($this->curlHandle, CURLOPT_URL, $this->_siteTargets['nilaiUrl']);

        $this->curlSetGet();

        $result = $this->exec();
        $result = $this->getParseNilaiMahasiswa($result);
        $result = $this->splitIntoSemester($result);

        return $result;
    }

    private function getParseNilaiMahasiswa($html)
    {
        $dom = new DOMDocument();

        if (PARSER_DEBUG) {
            $dom->loadHTML($html);
        } else {
            @$dom->loadHTML($html);
        }

        $xpath = new DOMXPath($dom);

        $rows = $xpath->query('//*[@id="artikel_tengah"]/div[2]/table/tbody/tr');
        $nilai = [];
        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            $cellData = [];
            foreach ($cells as $cell) {
                $cellData[] = $cell->nodeValue;
            }
            array_push($nilai, $cellData); 
        }
        array_multisort(array_column($nilai, 2), SORT_ASC, array_column($nilai, 3), SORT_ASC, $nilai);

        return $nilai;
    }

    private function splitIntoSemester($data)
    {
        $semesters = [];
        $temp = [];
        $count = 1;
        $length = count($data) + 1;
        $previousValue = null;

        foreach ($data as $x) {
            if ($previousValue) {
                if ($x[3] != $previousValue[3]) {
                    $count++;
                    array_push($semesters, $temp);
                    $temp = [];
                    array_push($temp, $x);
                    $previousValue = $x;
                } else {
                    $count++;
                    array_push($temp, $x);
                    $previousValue = $x;
                    if ($count == $length) {
                        array_push($semesters, $temp);
                    }
                }
            } else {
                $count++;
                array_push($temp, $x);
                $previousValue = $x;
            }
        }
        return $semesters;
    }

    public function getIPMahasiswa(){
        $nilai = $this->getNilaiMahasiswa();
        $ip_mahasiswa = [];
        foreach($nilai as $x){
            $nxk = 0;
            $kredit = 0;
            foreach($x as $y){
                $nxk += $y[6];
                $kredit += $y[5];
            }
        array_push($ip_mahasiswa, [$nxk, $kredit, number_format((float)$nxk/$kredit,2,'.','')]);
        }
        return $ip_mahasiswa;
    }

    public function getIPKMahasiswa(){
        $nilai[] = $this->getIPMahasiswa();
        $nxk = 0;
        $kredit = 0;
        foreach($nilai as $x){
            foreach($x as $y){
                $nxk += $y[0];
                $kredit += $y[1];
            }
        }
        return [$nxk, $kredit,number_format((float)$nxk/$kredit,2,'.','')];
    }
    public function getLogout()
    {
        curl_setopt($this->curlHandle, CURLOPT_URL, $this->_siteTargets['logoutUrl']);

        $this->curlSetGet();

        $this->exec();
    }
}
