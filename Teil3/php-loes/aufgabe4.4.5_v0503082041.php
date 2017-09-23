<html><head>
<style type="text/css"> 
A 					{ font-size: 11pt; text-decoration: none; } 
A:Hover, A:Active 	{ font-size: 12pt; text-decoration: underline; color: black; }
</style>
<script language="JavaScript">
var backup_text, text_is_sql = true;
function show_text_in_form(t , s ){	
	set_backup();
	document.forms[0].elements['sql'].value = t; 
	text_is_sql = false;
	window.status = s;
	//alert(s);
}
function load_backup(){ 
	if(!text_is_sql && backup_text){
		document.forms[0].elements['sql'].value = backup_text; 
		text_is_sql = true;
	}
	//alert('load_backup'); 
}
function set_backup(){	
	if(text_is_sql) {
		with (document.forms[0].elements['sql'] ) {
			if( value ){ backup_text = value; text_is_sql = true; }
		}
	}
	//alert('set_backup'); 
}
function on_load(){	set_backup(); }
</script>
</head><body onload="on_load()">
<?php
# htmlentities - auswahl anzeigen
#for( $i = 1 ; $i < 999 ; $i++ ) {	echo "<font color='#b0b0b0'>&#38&#35<!-- -->$i</font> = &#$i\n"; }

#http://localhost/privat/sebastian_privat/begin_19aug04/protected/Projekte/Studium/dpprak_logbuch/Teil3/PHP-Loesung/aufgabe4.4.5_v0503082041.php
# Nun die komischen Slashes entfernen die beim versenden von Formularen oft automatsich erzeugt werden. Grrr.
# machen wir immer automatisch sofort, sobald nötig.
# echo "<pre>";print_r( $HTTP_POST_VARS );
if ( get_magic_quotes_gpc() ) {
  // Really EGPCSR - Environment $_ENV, GET $_GET , POST $_POST, Cookie $_COOKIE, Server $_SERVER
  // and their HTTP_*_VARS cousins (separate arrays, not references) and $_REQUEST
   $fnStripMagicQuotes = create_function('&$mData, $fnSelf',
       'if (is_array($mData)) foreach ($mData as $mKey=>$mValue) $fnSelf($mData[$mKey], $fnSelf); '.
       'else $mData = stripslashes($mData);');
	//$fnStripMagicQuotes($_POST,$fnStripMagicQuotes);
	// do each set of EGPCSR as you find necessary
	#$fnStripMagicQuotes( $HTTP_POST_VARS , $fnStripMagicQuotes );
	#$fnStripMagicQuotes( $HTTP_GET_VARS , $fnStripMagicQuotes );
	$fnStripMagicQuotes( $sql , $fnStripMagicQuotes );
	// do each set of EGPCSR as you find necessary
}



if(!$table_name)$table_name = 'BERG';
$table_name = strtolower($table_name);
$default_sql = "SELECT * FROM $table_name ORDER BY B_ID" ;
?>
<font size="-1">Aufgabe 4.4.5 Eine kleine Applikation</font> - 
<?php
# $sql formatieren, damit es lesbarer ist.
#gelb($fromline.'»'.__LINE__,__FILE__, $sql, 'b' );
if( !$aufgabennr )pretty_sql($sql);
#gelb($fromline.'»'.__LINE__,__FILE__, $sql, 'b' );

#$hDB = odbc_connect('datenquelle','sasaus42','milchshake');
if( !$hDB = odbc_connect('datenquelle','','') ){
	# odbc_connect - Liefert eine ODBC-Verbindungskennung connection_id oder 0 (FALSE) bei Fehlfunktion zurück.
	echo odbc_errormsg();
	exit_in( __line__ );
}
# Verbindungsaufbau ok!
if (empty($hDB)) {
	echo "odbc_connect misslungen. (<font size=-3>$hDB</font>)";
	echo odbc_errormsg();
	exit_in( __line__ );
}
#phpinfo(); exit;

echo "odbc_connect ok! (<font size=-3>$hDB</font>)";


######################################
#odbc_tables --  Get the list of table names stored in a specific data source
if( 1 ){
	# Einfach Ausgabe aller Tabellen - Namen ...
	# echo 'odbc_tables=' . odbc_result_all( odbc_tables( $hDB )  );
	$resource_tbl = odbc_tables( $hDB );
	#echo '<select name="" onchange="on_change(this.value)">';
	echo '<br>SELECT * FROM <font size="-1">';
	while( $row = odbc_fetch_assoc( $resource_tbl ) ){ 
		$type = $row['TABLE_TYPE'] ;
		if( $type == 'SYSTEM TABLE' || $type == 'VIEW' ) continue;
		$tbl = $row['TABLE_NAME'];
		
/*
	funktioniert
  'TABLE_SCHEM' => 'SASAUS42',
  'TABLE_NAME' => 'BENACHBART',
  'TABLE_TYPE' => 'TABLE',
  	funktioniert nicht
    'TABLE_SCHEM' => 'SYSCAT',
  'TABLE_NAME' => 'BUFFERPOOLDBPARTITIONS',
  'TABLE_TYPE' => 'VIEW',
*/
		$tbl = strtolower($tbl);
		#blau('$row:' ,'', $row );
		$s = 'SELECT * FROM ' . $tbl;
		#echo '<option value="'.$s.'"'.( ( trim($sql) == trim($s) ) ? ' selected':''   ).'>'.$row['TABLE_NAME'].' : '.$s.'</option>';
		$href_table = '<a href="'.$PHP_SELF.'?sql='. $s .'&table_name='.$tbl.'" accesskey="'.substr($tbl,0,1).'">'. $tbl .'</a> ';
		if( $key_search ){
			# Was wir hir wollen heißt unter MySql: SHOW FIELDS FROM table
			# So erhällt man auf einmal alle Struckturinformationen aller Tabellen auf einmal:
			# $res400= odbc_columns( $hDB ,"","",""); # Für uns aber unzweckmässig.
			$rs = @odbc_exec( $hDB , $s. ' FETCH FIRST 1 ROWS ONLY ' );
			$row = odbc_fetch_assoc($rs) ;	unset($row[odbc_affected_rows]);
			if( isset( $row[strtoupper( $key_search )] ) ){
				# Die Spalte kommt hier vor. Wir geben alle Spalten aus.
				echo '<table border="1" cellpadding="0" cellspacing="0"><tr><td colspan="'.count($row).'"><b>'.$key_search.
				'</b> gefunden als Spalte der Tabelle '.
				'<b>'.$href_table.'</b>:</td></tr><tr>';
				foreach( array_keys( $row ) AS $key ) echo get_table_title( $key, $s, $tbl );
				echo '</tr></table>';
			}else echo $href_table;
		}else echo $href_table;


	}
	####################################
	# Manuelle Links:
	####################################
	echo '<hr>';
	include( 'aufgabenliste.inc.php' );
	while( list( $k, $v ) = each( $aufgaben_list )){
		echo_aufgabe( $k , $v[nr] , $v[text] , $v[sql] , $v[tbl]  );
	}
}
if( $aufgabennr ){
	$sql = $aufgaben_list[$aufgabennr][sql] ;
	$table_name = $aufgaben_list[$aufgabennr][tbl] ;
}
if( !$sql ) $sql = $default_sql ;
# Wir trennen die SQL bei ; auf. Das Zeichen sollte natürlich nicht anders verwendet werden.
$sql_array = explode( ';' , $sql );
while( $sql = trim(array_shift( $sql_array )) ){

	#if( $sql[0] == '#' ) continue;
	
	# odbc_exec - Liefert bei einem Fehler FALSE zurück, sonst eine ODBC-Ergebniskennung result_id. 
	# odbc_error - Returns a six-digit ODBC state, or an empty string if there has been no errors. 
	# odbc_errormsg -- Get the last error message
	
	# Wir lassen Zeilen die mit # markiert sind als Kommentar durchgehen.
	# Alternative
	#gelb($fromline.'»'.__LINE__,__FILE__, $sql, 'b' );
	if( !$sql_ohne_kommentare = trim( preg_replace( "/([^\n]*?)[#]+[^\n]*/s" , "$1" , $sql )) ) continue;
	#blau($fromline.'»'.__LINE__,__FILE__, $sql_ohne_kommentare , 'b'  );
	#$sql = preg_replace( '/([\s]+)[\s^\n]*\#/si' , "\\1\n#" , $sql );
	
	
	$result = @odbc_exec( $hDB , $sql_ohne_kommentare );
	
	# War es SELECT ??? oder WITH ???
	#preg_match( "/\s*(\w+)\b/s", trim( $sql ) , $erg );
	#$sql_erstes_wort = strtoupper($erg[1]);
	
	if($result){
		$num_rows = odbc_num_rows( $result );
		$num_fields = odbc_num_fields( $result );
	}else $num_rows = $num_fields = 'Syntax-Fehler';
	
	
	echo '<form method="get">'; 
	echo_form_content($PHP_SELF, $textarea_cols, $textarea_rows, $table_name, $sql , $num_rows, $num_fields );
	
	if( !$result ){
		echo "odbc_exec misslungen! (<font size=-3>$result</font>)";
		rot('','', $sql_ohne_kommentare . "\n" . odbc_errormsg() , 'bj'  );
		# $fromline.'»'.$line,__FILE__
	}
	
	#	int odbc_fetch_into ( int result_id [, int rownumber, array result_array] ) - 
	#	Liefert die Anzahl der Ergebnisspalten zurück, bei einem Fehler FALSE. 
	weis('&nbsp;&nbsp;<b>Anzahl</b> der <u>veränderten</u> bzw. <u>eingefügten</u> / <u>gelöschten</u> Tupel:&nbsp;' ,'', '&nbsp;&nbsp;<b>'.$num_rows .'</b>'. 
		"&nbsp;&nbsp;(<font size=-2>odbc_num_rows( $result )=<b>".$num_rows."</b> odbc_num_fields( $result )=<b>".$num_fields."</b></font>)" , '5'  );
	
	
	# Man könnte versuchen, das Datenholen hier zu steuern, aber die Steuerung würde nicht, 
	# alle Fälle berücksichtigen.
	# if( $sql_erstes_wort != 'SELECT' && $sql_erstes_wort != 'WITH') exit;
	###########################################
	
	# Ausgabe- HTML - schnell und standard
	# odbc_result_all --  Gibt das aktuelle Abfrageergebnis als HTML-Tabelle aus 
	if( 0 ){ echo odbc_result_all( $result ); exit; }
	
	// Liefert eine neue Zeile in einem Ergebnis einer Abfrage als Array zurueck
	// Als Schluessel kann dabei sowohl die Feldnummer als auch der Feldname
	// verwendet werden
	$echo_table_first_row_flag = true;

	# Schutz vor Endlosschleifen, 200 Datensätze genügen völlig.
	$max_schleifen_loops = 50; $schleifen_loops = 0;
	while( $row = odbc_fetch_assoc($result) ){ 
		if( $schleifen_loops++ > $max_schleifen_loops ){
			rot('<b><u>Notaus:</u></b> ' ,'', 'Die <b>maximal zulässige Menge von '.
			$max_schleifen_loops.' anzuzeigenden Datensätzen</b> 
			(wie im Script gesetzt) wurde überschritten und daher die Ausgabe unterbrochen.' );
			break;
		}
		
		# Ausgabe- HTML - schnell und schlampig
		if( 0 ){ echo str_replace("\n","<br>\n" , var_export( $row , true ) );
			continue; }
		
	
		# Ausgabe- HTML - hübscher
		if( 1 ){
			unset( $row[odbc_affected_rows] ); 
			###############
			# Es is hübscher, wenn wir odbc_affected_rows nur einmal ausgeben und nicht jedesmal, 
			# der Wert is eh immer gleich. Wir nehmen in daher raus.
			# Die Spalte odbc_affected_rows wurde künstlich hinzugefügt, entweder vom PHP- Interpreter oder
			# von DB2 oder sonstwo... jedenfalls wollen wir die nicht in der Tabelle sehen.
			# Einmal reicht.
			###############
			# Wir brauchen hier schon die neuen Keys, für die Spaltenbeschriftungen.
			if( $echo_table_first_row_flag ){
				$echo_table_first_row_flag = false;
				#$odbc_affected_rows = $row[odbc_affected_rows];	
				$table_keys = array_keys( $row );
				# Bestimmung der Spalte id, wir gehen einfach dafon aus die erste Spalte sei die jenige.
				# Ist ausreichend für unsere Sache.
				$tbl_id_name = $table_keys[0];
	
				# wir geben einen ausführlichen select, bei dem möglichst viel implizit vorgegeben ist, 
				# also vorlage aus.
	
				$temp = array();
				foreach( $table_keys AS $key ){
					$key = strtolower( preg_replace("/.*\./","", $key ) );
					#$name = $table_name.'.'.$key ;
					$name2 = $table_name[0].'.'.$key;
					$temp[] = $name2.' AS "'.$name2.'"';
				}
				echo '<font size="-3" color="#a0a0a0">';
				$s = 'SELECT '. implode(', ',$temp).' FROM '. $table_name.' AS '.$table_name[0];
				echo $s.
				' <a href="'.$PHP_SELF.'?sql='. urlencode($s) .'&table_name='.$table_name.
				'" accesskey="3">(ALT+3)</a></font>';
				
				echo '<table border="1" cellpadding="1" cellspacing="0" align="center"><tr>';
				echo '<tr>';
				#for( $i = 0; $i < count( $table_keys ) ; $i++){	$key = $table_keys[$i];
					#rot('ANFANG ' ,'', $key );
				$table_keys[] = '#Vorlagen'; 
				foreach( $table_keys AS $key ){
					if( $key === '#Vorlagen' ) { echo "<td align='center'><em><b>$table_name ...</b></em></td>";
						#gelb('MITTE ' ,'', $key );
						continue;
					}
					echo get_table_title( $key, $sql, $tbl );
				}
				echo '</tr>';
				#echo '<td>'.implode( '</td><td><a href="'.$PHP_SELF.'?sql='. $s .'&table_name='.$tbl.'">' , $table_keys ).'</a></td></tr>';
	
				# Exemplarisch reicht eine update und eine delete zeile... später lassen wir die weg... zwecks weniger overhead.
				$row['#Vorlagen'] = '<font size="-2">'.get_update_sql_who_does_nothing( $table_name, $row , $tbl_id_name ).'</font>' ;
				$row['#Vorlagen'] .= '<br><font size="-2">DELETE FROM '.$table_name."\n".' WHERE '.$tbl_id_name.' = '.$row[$tbl_id_name].'</font>' ;
			}
			#gelb('Sql-Befehl war:' ,'', "\$table_name=$table_name"  );
			echo '<tr><td>'.implode( '</td><td>' , array_values( $row ) ).'</td></tr>';
		}
	}
	echo '</table></form><br>';
	# Speicher freigeben
	# Es kann tatsächlich eine Rolle spielen, den Speicher vor der nächsten Anfrage frei zu geben,
	# wenn bei der nächsten Anfrage etwas aus der letzten Änderung verwendet werden soll.
	if($result)odbc_free_result($result);
}

# Verbindung schliessen
if($hDB)odbc_close($hDB);

###############
# Funktionen und classen aller hier ungen, aufrufe weiter oben
###############

#############################################
# Funktionen die was mit Datenbank zu tun haben.
#############################################

function myodbc_fetch_array($result) {
	$arr = Array();
	$num_cols = odbc_num_fields($result);
	# odbc_num_fields -- Liefert die Anzahl der Ergebnisspalten
	$count = odbc_fetch_into($result, $arr);
	# odbc_fetch_into -- Liefert die Anzahl der Ergebnisspalten zurück, bei einem Fehler FALSE.
	if ($count > 0)
		for ($i = 1; $i <= $num_cols; $i++) {
			$arr[odbc_field_name($result, $i)] = $arr[$i - 1];
			# odbc_field_name() gibt den Namen der Spalte mit der Nummer field_number für des Abfrageergebnisses result_id zurück. 
		}
	else return false;
	return $arr;
}
function odbc_fetch_assoc($rs){
	if ( @odbc_fetch_row($rs)){
		$line=array("odbc_affected_rows"=>odbc_num_rows($rs));
		for($f=1;$f<=odbc_num_fields($rs);$f++){
			$fn=odbc_field_name($rs,$f);
			$fct=odbc_result($rs,$fn);
			$newline=array($fn => $fct);
			$line=array_merge($line,$newline);
			//echo $f.": ".$fn."=".$fct."<br>";
		}
		return $line;
	}
	else{
		return false;
	}
}
function exit_in( $line , $text = '' ){
	$text = $text . "\n".'<b>Script wird beendet.</b> <br>'. odbc_errormsg();
	global $php_as_shell_flag;
	if( $php_as_shell_flag ){
		$text .= 'Bitte RETRUN-/ENTER-Taste klicken.'; 
		fgets(STDIN,100); exit;
	}
	rot($fromline.'»'.$line,__FILE__, $text , 'b'  );
	exit;
}
#####################################
# Ausgabe - Funktionen
# (lasse alles in einer Datei dann wird beim kopieren nix vergessen.)
#####################################
function get_table_title( $key, $sql, $tbl ){
	if( preg_match("/[A-Z]+[a-z]+/" , $key ) ){
		# Es kommen wahrscheinlich groß und klein Buchstaben, vor und wohl absichtlich zur
		# besseren Lesbarkeit.... was wir erhalten möchten:
		$key_pretty = '"'.$key.'"';
		#gelb('$key:' ,'', 1 );
	}else{
		# Entgegen der DB2 - Konvention, gleich der MySql - Konvention, mag ich 
		# kleingeschriebenen Eigen-Namen
		$key_pretty = strtolower( $key );
		#gelb('$key:' ,'', 2 );
	}
	#blau('ENDE ' ,'', $key );
	$s = urlencode( preg_replace( "/\bORDER\s+BY\b.*/" , '' , $sql ) )  ;
	$s_asc = $s . urlencode( ' ORDER BY '.$key_pretty. ' ASC ') ;
	$s_desc = $s . urlencode( ' ORDER BY '.$key_pretty. ' DESC ') ;
	$k = '<a href="'.$PHP_SELF.'?sql='. urlencode( $sql ) .'&key_search='. preg_replace("/.*\W/",'',$key).'">'.$key.'</a>';
	$a = '<a href="'.$PHP_SELF.'?sql='. $s_asc .'&table_name='.$tbl.'">A</a>';
	$d = '<a href="'.$PHP_SELF.'?sql='. $s_desc .'&table_name='.$tbl.'">D</a>';
	return "<td>$k $a $d</td>";
}
function pretty_sql( &$sql){
	#gelb($fromline.'»'.$line,__FILE__, $sql , 'b'  );
	# Ich mag es nicht wenn, Namen groß geschrieben sind... bin hier also ganz 
	# gegen die Konvention von DB2 ... Aber, ich darf nicht alles klein machen,
	# weil sonst im Zusammenspiel mit "MeinName" Fehler auftreten könnten :(
	# $sql = strtolower( $sql) ;
	$list_newline = 'select|insert|delete|update|from|where|order|values|union|fetch|group|having|union|intersect|case|when|else|with|drop|in';
	# Ganz kleine SQLs brauchen wir nicht auftrennen.
	if( strlen( $sql ) > 60 ) 
		$sql = trim(preg_replace( '/([^#])\s+\b('.$list_newline.')(\b)/is' , strtoupper("\\1 \n\\2\\3") , $sql ));
	# Kommentare sollen in in einer extra Zeile stehen.
	#rot($fromline.'»'.$line,__FILE__, $sql , 'b'  );
	$sql = preg_replace( "/([^\n#]+)([#]+)/s" , "$1\n$2" , $sql );
	#rot($fromline.'»'.$line,__FILE__, $sql , 'f'  );
	$list_upper  = strtolower($list_newline . '|into|soundex|as|with|max|min|SUBSTr|DISTINcT|AND|OR|all|rand'.
	'|asc|desc|count|by|grouping|then|end|cast|DEcIMAL|avg|FULL|OUTER|JOIN|integer|locate|RTRIM|length|DIFFERENCE|not|exists|excep');
	$list_upper_array = explode('|', $list_upper );
	foreach( $list_upper_array as $n ) $sql = preg_replace( '/(\b)'.$n.'(\b)/is' , "\\1".strtoupper($n)."\\2" , $sql ) ;
	#gelb($fromline.'»'.$line,__FILE__, $sql . "\n$list_newline" , 'b'  );
	######################################
}
function echo_form_content($PHP_SELF, $textarea_cols, $textarea_rows, $table_name='', $sql='', $num_rows=0, $num_fields=0 ){

	echo '<table align="center" border="2" cellpadding="2" cellspacing="0" bordercolor="#ffff00"><tr><td>';

	#blau($fromline.'»'.__LINE__,__FILE__, $sql, 'p' );
	$textarea_cols = 100;
	
	#gelb('Sql-Befehl war:' ,'', '<code>'.$sql.'</code>' , 'p'  );
	$info_text = '; # focus hier ALT+2';
	$einrueckung = str_repeat(' ', $textarea_cols - strlen($info_text) - 3 );
	$info_text1 =  $einrueckung.$info_text; 
	$info_text2 = "\n# Die Standard SELECTS * oben, erhällt man auch über".
				"\n# ALT+(Erster Buchstabe der Tabelle.)+ENTER ";
	$info_text2 .= "\n# odbc_num_rows: $num_rows ,  odbc_num_fields: $num_fields";
	$info_text = $info_text1.$info_text2;
	if( !eregi( quotemeta($info_text1), $sql )) $sql .= "\n".$info_text;
	$textarea_rows = substr_count ( $sql , "\n" ) + 1 - substr_count ( $info_text , "\n" ) ;
	# onmouseover="load_backup();"
	# onmouseover="load_backup();"
	echo '<em>&nbsp;&nbsp;SQL-Befehl fuer DB2</em>&nbsp;&nbsp;&nbsp;&nbsp;<input type="Submit" accesskey="1" value="SQL senden (ALT+1)"';
	echo ' onmouseover="load_backup();" ';
	echo ' onclick="load_backup();" " ';
	echo '>
	<a href="'.$PHP_SELF.'?sql='. 'SELECT * FROM ' . $table_name .'&table_name='.$table_name.'">SELECT * FROM '.$table_name.'</a>
	<br><textarea name="sql" cols="'.$textarea_cols.'" rows="'.$textarea_rows.
	'" ';
	echo ' onclick="load_backup();" " ';
	#echo ' onmouseout="set_backup();" ';
	#echo ' onblur="set_backup();" ';
	echo ' onmouseover="load_backup();" ';
	echo ' onchange="set_backup();" ';
	#echo ' onkeypress="set_backup();" ';
	echo ' onfocus="this.select();" " ';
	echo ' accesskey="2">'.$sql.'</textarea>';
	#blau($fromline.'»'.__LINE__,__FILE__, $sql, 'p' );

	echo '</td>';
	
		# show_text_in_form(t){	document.forms[0].elements['sql'].value = t; }
		# load_backup(){ document.forms[0].elements['sql'].value = backup_text; }
		# set_backup(){	backup_text = document.forms[0].elements['sql'].value; }
	#gelb('Sql-Befehl war:' ,'', '<code>'.$sql.'</code>' , 'b'  );
	echo '</tr></table>';
}
function echo_aufgabe( $aufgabennr , $nummerierung , $text , $sql_befehl , $tbl_name  ){
	global $PHP_SELF;
	$sql_befehl = trim($sql_befehl);
	$temp = preg_replace("/<[^>]*>/",'', trim( $nummerierung ) );
	$s = preg_replace("/\s+/s", ' ', $sql_befehl );
	#$status = str_replace('"','´´',"window.status='".str_replace(array("\n","'"),array(' ',"\'"),$s)."';");
	$status = "'".str_replace(array("\n","'",'"'),array(' ',"\'",'´´'),$s)."'";
	$js_alert = js_alert($fromline.'»'.$line,__FILE__, $text, 'br' ) ;
	# return true;
	$js_text = "'". substr( preg_replace( "/.*?\.php\s*\\\(.*)\);/is", "$1" , $js_alert[1] ) , 1) ;
	#   "alert( '$zeile $file_kurz".'\\n'."$text');",
	#gelb($fromline.'»'.$line,__FILE__, quotemeta(str_replace('.','\.',__FILE__)) .'<hr>' . $js_text  );


	# show_text_in_form(t){	document.forms[0].elements['sql'].value = t; }
	# load_backup(){ document.forms[0].elements['sql'].value = backup_text; }
	# set_backup(){	backup_text = document.forms[0].elements['sql'].value; }

	$onmouseover = "\n".' onmouseover="show_text_in_form('.$js_text.','.$status.');"'.
	"\n".' ';
	#gelb($fromline.'»'.$line,__FILE__, $onmouseover , 'b' );
	echo '<font size="+1"> <b>';
	if( strtolower(substr( $nummerierung , 0 , 4 )) == '<br>' ){
		$nummerierung =  substr( $nummerierung , 4  );	echo '<br>';
	}
	echo '&#664</b> '.(is_numeric($temp[0])?'<em>Aufg.</em>':'').$nummerierung.
	' <a href="' . $PHP_SELF . '?aufgabennr='. $aufgabennr
	.'"'.$onmouseover.'>'.substr( $sql_befehl , 0 , 3 ).'...</a> ';
	echo "\n".'<a href="javascript:show_text_in_form('.$status.','.$status.');alert('.$js_text
	.');"'.$onmouseover."\n".' onkeypress="'.$status.'">Text</a></font>';
	#gelb($fromline.'»'.$line,__FILE__, "\$temp=$temp, \$temp[0]=".$temp[0].(is_numeric($temp[0])?'<em>Aufg.</em>':'')  );
}
function get_update_sql_who_does_nothing( &$table_name, &$row, &$tbl_id_name ){
	$update = 'UPDATE ' . $table_name . " SET ";
	while( list( $k, $v ) = each( $row ) ){
		# odbc_field_type -- Liefert den Datentyp eines Feldes
		if(!$v) continue;
		$v = ( !is_numeric( $v ) && !ereg(',',$v) ) ? "'$v'" : str_replace( ',' , '.' , $v ) ;
		$temp[] = "\n $k = $v ";
	}
	$update .= @implode( ', ' , $temp ) . "\n WHERE $tbl_id_name = " . $row[$tbl_id_name] . " ";
	return $update . '<a href="?sql=' . urlencode($update) . '" accesskey="4">(ALT+4)</a>';
}
function horizontal_line( $newline1, $width , $newline2 ){
	echo str_repeat( "\n" , $newline1 ) . str_repeat( "_" , $width ) . str_repeat( "\n" , $newline2 ) ; 
}
function weis( $zeile , $file , $text , $code = '' , $c=false, $wort_bgcolor_ar=false ){
	if(!$c)$c='ffffff';farbtabelle( $zeile , $file , $text , $code , $c, $wort_bgcolor_ar );
}
function blau( $zeile , $file , $text , $code = '' , $c=false, $wort_bgcolor_ar=false ){
	if(!$c)$c='66CCFF';farbtabelle( $zeile , $file , $text , $code , $c, $wort_bgcolor_ar );
}
function rot( $zeile , $file , $text , $code = '' , $c=false, $wort_bgcolor_ar=false ){
	if(!$c)$c='FFCC99';farbtabelle( $zeile , $file , $text , $code , $c, $wort_bgcolor_ar );
}
function BIG_gelb( $zeile , $file , $text , $code = '' , $c=false, $wort_bgcolor_ar=false ){
	if(!$c)$c='FFFFCC';farbtabelle( $zeile , $file , $text , $code , $c, $wort_bgcolor_ar );
}
function gelb( $zeile , $file , $text , $code = '' , $c=false, $wort_bgcolor_ar=false ){
	if(!$c)$c='FFFFCC';farbtabelle( $zeile , $file , $text , $code , $c, $wort_bgcolor_ar );
}
function green( $zeile , $file , $text , $code = '' , $c=false, $wort_bgcolor_ar=false ){
	if(!$c)$c='32CD32';farbtabelle( $zeile , $file , $text , $code , $c, $wort_bgcolor_ar );
}
function pink( $zeile , $file , $text , $code = '' , $c=false, $wort_bgcolor_ar=false ){
	if(!$c)$c='FFB6C1';farbtabelle( $zeile , $file , $text , $code , $c, $wort_bgcolor_ar );
}
function farbtabelle( $zeile , $file , $text , $code , $farbe , $wort_bgcolor_ar, $width = '100%' ){
	# n - Zeilennummerierung
   # e - htmlentities
   # p - pre
   # f - textarea
   # j - jump
   # v - var_export
   # x - exit
   #echo "<h2>$code=\$code</h2>" ;
   global $SERVER_NAME;

   if( $SERVER_NAME != 'localhost' )unset($file);
	$file_kurz = preg_replace( "-.*[\\\\/]-is",'',$file );
	$is_php = ( strpos( $text , '?'.'>'  )) ;
	if( is_array( $text ) || is_object( $text ) ){
		$text = var_export( $text, true );
		$is_php = true;
	}
   if(eregi('n',$code)){
      # Die Zeilen werden durchnummeriert.
      $code .= 'p' ;
      $temp = explode("\n", $text);
	  $count = count($temp);
	  $l = strlen( $count );
      for( $i = 0 ; $i < $count ; $i++ ) {
 		  $ii= $i+1 ;
		  while( strlen( $ii ) < $l ){ $ii = '0' . $ii; }
          if($temp[$i])$temp[$i] = $ii.":".$temp[$i];
      }
      $text = implode ("\n" , $temp ) ;
   }
   if(eregi('b',$code)){
		$text = str_replace( array('  ',"\n") , array('&nbsp; ',"<br>\n") , $text ) ;
   }
   if(eregi('p',$code)){
      $pre1 = '<pre>';
      $pre2 = '</pre>';
   }
	# beschreibt die dicke des Tabellenrandes
   $tbl_border = (   preg_match("/\d+/",$code,$digit)   ) ? $digit[0] : 1 ;
   if(eregi('f',$code)){
      $pre1 = '<textarea cols="50" rows="5">';
      $pre2 = '</textarea>';
   }elseif( $is_php ) $text = str_replace( array('&lt;?','?&gt;') , '' , highlight_string( '<?'.$text.'?>' , true ) ) ;
   if(eregi('e',$code)){
      $text = htmlentities($text);
   }
	if( is_array( $wort_bgcolor_ar ) ){
		# die schlüssel sind gleichzeitig die Strings die mit der Hintergrundfarbe der zugehörigen Werte ersetzt werden.
		while( list( $string , $bgcolor ) = each( $wort_bgcolor_ar ) ){
			$text = str_replace( $string , '<span style="border: 1px solid #BEBEBE; background-color:'.$bgcolor.'">'.$string.'</span>' , $text );
	}	}
   
   #echo "$code , $farbe";
   if ( is_int( $zeile )) $zeile = 'Zeile = ' . $zeile ;
	$strlen_status = strlen ( $zeile . $file_kurz ) ;
	$strlen_text= strlen ( $text ) ;
	$status_zeile = '<td valign="top" bgcolor="#FFFFFF" nowrap><font color="Black"><font size="-1">'.$zeile.
		'<br><font size="-2"><a href="'.$file.'" target="o">'.$file_kurz.'</a></font></font></td>';
	$text_zeile = '<td bgcolor="#'.$farbe.'" width="'.$width.'"><font color="Black">'.$pre1.$text.$pre2.'</font></td>';
	echo '<table border="'.$tbl_border.'" cellspacing="0" cellpadding="0" width="'.$width.'"><tr>';
	if( $strlen_status * 12 > $strlen_text ) echo $status_zeile . $text_zeile ;
	else echo $status_zeile . '</tr><tr>' . $text_zeile ;
   echo '</tr></table>' ;
   if(eregi('x',$code)){
   	echo "<b> - E <u>X</u> I T - </b>";
      exit;
   }
   if(eregi('j',$code)){
			# es soll gleich zu dieser meldung gesprungen werden.
         #echo '</tr><tr><td>';
         $name = 'f'.mktime();
         echo '<form name="'.$name.'">'.
         '<input type="Button" style="font-size: 9px; color: black; border: 0px White; background: White;" name="'.$name.'" value="Jump"></form>'  .
         '<script language="JavaScript">
         document.forms["'.$name.'"].elements["'.$name.'"].focus();
         </script>' ;
         #echo '</td>';
	 }
}
function js_alert( $zeile = '',$file = '' ,  $text = '' , $code = 'h' ){
   $text = str_replace( "'" , "\'" , $text ) ;
	if(!eregi('h',$code)){
		# HTML- Tags werden drin gelassen.
		$temp = '/([^\\\\])<[^<>]*?>/si' ;
		$text = str_replace("\n",'\\n',preg_replace( $temp ,"$1", $text ));
	}
	$text = preg_replace( "/<br>/is" , '\\n' ,  $text ) ;
	$text = preg_replace( "/<\/?(b|p|hr)>/is" , '' ,  $text ) ;
	$file_kurz = preg_replace( "-.*[\\\\/]-is",'',$file );
   if(eregi('n',$code)){
      # Die Zeilen werden durchnummeriert.
      $code .= 'p' ;
      $temp = explode("\n", $text);
	  $count = count($temp);
	  $l = strlen( $count );
      for( $i = 0 ; $i < $count ; $i++ ) {
 		  $ii= $i+1 ;
		  while( strlen( $ii ) < $l ){ $ii = '0' . $ii; }
          if($temp[$i])$temp[$i] = $ii.":".$temp[$i];
      }
      $text = implode ("\n" , $temp ) ;
   }
   if(eregi('p',$code)){
      $text = preg_replace( "/[\r\f\n\t]/" , '\\n'  ,  $text ) ;
   }else{
      $text = preg_replace( "/[\r\f\n\t]/" , ' ' ,  $text ) ;
   }
   if(eregi('e',$code)){
      #$text = htmlentities($text);
   }
   if ( is_int( $zeile )) $zeile = 'Zeile = ' . $zeile ;
	if(eregi('r',$code)){
		# r steht für return
	   return array( "<script language='JavaScript'>",
	   "alert( '$zeile $file_kurz".'\\n'."$text');",
	   "</script>");
	}else
   echo "<script language='JavaScript'>
   alert( '$zeile $file_kurz".'\\n'."$text');
   </script>";
}
######################################
### Es folgen nur noch Kommentare. ###
######################################
#################################################
# Diverse Notizen/Aufschriebe
# (mache oben einen Verweis, dass es diese unten gibt)
#################################################
/*
odbc_error -- Get the last error code

google: "odbc_connect"
http://www.google.de/search?hl=de&client=firefox-a&rls=org.mozilla%3Ade-DE%3Aofficial&q=odbc_connect&btnG=Suche&meta=lr%3Dlang_de

http://www.google.de/search?hl=de&client=firefox-a&rls=org.mozilla%3Ade-DE%3Aofficial&q=odbc_connect+db2&btnG=Suche&meta=lr%3Dlang_de
odbc_connect db2

ODBC Datenbankquellen 2000
http://www.google.de/search?hl=de&client=firefox-a&rls=org.mozilla%3Ade-DE%3Aofficial&q=ODBC+Datenbankquellen+2000&btnG=Suche&meta=

odbc installieren
http://www.google.de/search?hl=de&client=firefox-a&rls=org.mozilla%3Ade-DE%3Aofficial&q=odbc+installieren&btnG=Suche&meta=lr%3Dlang_de
Systemsteuerung > Verwaltung > Datenquellen

datenquelle: datenquelle
CLI-Parameter: DBALIAS
Wert: TEST
Anstehender Wert: TEST

Im Dos-Fenster kopieren: Markieren und dann Eingabetaste

Warning: odbc_connect(): SQL error: [Microsoft][ODBC Driver Manager] Data source name not found and no default driver specified, SQL state IM002 in SQLConnect in E:\test.php on line 37
Verbindungsaufbau misslungen..

odbc_connect db2 Data source name not found and no default driver specified
http://www.google.de/search?hl=de&client=firefox-a&rls=org.mozilla:de-DE:official&lr=lang_de&q=odbc_connect+db2+Data+source+name+not+found+and+no+default+driver+specified&spell=1

Warning: odbc_connect(): SQL error: [IBM][CLI Driver] SQL30082N  Die Verbindung
konnte auf Grund der Sicherheitsbedingung "24" ("USERNAME AND/OR PASSWORD INVALI
D") nicht hergestellt werden.  SQLSTATE=08001
, SQL state 08001 in SQLConnect in E:\test.php on line 49
Verbindungsaufbau misslungen..
C:\Dokumente und Einstellungen\sasaus42>

to-do:
------
- DB2 Personal Edition, PHP installieren
- DB2 Datenbank erzeugen.
- Systemsteuerung > Verwaltung > Datenquellen (ODBC)
 dort unter USER DSN Hinzufügen "IBM DB2 DBC DRIVER"
	und 
 dort unter SYSTEM DSN Hinzufügen "IBM DB2 DBC DRIVER"
*/
?>
</body></html>