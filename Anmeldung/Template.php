<?php
/* Created on 27.10.2005 / 05.01.2006

kopiert von http://www.peuss.com/PHP/Template/tutorial_1.php
*/
/**
* Aufgaben:
*<pre>
* - besorgt die Vorlage-Seite
* - bindet Content ein
* - liefert fertige Webseite
*</pre>
* @version "aktuellste Version, PHP 4"
* @package Vorlage, Template
*
*/
class Template {

/**
* @var String enthält die WebSite, zuerst mit Variablen, später in endgültiger Version
* @access public
***/
var $vorlage;
 
/**
* @param String $vorlage URL zur Vorlage-Datei oder die Vorlage als String
* @access public
*/
function Template($vorlage) {
$this->setVorlage($vorlage);
}
 
/**
* Entscheidet, ob der übergebene String ein URL ist und bindet die richtige Vorlage ein.
* @param String $vorlage URL zur Vorlage-Datei oder die Vorlage als String
* @access private
*/
function setVorlage($vorlage) {
if(file_exists($vorlage)) {
$fp = fopen($vorlage, "r");
$text = fread($fp, filesize($vorlage));
fclose($fp);
$this->vorlage = $text;
} else {
$this->vorlage = $vorlage;
}
}
 
/**
* Mögliche Aufrufe:
* <pre>
* - String/String => suchWort, substitution
* - Array/ - => Array(suchWort => substitution)
* - String/Array => SchleifenName/2D-Array
* </pre>
* @internal Überladung simulieren: diese Methode entscheidet anhand der Parameter, an welche private Methode delegiert wird
* @param String_oder_Array param1
* @param String_oder_Array param2
* @access public
*/
function setContent($param1, $param2="") {
if(!is_array($param1) && $param2 && !is_array($param2)) {
$this->setOne($param1, $param2);
} elseif (is_array($param1) && !$param2){
$this->setArray($param1);
} elseif (!is_array($param1) && is_array($param2)) {
$this->setLoop($param1, $param2);
} else {
die("Parameter in der Klasse Vorlage wurden falsch übergeben.");
}
}
 
/**
* AufrufBeispiel:
* <pre>
* $vorlage->setOne("TITEL", "Titel der WebSite");
* </pre>
* @param String suchWort "Der String, der ersetzt wird"
* @param String substitution "Der String, der eingebunden wird"
* @access private
*/
function setOne($suchWort, $substitution) {
$this->vorlage = str_replace("{".$suchWort."}",
$substitution,
$this->vorlage);
}
 
/**
* AufrufBeispiel:
* <pre>
* $vorlage->setArray(array("MELDUNG" => $meldung,
* "NAME" => $_POST['name'],
* "EMAIL" => $_POST['eMail'],
* "TEXT" => $_POST['text'],
* "KOPIE" => $_POST['kopie']));
* </pre>
* @param mixed $Array enthält Variable/Substitution-Paare
* @access private
*/
function setArray($Array) {
foreach ($Array as $suchWort => $substitution) {
$this->setOne($suchWort, $substitution);
}
}
 
/**
* Aufrufbeispiel:
* <pre>
* $vorlage->setLoop($nameDerSchleife, array(array("var1" => "konst1",
* "var2" => "konst2"),
* array("var1" => "konst3",
* "var2" => "konst4")));
* </pre>
* @param String $schleife Bezeichnung der Schleife
* @param mixed $Array Array von assoziativen Arrays, die jeweils die Schlüssel/Werte enthalten (siehe Aufrufbeispiel)
* @access private
*/
function setLoop($schleife, $Array) {
$str = explode("<!--anfang:".$schleife."!-->",
str_replace("<!--ende:".$schleife."!-->",
"<!--anfang:".$schleife."!-->",
$this->vorlage));
$teilStr = "";
foreach ($Array as $element) {
$teilVorlage = new Template($str[1]);
$teilVorlage->setArray($element);
$teilStr .= $teilVorlage->vorlage;
}
$this->vorlage = $str[0] . $teilStr . $str[2];
}
}
?>
