# pnj-parser
Parsing data dari portal mahasiswa PNJ

## How to use
### ***Login***
```php
//Use NIM and Password your Student account

require_once('src/PNJParser.php');
$Parser = new PNJParser('NIM', 'password');
```
### *** Get biodata data ***

```php
//Use NIM and Password your Student account

require_once('src/PNJParser.php');
$Parser = new PNJParser('nim', 'password');
$biodata = $Parser->getDataMahasiswa();
echo json_encode($biodata);
```
result
```json
0: "NAMA"
1: "JALUR MASUK"
2: "STATUS MAHASISWA"
3: "NIM"
4: "JURUSAN / PROOGRAM STUDI"
5: "REGULAR / KERJASAMA"
6: "AGAMA"
7: "JENIS KELAMIN"
8: "TEMPAT, TANGGAL LAHIR"
9: "ALAMAT"
```
### *** Get kompen data ***
```php
//Use NIM and Password your Student account

require_once('src/PNJParser.php');
$Parser = new PNJParser('NIM', 'password');
$kompen = $Parser->getKompenMahasiswa();
echo json_encode($kompen);
```
result
```json
0: "0" // JUMLAH IZIN 
1: "0" // JUMLAH SAKIT
2: "0" // JUMLAH ALPHA 
3: "0" // JUMLAH TERLAMBAT
```

### *** Get nilai data student ***
```php
//Use NIM and Password your Student account

require_once('src/PNJParser.php');
$Parser = new PNJParser('nim', 'password');
$nilai= $Parser->getNilaiMahasiswa();
echo json_encode($nilai);
```
result
```json
2019/2020-Ganjil: [["35", "Cryptography 1 (Symetrics)", "2019/2020", "Ganjil", "A", "2", "8"],…]
0: ["35", "Cryptography 1 (Symetrics)", "2019/2020", "Ganjil", "A", "2", "8"]
1: ["36", "Digital Forensics", "2019/2020", "Ganjil", "A", "2", "8"]
2019/2020-Genap: [["43", "Cryptography 2 (Asymetrics)", "2019/2020", "Genap", "A", "2", "8"],…]
0: ["43", "Cryptography 2 (Asymetrics)", "2019/2020", "Genap", "A", "2", "8"]
1: ["44", "Distributed Systems", "2019/2020", "Genap", "A", "2", "8"]
```

### *** Get IP data student ***
```php
//Use NIM and Password your Student account

require_once('src/PNJParser.php');
$Parser = new PNJParser('nim', 'password');
$ip = $Parser->getIPMahasiswa();
echo json_encode($ip);
```
result
```json
0: "3.78"
1: "3.97"
```

### *** Get IPK data student ***
```php
//Use NIM and Password your Student account

require_once('src/PNJParser.php');
$Parser = new PNJParser('nim', 'password');
$ipk = $Parser->getIPKMahasiswa();
echo json_encode($ipk);
```
result
```json
0: "451.2" // score total
1: 122 // credit total 
2: "3.70" // ipk 
```

### *** Log out from PNJ academic portal ***
```php
$Parser->getLogout();
```
