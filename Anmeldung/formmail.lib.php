<?php
//Globale Fehler liste
$error_msgs = array();
$error=0; //um bei falsch angaben abzubrechen

function recive_formular() {
	//um einwenig weniger tipp arbeit zu haben und zukünftige erweiterungen schneller einfließen zu lassen
	//werden einfach nur alle tabellen felder durch gegangen und ggf berichtigt
	//konfiguration über config.php: $anmeldung_fields

	/*liest die bei der Anmeldung abgeschickten Daten aus, prüft sie und packt sie in einen Hash*/

	$daten=array();
	foreach ($GLOBALS["anmeldung_fields"]  AS $key) {
		switch($key) {
		case 'idx':
			$daten[$key]=get_id();
			break;
		case 'ew_count':
			if ((!(is_numeric($_POST[$key])))or($_POST[$key]>99)) {
				array_push($GLOBALS["error_msgs"],MSG_NUMBER_UNREAL);
				$GLOBALS["error"] += 1;
			}
			$daten[$key]=abs($_POST[$key]);
			break;
		case 'ki_count':
			if ((!(is_numeric($_POST[$key])))or($_POST[$key])>99) {
				array_push($GLOBALS["error_msgs"],MSG_NUMBER_UNREAL);
				$GLOBALS["error"] += 1;
			}
			$daten[$key]=abs($_POST[$key]);
			break;
		case 'ba_count':
			if ((!(is_numeric($_POST[$key])))or($_POST[$key]>99)) {
				array_push($GLOBALS["error_msgs"],MSG_NUMBER_UNREAL);
				$GLOBALS["error"] += 1;
			}
			$daten[$key]=abs($_POST[$key]);
			break;
		case 'newsletter':
			if (isset($_POST[$key])) {
				$daten[$key]="1";
			}else{	$daten[$key]="0";}
			break;
		case 'timestamp':
			$daten[$key]=date("d.m.Y \u\m H:i:s");
			break;
		default:
			if (isset($_POST[$key])) {
				$daten[$key]=$_POST[$key];
			}else{
				$daten[$key]="";
			}
    }
  }

  //gibt die Formular daten zurück
  return $daten;
}


/** generiert eine Art Rechnung
  * für die mail und die Seiten Ausgabe
	*/
function generate_bill($daten,$preise) {
		//der text wird mit Hilfe eines Template erstellt
		$tmp = new Template('./rechnung.htm');
		//name einfügen
		$tmp->setContent('NAME', $daten["name"]." ");

    //personen anzahl
		$tmp->setContent('EW_COUNT',($daten['ew_count']." "));
		$tmp->setContent('KI_COUNT', ($daten["ki_count"]." "));
		$tmp->setContent('BA_COUNT', $daten["ba_count"]." ");

    //preise
		$tmp->setContent('EW_PREIS', $preise["ew"]." ");
		$tmp->setContent('KI_PREIS', $preise["ki"]." ");
		$tmp->setContent('BA_PREIS', $preise["ba"]." ");

		//gesamt pro pers gruppe
		$tmp->setContent('EW_GES_PREIS', ($daten["ew_count"] * $preise["ew"])." ");
		$tmp->setContent('KI_GES_PREIS', ($daten["ki_count"] * $preise["ki"])." ");
		$tmp->setContent('BA_GES_PREIS', ($daten["ba_count"] * $preise["ba"])." ");

    //gesamt preis
		$tmp->setContent('GES_PREIS', ($daten["ba_count"] * $preise["ba"]
		       + $daten["ki_count"] * $preise["ki"] + $daten["ew_count"] * $preise["ew"])." ");

		//Timestamp
		$tmp->setContent('TIMESTAMP', $daten["timestamp"]);

    //ID für überweisung
		$tmp->setContent('ID', $daten["idx"]." ");
		//Event name
		$tmp->setContent('EVENT', EVENT." ");

    //und nun das ausgefüllte Template zurück geben:
     return $tmp->vorlage;
}

/** gibt eine übersicht mit den daten die auch in der mail enthalten
	* sind zurück
	*/
function print_page($daten,$rechnung,$fehler) {
		//der text wird mit Hilfe eines Template erstellt
		$tmp = new Template('./return.htm');
		//name einfügen
		$tmp->setContent('NAME', $daten["name"]." ");
		//email einfügen
		$tmp->setContent('EMAIL', $daten["email"]." ");

    //gibt die vorgefertigte Rechnung aus
		$tmp->setContent('RECHNUNG', $rechnung." ");

		//ggf Fehler ausgeben
		$tmp->setContent('FEHLER',$fehler." "); 
		//Event name
		$tmp->setContent('EVENT', EVENT." ");

    //und auch den inhalt der mail noch mal anzeigen:
     echo $tmp->vorlage;
}


/** erhöht die anzahl der vorangemeldeten besucher um die der neuen Anmeldung
	*/
function statistics($daten,$preise) {
	//öffnet im lese modos

  $inhalt = array(0,0,0,0);//leeren - wird genommen wenn datei nicht existiert
	$b = 1;
	$file = fopen("statistics.txt","a+");
	if ($b) {
	  fseek($file,0);//zeiger auf anfang setzten
		$inhalt = explode(';',trim(fread($file, 1000))); //packt diese zeile in einen array
		fclose ($file);
	}
  
  $inhalt[0]=$inhalt[0] + $daten["ew_count"];//Anzahl der Erwachsenen
  $inhalt[1]=$inhalt[1] + $daten["ki_count"];//Anzahl der Kinder
  $inhalt[2]=$inhalt[2] + $daten["ba_count"];//Anzahl der Kleinkinder
  $inhalt[3]=$inhalt[3] + $daten["ba_count"] + $daten["ki_count"] + $daten["ew_count"];//gesamt Anzahl der teilnehmenden Personen
  $inhalt[4]=$inhalt[4] + ($daten["ew_count"]*$preise["ew"])
                	      + ($daten["ki_count"]*$preise["ki"])
	                      + ($daten["ba_count"]*$preise["ba"]);//Anzahl des gesamt erwirtschaften Geldes

	$file = fopen("statistics.txt","w");
	$entry = implode(';', $inhalt);
	fwrite ($file, $entry);
	fclose ($file);

  //gibt alle werte zurück
	return $inhalt;
}

/** ließt die bis herigen anmeldungnen aus einer text file
	* und setzt erhöht die zahl um 1 überspeichert sie
	*/
function get_id() {
	//öffnet im lese modos

	$b = 1;
	$file = fopen("id.txt","a+");
	fseek($file,0);//zeiger auf anfang setzten
	if ($b) {
		$id = trim(fread($file, 100));
		fclose ($file);
	}

	if (empty($id)) {$id = 100;}
	$id++;

	$file = fopen("id.txt","w");
	fwrite ($file, $id);
	fclose ($file);
	return $id;
}


/** speichert die daten in einer Text file
  * ein Datensatz pro zeile
  */
function save_data($daten) {
	//daten zusammenführen
	$entry = implode($GLOBALS["splitter"], $daten);
	$head="";

  if (!file_exists("daten.php")) {
    //datei Inhalt erzeugen wenn datei noch nicht existiert
    $head = "<?php exit(); /*damit der Inhalt nicht angezeigt wird*/ ?>\n";
  	foreach ($GLOBALS["anmeldung_fields"]  AS $key) {
      $head = $head.$key.$GLOBALS["splitter"];
    }
    //das letzte Trennzeichen wieder entfernen und einen Zeilenumbruch anfügen:
    $head = substr($head,0,-strlen($GLOBALS["splitter"]))."\n";
  }

  //save daten
	$file = fopen("daten.php","a");
	fwrite($file, $head.$entry."\n");//hinten anhängen
	fclose ($file);
}


/** sendet eine mail an den Veranstalter, wenn eine
	* Anmeldung mit Bemerkung kommt
	*
	* $stat ist ein array mit der mini Statistik
	* (ges_ew_count, ges_ki_count , ges_ba_count, ges_person_count, ges_preis_count)
  */
function sends_info($daten,$stat) {
  $empfaenger = ORGA_MAIL;
  $from = $daten["mail"];
  $betreff = ORGA_SUBJECT."(".$daten["name"].")";
  $tmp = new Template('./info_mail.htm');

	//name einfügen
	$tmp->setContent('NAME', $daten["name"]." ");

  //personen anzahl
	$tmp->setContent('EW_COUNT', $daten["ew_count"]." ");
	$tmp->setContent('KI_COUNT', $daten["ki_count"]." ");
	$tmp->setContent('BA_COUNT', $daten["ba_count"]." ");

	//Bemerkungen
	$tmp->setContent('BEMERKUNG', $daten["bemerkung"]." ");
	//Timestamp
	$tmp->setContent('TIMESTAMP', $daten["timestamp"]." ");
	//ID
	$tmp->setContent('ID', $daten["idx"]." ");
	//Event name
	$tmp->setContent('EVENT', EVENT." ");

  //gesamt eingenommenes geld und gesamt zahl der leute
	$tmp->setContent('PERS_COUNT', $stat[3]." ");
	$tmp->setContent('GELD_COUNT', $stat[4]." ");

  $text = $tmp->vorlage;

	mail($empfaenger, $betreff, $text, "From: ".$from.
					"\nContent-Type: text/html; charset=utf8")
					or ( $t=0);
}

/** erzeugt die Email aus den Formular-daten
  * und deren Auswertung
  *
  * $rechung enthält eine durchs Template bestimmte Form
  */
function generate_mail($daten,$rechnung) {
	$adresse = $daten["mail"];
	$t = 0;
	if ($adresse) {
		$betreff = SUBJECT;
		$from = FROM;
		$empfaenger = $adresse;

		//der text wird mit Hilfe eines Template erstellt
		$tmp = new Template('./mail.htm');
		//name einfügen

		$tmp->setContent('NAME', $daten["name"]." ");

    //Rechnung
		$tmp->setContent('RECHNUNG', $rechnung." ");

		//Timestamp
		$tmp->setContent('TIMESTAMP', $daten["timestamp"]." ");

		//Event name
		$tmp->setContent('EVENT', EVENT." ");

    //den mail text um speichern:
    $text = $tmp->vorlage;

		$t = 1;
		mail($empfaenger, $betreff, $text, "From: ".$from.
					"\nContent-Type: text/html; charset=utf8")
					or ($t = '');
	}


	if ($t == 1)  {
		return 'Die Bestätigungsmail wurde erfolgreich versendet<br>';
	}else{
		return 'Es ist ein Fehler beim senden der Anmeldungs-Bestätigungs-Mail aufgetrehten<br>';
	}

}


/** erstellt eine csv Datei und schickt den link darauf an den Orga
  *
  */
function extract_csv($save) {
//**UNTESTET**//
////////////////

	//öffnet im lese modos
  $inhalt = array();
  $file = fopen("daten.php","r");
  //erste Zeile mit php code entfernen
  $ignore = fgets($file,1000);
  //keys einlesen
  $keys = explode($GLOBALS["splitter"], trim(fgets($file,1000)));

  //daten einlesen
  $i = 0;
  while (!feof($file)) {
	  $zeile = explode($GLOBALS["splitter"], trim(fgets($file,1000)));
    foreach($keys as $key => $value) {
      //weißt den wert den in der ersten zeile def. key zu (damit die spalten
      //auch vertauscht werden könnten
      $inhalt[$i][$value]=$zeile[$key];  
    }
	  $i++;
  }
	fclose ($file);

  //und als csv file generieren
  $csv = implode($GLOBALS["splitter"], $keys)."\n";//kopf mit den keys
  //daten
  foreach($inhalt as $key => $row) {
    if (($inhalt[$key]["ki_count"]==0)&&($inhalt[$key]["ba_count"]==0)&&($inhalt[$key]["ew_count"]==0)) 
      { continue; }
    //sonst speichern
//    clean_array_from_comma($row); //nur nötig wenn ein Komma als seperator genutzt wird
	  $csv.= implode($GLOBALS["splitter"], $row)."\n";
	}

  if ($save==1) { 
    //wenn auch auf dem server gespeichert werden soll (ggf unsicher)
  	$file = fopen("daten.csv","w");
	  fwrite ($file, $csv);
	  fclose ($file);
	}

  //als mail versenden
  $empfaenger = ORGA_MAIL;
  $from = FROM;
  $betreff = EVENT." ( Daten )";
  $text = $csv;

	mail($empfaenger, $betreff, $text, "From: ".$from.
					"\nContent-Type: text; charset=utf8");
}

/** löscht alle ungewöhnlichen Zeichen aus der
	* eingabe (damit das speichern klappt)
	* wenn $html = true ist, werden alle sonderzeichen in html verwandelt
	* (damit es nicht zu einer injection kommt)
	*/ 
function clean_array(&$string,$html) {
	if(is_string($string)) {
    if ($html != 1) {
      /*Eingabe behandlung zum speichern*/

  	  //dieses ersetzten ist zwar sicher aber es gibt da
  	  //z.T, probleme mit üäöß oder ähnlichen zeichen
//  		$string = preg_replace('/[^a-zA-Z0-9\-\._:üÜäÄöÖß@?\/!\\() ]/', '_', $string);

		  // for end of line (or begining)
		  $string = trim($string);

		  // from everywhere
		  $string = str_replace("\n", " ", $string);
		  $string = str_replace("\r", "", $string);
		  //und das Trennerzeichen der dataen.php aus der eingabe entfernen
      $string = str_replace($GLOBALS["splitter"], " ", $string);
//		  $string = str_replace("<", "", $string);
//		  $string = str_replace(">", "", $string);

		}else{
		  //umwandeln in html sonderzeichen,
		  //sodass evlt vorhandener code nichts bringt
		  $string = htmlentities($string);
//		  $string = htmlspecialchars($string,ENT_QUOTES);
		}

	}else{
		if(is_array($string)) {
			foreach($string AS $key => $value) {
				clean_array($string[$key],$html);
			}
		}
	}
}

//für csv file
function clean_array_from_comma(&$string) {
	if(is_string($string)) {
		$string = str_replace(",", ".", $string);
	}else{
		if(is_array($string)) {
			foreach($string AS $key => $value) {
				clean_array_from_comma($string[$key]);
			}
		}
	}
}
?>
