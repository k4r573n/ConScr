<?php
/* hier sind die Konfigurationsvariablen zu finden
 */

//das array kann auch vergrößert werden - die neuen Felder müssten auch gespeichert werden
$anmeldung_fields = array("idx" , "name" , "mail" , "newsletter" , "ba_count" , "ki_count" , "ew_count" , "timestamp" , "bemerkung" );

//die Preise für babys kinder und erwachsene - oder einfach 3 verschiedene
//Preisgruppen der betrag ist in Euro einzugeben z.B.
$preise = array("ba" => "0","ki" => "18", "ew" => "23");

//Trennzeichen für die daten.php datei
$splitter="¶";


define('EVENT','CHANGE: der Event name');
define('SUBJECT','CHANGE: Betreff der generierten Mails');
define('FROM','CHANGE: hier muss der Absender für die generierten mails rein');
define('ORGA_MAIL','CHANGE: die mail Adresse an die die info mails gehen');
define('ORGA_SUBJECT','CHANGE: der Betreff der info mail (es wird automatisch der name des anmelders angeängt)');
define('MSG_NUMBER_UNREAL','Die Anzahl ist unrealistisch!');
//Fehler ausgaben - werden aber bisher nirgends verwendet
define('MSG_PLZ_UNREAL','Die Postleitzahl darf keine buchstaben enthalten oder leer sein!');
define('MSG_SAVING_ERROR','Fehler! Die Angaben konnten nicht gespeichert werden!');
define('MSG_SAVING_OK','Die Angaben wurden erfolgreich gespeichert!');
?>
