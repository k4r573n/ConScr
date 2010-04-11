<?PHP
/* formmail.php  v0.1 */


// recives the data:
//  - analyses them
//  - saves them
//
// mails dump back


/*
 * published under the GPL Licence
 *
 * (c) Mar 2010
 *     by Karsten Hinz
 */

require_once "./config.php";
require_once "./formmail.lib.php";
require_once './Template.php';

//testing
extract_csv(0);

$daten_org = recive_formular();
$daten_no_html = $daten_org;//ka ob das nur die addresse rüber kopiert ist hier aber auch egal

//löscht die zeilenumbrüche
clean_array($daten_no_html,0);
//ersetzt alle sonderzeichen durch html
clean_array($daten_org,1);

$stat = statistics($daten_org,$preise);

if (!empty($daten_org["bemerkung"])) {
  sends_info($daten_org,$stat);
}

//die nicht escapte version, damit man die datei einfacher wo anders importieren kann
save_data($daten_no_html);

//erzeugt eine Rechnung aus einen Template
$rechnung = generate_bill($daten_org,$preise);

$fehler = generate_mail($daten_org,$rechnung);

//und auch noch was anzeigen
print_page($daten_org,$rechnung,$fehler);

?>
