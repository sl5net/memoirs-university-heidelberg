<?	
$aufgaben_list = array();
###############################
$aufgaben_list[1] = array( nr => '<b><u>2.3.3.1</u></b>' , 
text => 'Geben Sie alle Städte an, die ungefähr 100.000 Einwohner haben.
Sortieren Sie die Städte 
nach der Abweichung von dieser Einwohnerzahl 
(Städte mit kleinerer Abweichung stehen weiter vorne in der Liste).

Geben Sie alle Städte an, die um maximal 10.000 Einwohner von 100.000 Einwohnern abweichen.' , 
sql => '
SELECT name, einwohner, ABS( einwohner - 100000 ) AS abweichung  FROM STADT 
WHERE einwohner + 10000 >= 100000 
AND	einwohner - 10000 <= 100000 
ORDER BY ABS( einwohner - 100000 ) ' , tbl => 'STADT' );
###############################

##echo '<br>';
###############################
$aufgaben_list[] = array( nr => '<b><u>2.3.3.2</u> Ähnlichkeit mittels LOCATE|SOUNDEX|DIFFERENCE</b>' , 
text => 
'Geben Sie alle Länder an, deren Hauptstadtname Ähnlichkeit mit dem Landesnamen hat.
Die Lösung ganz am Ende mit DIFFERENCE gefällt mir am besten.
' 
, sql => '
SELECT s.name AS Stadt, l.name AS Land  , length( RTRIM(s.name) ) AS Stadtlänge , length( RTRIM(l.name) ) AS Landlänge
FROM land l, stadt s

### LOCATE( s1, s2 ) -> Integer
### gibt die Position des ersten Auftauchens von String s2 in String s1 an.
WHERE l.hauptstadt  = s.s_id
AND ( 		LOCATE(RTRIM(s.name), RTRIM( l.name)) > 0 
		OR  LOCATE(RTRIM(l.name), RTRIM( s.name)) > 0   )	;

### Zur Verdeutlichung einmal nur das enthaltensein von Stadtnamen im Landesnamen
SELECT s.name AS Stadt, l.name AS Land  , length( RTRIM(s.name) ) AS Stadtlänge , length( RTRIM(l.name) ) AS Landlänge
FROM land l, stadt s 
WHERE l.hauptstadt = s.s_id AND LOCATE(RTRIM(s.name), RTRIM( l.name)) > 0 ;

### Und noch einmal umgekehrt, das enthaltensein von Landesnamen im Stadtnamen
SELECT s.name AS Stadt, l.name AS Land  , length( RTRIM(s.name) ) AS Stadtlänge , length( RTRIM(l.name) ) AS Landlänge
FROM land l, stadt s 
WHERE l.hauptstadt = s.s_id AND LOCATE( RTRIM( l.name) , RTRIM(s.name) ) > 0 ;

### Alternative Lösung mit Funktion SOUNDEX()
SELECT s.name AS Stadt, l.name AS Land
FROM land l, stadt s
WHERE l.hauptstadt = s.s_id AND SOUNDEX(s.name) = SOUNDEX(l.name);

### Alternative Lösung mit Funktion DIFFERENCE( s1, s2 ) Klangübereinstimmung zw. 0 und 4
SELECT s.name AS Stadt, l.name AS Land, DIFFERENCE( s.name , l.name ) as Klangübereinstimmung
FROM land l, stadt s
WHERE l.hauptstadt = s.s_id AND DIFFERENCE( s.name , l.name ) > 2
ORDER BY Klangübereinstimmung DESC
;

'
 , tbl => 'STADT' );
###############################

#echo '<br>';
###############################
$aufgaben_list[] = array( nr =>  'INSERT .. MAX(S_ID)' , 
text => 'Insert in eine Tabelle.' 
, sql => "
INSERT INTO stadt ( s_id, name, einwohner ) 
VALUES ( 
	(  SELECT MAX(S_ID)+1 FROM stadt  ), 
	'TestStadt', 
	12345 
);

SELECT * FROM stadt WHERE S_ID > 629;

DELETE FROM STADT WHERE S_ID > 629
"
, tbl => 'STADT' );
###############################

#echo '<br>';
$wo_ist_die_teststadt = "
SELECT s_id, concat(name , 'von stadtview') , einwohner 	FROM stadtview 
	WHERE name LIKE '%TestStadt%'
UNION 
SELECT s_id, concat(name , 'von stadt'), einwohner 	   		FROM stadt 
	WHERE name LIKE '%TestStadt%'
ORDER BY s_id
";

###############################
$aufgaben_list[] = array( nr =>  '<b><u>2.3.3.3 a), b), c), d), e), f)</u></b> &nbsp;VIEW' , 
text =>'Erzeugen Sie mit einer Sicht einen Auschnitt der Relation Stadt, 
in der alle Städte mit mehr als fünf Millionen Einwohnern eingetragen sind. 
Übernehmen Sie in die Sicht die Attribute S ID, Name und Einwohner. 

Weitere Teilaufgaben zwischen dem SQL- Befehl formuliert.
Beschreiben Sie für die folgenden Operationen jeweils die Reaktionen des Datenbanksystems:
' 
, sql => '
CREATE VIEW stadtview ( s_id, name, einwohner) AS
	SELECT s_id, name, einwohner 
		FROM stadt
		WHERE einwohner > 10000000;
# Die Sicht ist erzeugt, jetzt wollen doch mal sehen was so drin steckt.
SELECT * FROM stadtview;
# 2.3.3.3 a) Stadt in Sicht mit mehr als fünf Millionen Einwohnern einfügen und ausgeben.
INSERT INTO stadtview (s_id, name, einwohner) 
VALUES ( 
	(  SELECT MAX(S_ID)+99  FROM stadtview), 
	\'TestStadt\', 
	10000001 
);

	'. $wo_ist_die_teststadt ."
# Interessant ist, das der eben eingefügte Datensatz zweimal angezeigt wird.
# Man sieht (vielleicht überraschend) dass der Datensatz in Tabelle stadt eingefügt wurde.
# obwohl der insert_ eindeutig in die view einfügt. ;
# 2.3.3.3 b)
DELETE FROM stadtview WHERE name = 'TestStadt' 
# Wir werden gleich sehen, das sich dieses löschen 
# wieder auf die Tabelle ausgwirkte. Im View ist der Eintrag auch nicht mehr zu finden. ;
".  $wo_ist_die_teststadt ." ;

# 2.3.3.3 c) 
# Fügen Sie eine Stadt mit weniger als fünf Millionen Einwohnern in die Sicht ein.
# Lassen Sie sich die Sicht und die Relation Stadt ausgeben.
INSERT INTO stadtview (s_id, name, einwohner) 
VALUES ( 
	( SELECT MAX(S_ID)+99 FROM stadtview ), 
	'TestStadt', 
	11 
)
# Wir werden sehen, dieser neue Datensatz erstens wieder in Tabelle stadt
# eingefügt wurde und zweitens in stadtview nicht mehr auftaucht, ganz gemäß
# der Bedinungen des VIEWS bezüglich der Einwohnerzahl. Fein :)
# Also alles so wie es sein sollte. Der VIEW bleibt stimmig.
;
".  $wo_ist_die_teststadt ." 
;
# 2.3.3.3 d) 
# Fügen Sie die Stadt erneut mit mehr als fünf Millionen Einwohnern in die Sicht ein.
# Lassen Sie sich die Sicht ausgeben.
INSERT INTO stadtview (s_id, name, einwohner) 
VALUES ( 
	( SELECT MAX(S_ID)+99 FROM stadtview ), 
	'TestStadt', 
	10000002 
)
# Interessant hier gibt es eine Fehlermeldung, weil dieser Index für den letzten Einfug in stadt
# verwendet wurde, also belegt ist.
# Dies wird nicht erkannt bzw. berücksichtig, da letzer Einfug nicht im VIEW vermerkt ist. ;
SELECT MAX(v.S_ID)+99 AS \"MAX(v.S_ID)+99\", MAX(v.S_ID) AS \"MAX(v.S_ID)\", 
	MAX(s.S_ID)+99 AS \"MAX(s.S_ID)+99\", MAX(s.S_ID) AS \"MAX(s.S_ID)\" 
FROM stadtview AS v, stadt AS s	;

INSERT INTO stadtview (s_id, name, einwohner) 
VALUES ( 
	( SELECT MAX(S_ID)+1 FROM stadt ), 
	'TestStadt', 
	10000002 
)
;
".  $wo_ist_die_teststadt ." 
;
# 2.3.3.3 e) 
# Löschen Sie die Stadt aus der Sicht.
DELETE FROM stadtview WHERE einwohner = 10000002
;
".  $wo_ist_die_teststadt ." ;
# 2.3.3.3 f) 
# Löschen Sie die Stadt aus der Relation Stadt.
DELETE FROM stadt WHERE einwohner = 10000002
# Klar, wir wissen ja, das hat auswirkungen auf VIEWs und RELATION
;
".  $wo_ist_die_teststadt ." 
;
# Und wieder löschen, für den nächsten Versuch :)
DROP VIEW stadtview;
DELETE FROM STADT WHERE S_ID > 629;
"
 , tbl => 'stadtview' );
###############################

#echo '<br>';
###############################
$aufgaben_list[] = array( nr =>  '<b><u>2.3.4.1</u></b>' , 
text =>'Bestimmung sämtliche Nachbar-IDs von Bundesrepublik_Deutschland.
Tip: Eine Sicht anzulegen, um nur eine Anfrage zu stellen, ist etwas umständlich. 
Dafür gibt es die WITH-Anweisung.' 
, sql => "
SELECT l.NAME, b.L_ID1, b.L_ID2     FROM LAND l ,  BENACHBART b 
	WHERE l.NAME LIKE '%Deutschland%' 
	AND ( 	l.L_ID = b.L_ID2 
		OR  l.L_ID = b.L_ID1 )
"
, tbl => 'STADT' );

# Wie lässt sich der Nachbar einer Region finden ?
# Die Info steht in Tabell BENACHBART
###############################


###############################
$aufgaben_list[] = array( nr =>  '2.3.4.1 a) Bevölkerungsdichte' , 
text =>'Bestimmen Sie die Bevölkerungsdichte der Region, die die Länder Algerien,
Libyen und sämtliche Nachbarn dieser Länder umfaßt.
Tip: Eine Sicht anzulegen, um nur eine Anfrage zu stellen, ist etwas umständlich.
' 
, sql => "
SELECT * FROM land AS l1     WHERE  (name = 'Algerien'  OR  name = 'Libyen') ;
# Wir brauchen also die Länder Algerien, Libyen
# Bevölkerungsdichte müsste Einwohner pro Fläche sein, also:
SELECT name, einwohner/ flaeche AS \"Bevölkerungsdichte\" FROM land AS l1 WHERE
(name = 'Algerien' OR name = 'Libyen') ;

# zusammen
SELECT avg(einwohner/ flaeche) AS \"Bevölkerungsdichte\" FROM land AS l1 WHERE
(name = 'Algerien' OR name = 'Libyen') ;

# Ok soweit, jetzt sollten noch alle Nachbarn einbezogen werden.
# Da hilft die Tabelle benachbart( l_id1 ,	l_id2 )
# Suchen wir erst mal alle Länder ids der Nachbarn.
SELECT l_id1, l_id2 FROM benachbart
WHERE  l_id1 IN (
		SELECT l_id		FROM land AS l1
		WHERE name LIKE 'Algerien%' OR name LIKE 'Libyen%' )
	OR l_id2 IN (
		SELECT l_id		FROM land AS l1
		WHERE name LIKE 'Algerien%' OR name LIKE 'Libyen%' )
# Man muss hier aufpassen das man statt IN nicht versehentlich = schreibt.
# Es gäbe hier zwar keinen Fehler aber auch keine Ergebnisse.
;
SELECT l_id1  AS \"l_id1 Nachbarn\", l_id2
FROM benachbart 
WHERE l_id2 IN ( 
SELECT l_id 
FROM land 
WHERE (name LIKE 'Algerien%' OR name LIKE 'Libyen%') )
# So haben wir erst mal alle ids der Nachbarn in der Spalte l_id1 von 6 bzw. 84
# Aber es könnte vielleicht ja Nachbarn in der anderen Spalte auch gebeen.
# Schaun wir mal.
;
SELECT l_id1 , l_id2 AS \"l_id2 Nachbarn\" FROM benachbart 
WHERE l_id1 IN ( 
	SELECT l_id FROM land 
	WHERE (name LIKE 'Algerien%' OR name LIKE 'Libyen%') )
# alle ids der Nachbarn stehen in der Spalte l_id2 von 6 bzw. 84
;	
SELECT * FROM land 
WHERE 
l_id IN (
	SELECT l_id1 FROM benachbart 
	WHERE l_id2 IN ( 
		SELECT l_id FROM land WHERE name LIKE 'Algerien%' OR name LIKE 'Libyen%'
	)
)
OR l_id IN (
	SELECT l_id2 FROM benachbart 
	WHERE l_id1 IN ( 
		SELECT l_id FROM land WHERE name LIKE 'Algerien%' OR name LIKE 'Libyen%'
	)
)
OR name LIKE 'Algerien%' OR name LIKE 'Libyen%'

# Jetzt haben wir tatsächlich alle Nachbarländer, aber es sieh etas unschön aus, finde ich.
# Mit dem letzten beiden or kriegen wir unsere beiden Länder auch noch hinnein.
# Es sollte auch eleganter gehen.
# Schauen wir mal was redundant ist und ersetzten dies mit einer temporären Sicht.
;
# Folgender Ansatz, hätte gehen sollen, geht aber nicht :( Warum eigentlich?
WITH my_region (l_id) AS (
	SELECT l_id FROM land WHERE name LIKE 'Algerien%' OR name LIKE 'Libyen%'
)
SELECT * FROM land 
WHERE 
l_id IN (
	SELECT l_id1 FROM benachbart 
	WHERE l_id2 IN ( 		my_region	)
)
OR l_id IN (
	SELECT l_id2 FROM benachbart 
	WHERE l_id1 IN ( 		my_region	)
)
OR name LIKE 'Algerien%' OR name LIKE 'Libyen%'

# Dise Vereinfachung führt leider zu Fehler ... ich weiss auch nicht warum.
;

WITH my_region (l_id) AS ( 
	SELECT l.l_id FROM land l, benachbart b, land l2 
		WHERE 
		l.l_id = b.l_id2   AND   b.l_id1 = l2.l_id 
		AND (l2.name = 'Algerien' OR l2.name = 'Libyen') 
	UNION 
	SELECT l.l_id FROM land l, benachbart b, land l2 
		WHERE 
		l.l_id = b.l_id1   AND   b.l_id2 = l2.l_id 
		AND (l2.name = 'Algerien' OR l2.name = 'Libyen') 
) 
SELECT l.name AS name, l.einwohner AS einwohner, l.flaeche AS flaeche 
	FROM land l, my_region
	WHERE l.l_id = my_region.l_id	

# Das hier klappt.
"
, tbl => 'land l, my_region m' );

# Wie lässt sich der Nachbar einer Region finden ?
# Die Info steht in Tabell BENACHBART
###############################

###############################
$aufgaben_list[] = array( nr =>  '2.3.4.1 b) Bevölkerungsdichte ohne Wüsten' , 
text =>'2.3.4.1 b) Vergleichen Sie das Ergebnis mit der Bevölkerungsdichte die man erhält, wenn
man die Wüsten als unbewohnbar berücksichtigt.
' 
, sql => '
# Wüsten wo sind die?
SELECT w.w_id AS "w.w_id", w.name AS "w.name", w.flaeche AS "w.flaeche", w.wuestenart AS "w.wuestenart" 
FROM wueste AS w 
# Aber welche wüsten muss ich berücksichtigen ?
;
# Hat das was mit Tabelle geo_wueste zu tun ?
SELECT g.lt_id AS "g.lt_id", g.w_id AS "g.w_id" 
FROM geo_wueste AS g
; 
# Und wohl der Tabelle landesteil
SELECT l.lt_id AS "lt.lt_id", lt.name AS "lt.name", lt.l_id AS "lt.l_id", lt.einwohner AS "lt.einwohner", lt.lage AS "lt.lage", lt.hauptstadt AS "lt.hauptstadt" 
FROM landesteil AS lt
# Hier sehen wir einen zusammenhang zwischen lt.lt_id und lt.l_id
;
SELECT l.l_id AS "l.l_id", l.name AS "l.name", l.einwohner AS "l.einwohner", l.zuwachs AS "l.zuwachs", l.flaeche AS "l.flaeche", l.bsp AS "l.bsp", l.staatsform AS "l.staatsform", l.regierungschef AS "l.regierungschef", l.hauptstadt AS "l.hauptstadt" 
FROM land AS l
# Hier haben wir dann wieder unsere l.l_id
;
# Ich will erst mal ausgeben können wo eine Wüste liegt, in welchem Land bzw. in welchen Ländern.
SELECT w.w_id AS "w.w_id", g.lt_id AS "g.lt_id" , lt.lt_id AS "lt.lt_id", l.l_id AS "l.l_id", 
	w.name AS "w.name", w.flaeche AS "w.flaeche", w.wuestenart AS "w.wuestenart", l.name AS "l.name" 
FROM geo_wueste AS g, wueste AS w, landesteil AS lt, land AS l 
WHERE w.w_id = g.lt_id AND g.lt_id = lt.lt_id
AND lt.l_id = l.l_id
;
# Ich verstehe nicht warum es oben mehrere gleiche Ergebnisszeilen gibt, natürlich lassen sich die leicht entfernen:
SELECT DISTINCT w.w_id AS "w.w_id", g.lt_id AS "g.lt_id" , lt.lt_id AS "lt.lt_id", l.l_id AS "l.l_id", 
	w.name AS "w.name", w.flaeche AS "w.flaeche", w.wuestenart AS "w.wuestenart", l.name AS "l.name" 
FROM geo_wueste AS g, wueste AS w, landesteil AS lt, land AS l 
WHERE w.w_id = g.lt_id AND g.lt_id = lt.lt_id
AND lt.l_id = l.l_id
# Außerdem will ich diesen Zusammenhang für alle 30 Wüsten angezeigt haben, jetzt sind es aber plötzlich nur noch 2
;
SELECT DISTINCT w.w_id AS "w.w_id", g.lt_id AS "g.lt_id" , lt.lt_id AS "lt.lt_id", l.l_id AS "l.l_id", 
	w.name AS "w.name", w.flaeche AS "w.flaeche", w.wuestenart AS "w.wuestenart", l.name AS "l.name" 
FROM geo_wueste AS g, wueste AS w, landesteil AS lt, land AS l 
WHERE w.w_id = g.lt_id AND g.lt_id = lt.lt_id AND lt.l_id = l.l_id
GROUP BY ROLLUP(w.w_id, g.lt_id, lt.lt_id, l.l_id,w.name,w.flaeche,w.wuestenart,l.name)
ORDER BY w.w_id, g.lt_id, lt.lt_id, l.l_id,w.name,w.flaeche,w.wuestenart,l.name
# Ich glaube wir müssen da mit UNION arbeiten ... nee ... oder wie?
;
SELECT DISTINCT w.w_id AS "w.w_id", g.lt_id AS "g.lt_id" , lt.lt_id AS "lt.lt_id", l.l_id AS "l.l_id", 
	w.name AS "w.name", w.flaeche AS "w.flaeche", w.wuestenart AS "w.wuestenart", l.name AS "l.name" 
FROM geo_wueste AS g, wueste AS w, landesteil AS lt, land AS l 
WHERE w.w_id = g.lt_id 

'
, tbl => 'wueste' );

###############################



###############################
$aufgaben_list[] = array( nr =>  '<b><u>4.4.5</u></b>' , 
text =>'Eine kleine Applikation
1. 	Implementieren Sie eine Webanfrageschnittstelle für DB2 (ähnlich der Schnittstelle
	die für die Vorlesung DBS I eingesetzt wird).
2. 	Falls noch nicht geschehen, erweitern Sie die Schnittstelle so, da. auch Insert-,
	Update- und Delete-Ausdrücke verarbeitet werden können. Geben Sie dabei als
	Ausgabe die Anzahl der veränderten bzw. eingefügten/gelöschten Tupel an.' 
, sql => 'SELECT * FROM BERG'
, tbl => 'STADT' );
###############################

#echo '<br>';
###############################
$aufgaben_list[] = array( nr =>  '<br><b><u>GROUP und DISTINCT</u></b>' , 
text =>'Und einmal ohne DISTINCT SELECT ...' ,
sql => "
SELECT * FROM berg WHERE gebirge < 'b' 
ORDER BY gebirge ASC
;
# DISTINCT entfernt doppelt Werte
SELECT DISTINCT gebirge FROM berg WHERE gebirge < 'b' 
;
# Obwohl hier die Ausgabe gleich wie die oben ist, gruppiert GROUP, 
# und ist damit nicht gleich DISTINCT' ,
SELECT gebirge FROM berg 
GROUP BY gebirge
;
".'
SELECT GEBIRGE , COUNT(name) AS "AnzahlBerge", MAX(HOEHE ) "Groester" ,MIN(HOEHE ) "Kleinster", 
	MAX(HOEHE )-MIN(HOEHE ) AS "Unterschied" 
FROM BERG 
	GROUP BY GEBIRGE 
	ORDER BY COUNT(name) DESC
;
# Hier sieht man zusätzlich eine Zeile die alle Gruppen zusammengefasst, als eine Gruppe behandelt.
SELECT 
GEBIRGE , COUNT(name) AS "AnzahlBerge", MAX(HOEHE ) "Groester" ,MIN(HOEHE ) "Kleinster", MAX(HOEHE )-MIN(HOEHE ) AS "Unterschied" 
FROM BERG 
GROUP BY ROLLUP(gebirge) 	ORDER BY COUNT(name) DESC
;
# ROLLUP GROUPING
# Jetzt keine Verwechslunksgefahr mehr durch bessere Benennung. (Vgl. Null-Werte)
SELECT GROUPING(GEBIRGE) AS "GROUPING(gebirge)" , 
	CASE GROUPING(gebirge) 
		WHEN 1 THEN \'(-alle Gebirge-)\' 
		ELSE gebirge 
	END 	AS "CASE"	, 
	GEBIRGE , COUNT(name) AS "AnzahlBerge", MAX(HOEHE ) "Groester" ,MIN(HOEHE ) "Kleinster", 
	MAX(HOEHE )-MIN(HOEHE ) AS "Unterschied" 
FROM BERG 
GROUP BY ROLLUP(gebirge) 	ORDER BY COUNT(name) DESC
;
# DISTINCT mehrspaltik</u></b>
# DISTINCT sorgt hier nur für einmalige Ergebniszeilen. Doppeltes wird gestrichen. 
# Vgl. einmal den SELECT nur über die Spalte GEBIRGE
SELECT gebirge, name FROM BERG         ORDER BY gebirge'
 , tbl => 'BERG' );
###############################



#echo '<br>';
$t1 = "SELECT gebirge FROM berg 	WHERE gebirge 	BETWEEN 'r' AND 't' \n";
$t2 = "\nSELECT gebirge FROM berg 	WHERE gebirge 	BETWEEN 's' AND 'u' 
ORDER BY gebirge ASC";
###############################
$aufgaben_list[] = array( nr =>  '<br><b><u>UNION(Vereinigung) INTERSECT(Schnittmenge) und EXCEPT(Minus)</u></b>' , 
text =>'T1 UNION T2
Vereinigung ohne doppelte Ergebnisse.
Erscheint z.B. Zeile A 3-mal in T1 und 2-mal in T2, erscheint sie nur 1-mal.' ,
sql => "
# T1 UNION T2
# Vereinigung ohne doppelte Ergebnisse.
# Erscheint z.B. Zeile A 3-mal in T1 und 2-mal in T2, erscheint sie nur 1-mal.
$t1 UNION $t2
;
# T1 UNION ALL T2
# Vereinigung. Erscheint z.B. Zeile A 3-mal in T1 und 2-mal in T2, erscheint sie sogar 5-mal.
$t1 UNION ALL $t2
;
# T1 INTERSECT T2
# Schnittmenge ohne doppelte Zeilen.
$t1 
INTERSECT 
$t2
;
# T1 INTERSECT ALL T2
# Schnittmenge mit bei der kleinere Häufigkeit (wie bei UNION ALL) erhalten bleibt.
$t1 
INTERSECT ALL 
$t2
;
# T2 EXCEPT T1
# Ergebnisse sind in T2, aber nicht in T1. Ohne doppelte Zeilen.
".str_replace('ORDER BY gebirge ASC','',$t2)." EXCEPT $t1
;
# T1 EXCEPT T2
# Ergebnisse sind in T1, aber nicht in T2. Ohne doppelte Zeilen.
$t1 
EXCEPT 
$t2
;
# T1 EXCEPT ALL T2
# Differenz. Erscheint z.B. Zeile A 3-mal in T1 und 2-mal in T2, erscheint sie genau 1-mal.
$t1 
EXCEPT ALL 
$t2

"
 , tbl => 'BERG' );
###############################

#echo '<br>';
###############################
$aufgaben_list[] = array( nr =>  '<br><b><u>LEFT|RIGHT|FULL OUTER JOIN</u></b>' , 
text =>'Der "normale" JOIN wird implizit im folgenden verwendet.' ,
sql => '
SELECT b.hoehe AS "b.hoehe", e.hoehe AS "e.hoehe" 
FROM ebene AS e, berg AS b 
WHERE CAST(e.hoehe/500 AS DECIMAL(9))  = CAST(b.hoehe/500 AS DECIMAL(9))
;
# JOIN
# Hier wurde JOIN explizit verwendet. Das Ergebnis ist das selbe.
SELECT b.hoehe AS "b.hoehe", e.hoehe AS "e.hoehe" 
FROM ebene AS e JOIN berg AS b
ON CAST(e.hoehe/500 AS DECIMAL(9))  = CAST(b.hoehe/500 AS DECIMAL(9))
;
# LEFT OUTER JOIN
# Zusätzlich nun auch Zeilen linken Tabelle, welche die WHERE - Bedingung nicht erfüllen.
SELECT l.hoehe AS "left.hoehe" , b.hoehe AS "b.hoehe" 
FROM ebene AS l LEFT OUTER JOIN berg AS b
ON CAST(l.hoehe/500 AS DECIMAL(9))  = CAST(b.hoehe/500 AS DECIMAL(9)) 
ORDER BY l.hoehe ASC
;
# RIGHT OUTER JOIN
# Zusätzlich nun auch Zeilen rechten Tabelle, 
# welche die WHERE - Bedingung nicht erfüllen.
SELECT right.hoehe AS "right.hoehe", e.hoehe AS "e.hoehe" 
FROM    ebene AS e   RIGHT OUTER JOIN     berg AS right
ON CAST(e.hoehe/500 AS DECIMAL(9))  = CAST(right.hoehe/500 AS DECIMAL(9)) 
ORDER BY e.hoehe ASC
;
# FULL OUTER JOIN
# Zusätzlich nun auch Zeilen beider Tabellen, welche die WHERE - Bedingung nicht erfüllen.
SELECT b.hoehe AS "b.hoehe", e.hoehe AS "e.hoehe" 
FROM ebene AS e FULL OUTER JOIN berg AS b
ON CAST(e.hoehe/500 AS DECIMAL(9))  = CAST(b.hoehe/500 AS DECIMAL(9)) 
ORDER BY e.hoehe ASC
'	 , tbl => 'BERG' );
###############################

#echo '<br>';
###############################
$aufgaben_list[] = array( nr =>  '<u>Zufallszahlen - CREATE INSERT <b>WITH</b></u>' , 
text =>'Tabelle mit Zufallszahlen erstellen.' ,
sql => '
CREATE TABLE zahlen(zähler Integer, zufall Integer)
# Tabelle ist erstellt
;
INSERT INTO zahlen(zähler, zufall) 
	WITH temporäre_Sicht(n) AS (
		VALUES(1) 
		UNION ALL 
		SELECT n+1 FROM temporäre_Sicht WHERE n<2 
	) 
	SELECT n, integer(rand()*10) FROM temporäre_Sicht
# Das hier ist der interessante Teil gewesen.
;
SELECT * FROM zahlen;
# Man sieht es hat geklappt.
DROP TABLE zahlen;
'	 , tbl => 'zahlen' );
###############################

###############################
$aufgaben_list[] = array( nr =>  '<u>2.3.4.2 <b>WITH</b></u>' , 
text =>'2.3.4.2
Geben Sie alle Paare von Ländern mit Meeresküste in Europa aus, die an die gleiche Menge von Meeren angrenzen.
Beispiel:
[Belgien, Niederlande]
da M_Belgien = M_Niederlande = {Nordsee}
[Belgien, Spanien] sollte nicht auftauchen,
da M_Spanien = {Atlantik, Mittelmeer}
' ,
sql => '
# Alle Länder-Paare innerhalb Europa:
WITH europa (l_id, l_name) AS
( 
	SELECT l.l_id AS "l.l_id", l.name AS "l.name"  
		FROM land l, umfasst u, kontinent k 
		WHERE   l.l_id = u.l_id   AND   u.k_id = k.k_id   AND   k.name = \'Europa\'
) 
SELECT l1.name "l1.name", l2.name "l2.name" 
	FROM benachbart b, land l1, land l2 
	WHERE b.l_id1 = l1.l_id AND b.l_id2 = l2.l_id
	AND b.l_id1 
		IN (SELECT l_id FROM europa)
		AND b.l_id2 
			IN (SELECT l_id FROM europa)
# ...57 Paare
;
# Zuordnung Land und Meer:

WITH land_meer (l_id, name) AS ( 
	SELECT l.l_id, g.name 
		FROM land l, landesteil lt, geo_gewaesser gg, gewaesser g, meer m 
		WHERE l.l_id = lt.l_id
		AND lt.lt_id = gg.lt_id
		AND gg.g_id = g.g_id
		AND g.g_id = m.g_id 
		GROUP BY l.l_id, g.name
) 
SELECT l.name "l.name", m.name "m.name"
	FROM land_meer m, land l 
	WHERE m.l_id = l.l_id

;
# Eine mögliche Lösung:

WITH europa (l_id, l_name) AS ( 
	SELECT l.l_id, l.name 
		FROM land l, umfasst u, kontinent k 
		WHERE l.l_id = u.l_id		AND u.k_id = k.k_id		AND k.name = \'Europa\'
), europa_paare (l_id1, l_id2) AS ( 
	SELECT l_id1, l_id2 
		FROM benachbart b 
		WHERE b.l_id1 IN ( 
			SELECT l_id FROM europa 
		)AND b.l_id2 IN (
			SELECT l_id FROM europa
		)
), land_meer (l_id, name) AS ( 
	SELECT l.l_id, g.name 
		FROM land l, landesteil lt, geo_gewaesser gg, gewaesser g, meer m 
		WHERE l.l_id = lt.l_id		AND lt.lt_id = gg.lt_id
		AND gg.g_id = g.g_id		AND g.g_id = m.g_id 
		GROUP BY l.l_id, g.name
) 
SELECT l1.l_id "l1.l_id",  l1.name "l1.name", l2.l_id "l2.l_id", l2.name "l2.name"
	FROM land l1, land l2, europa_paare e 
	WHERE l1.l_id = e.l_id1  	AND    l2.l_id = e.l_id2
	AND NOT EXISTS ( 
		SELECT m1.name FROM land_meer m1 
			WHERE m1.l_id = e.l_id1
		EXCEPT ALL 
		SELECT m2.name FROM land_meer m2 
			WHERE m2.l_id = e.l_id2
	)
	AND NOT EXISTS ( 
		SELECT m3.name 
			FROM land_meer m3 
			WHERE m3.l_id = e.l_id2
		EXCEPT ALL 
		SELECT m4.name FROM land_meer m4 
			WHERE m4.l_id = e.l_id1
	)
	AND  EXISTS ( 
		SELECT m5.name FROM land_meer m5 
			WHERE m5.l_id = e.l_id1
	)
'	 , tbl => 'zahlen' );
###############################

$aufgaben_list[] = array( nr =>  '<u>2.3.4.3.(a) <b>WITH</b></u> billig von Kapstadt nach New York' , 
text =>'2.3.4.3.(a)
Finden Sie die günstigste Flugverbindung von Kapstadt nach New York.' ,
sql => '
# Theoretisch muss man alle möglichen Verbindungen durchrechnen, das ist bei einem rückgekoppelten Netz nicht möglich -> 
# Endlosschleife.
# Daher wird hier die Zahl der Umstiegen wird auf 5 gesetzt.

WITH flug_ny ( flug_id, flug_strecke, fh_strecke, umstieg, abflugfh_id, ankunftfh_id, preis, sum_preis) AS (
	( SELECT f.flug_id, 
		CAST(CAST(f.flug_id AS char(3)) AS varchar(100)), 
		CAST(CAST(f.abflugfh_id AS char(3)) AS varchar(100)) || \'->\' || CAST(CAST(f.ankunftfh_id AS char(3)) AS varchar(100)), 
		1, f.abflugfh_id, f.ankunftfh_id, f.preis, f.preis 
		FROM flug f, flughafen fh, stadt s 
		WHERE f.abflugfh_id = fh.fh_id  AND  fh.s_id = s.s_id AND s.name = \'Kapstadt\'
	) 
	UNION ALL 
	( SELECT f1.flug_id, 
		fny.flug_strecke || \'->\' || CAST(CAST(f1.flug_id AS char(3)) AS varchar(100)), 
		fny.fh_strecke || \'->\' || CAST(CAST(f1.ankunftfh_id AS char(3)) AS varchar(100)), 
		fny.umstieg+1, f1.abflugfh_id, f1.ankunftfh_id, f1.preis, f1.preis + fny.sum_preis 
		FROM flug f1, flug_ny fny  
		# , flughafen fh1, stadt s1 
		WHERE f1.abflugfh_id = fny.ankunftfh_id 
		# _and f1.ankunftfh_id = fh1.fh_id AND fh1.s_id = s1.s_id AND s1.name = \'New_York\'
		AND umstieg < 5
	)
) 
SELECT flug_strecke, fh_strecke, umstieg, code AS ankunft_fh, sum_preis AS gesamt_preis 
	FROM flug_ny fny2, flughafen fh2, stadt s2 
	WHERE  fny2.ankunftfh_id = fh2.fh_id AND fh2.s_id = s2.s_id AND s2.name = \'New_York\' 
	AND sum_preis = 
	( SELECT MIN(sum_preis) 
		FROM flug_ny fny2, flughafen fh2, stadt s2 
		WHERE  fny2.ankunftfh_id = fh2.fh_id AND fh2.s_id = s2.s_id AND s2.name = \'New_York\' 
	)


# FLUG_STRECKE	FH_STRECKE	UMSTIEG	ANKUNFT_FH	GESAMT_PREIS
# 34 ->7  	1  ->2  ->3  	2	JFK		1673.00

'	 , tbl => 'zahlen' );

$aufgaben_list[] = array( nr =>  '<u>2.3.4.3.(b) <b>WITH</b></u> wenig Umstiege von Frankfurt nach Los Angeles' , 
text =>'2.3.4.3.(b)
Finden Sie die Verbindung von Frankfurt nach Los Angeles mit den wenigsten
Umstiegen. Falls mehrere Alternativen bestehen, wählen Sie die günstigste.' ,
sql => '
# Die Abbruchbedingung aus der Rekursion kann man hier direkt angaben, da hier die Verbindung mit den wenigsten Umstiegen gesucht wird. 

WITH flug_ny ( flug_id, flug_strecke, fh_strecke, umstieg, abflugfh_id, ankunftfh_id, preis, sum_preis) AS (
( 	SELECT f.flug_id, 
		CAST(CAST(f.flug_id AS char(3)) AS varchar(100)), 
		CAST(CAST(f.abflugfh_id AS char(3)) AS varchar(100)) || \'->\' 
			|| CAST(CAST(f.ankunftfh_id AS char(3)) AS varchar(100)), 
		1, f.abflugfh_id, f.ankunftfh_id, f.preis, f.preis 
		FROM flug f, flughafen fh, stadt s 
		WHERE f.abflugfh_id = fh.fh_id AND fh.s_id = s.s_id AND s.name = \'Frankfurt\'
	) 
	UNION ALL ( 
	SELECT f1.flug_id, 
		fny.flug_strecke || \'->\' || CAST(CAST(f1.flug_id AS char(3)) AS varchar(100)), 
		fny.fh_strecke || \'->\' || CAST(CAST(f1.ankunftfh_id AS char(3)) AS varchar(100)), 
		fny.umstieg+1, f1.abflugfh_id, f1.ankunftfh_id, f1.preis, f1.preis + fny.sum_preis 
		FROM flug f1, flug_ny fny, flughafen fh1, stadt s1 
		WHERE f1.abflugfh_id = fny.ankunftfh_id 
		AND f1.ankunftfh_id = fh1.fh_id AND fh1.s_id = s1.s_id AND s1.name = \'Los_Angeles\'
		#AND umstieg < 5 */
	)
) 
SELECT flug_strecke, fh_strecke, umstieg, code AS ankunft_fh, sum_preis AS gesamt_preis 
	FROM flug_ny fny2, flughafen fh2, stadt s2 
	WHERE  fny2.ankunftfh_id = fh2.fh_id AND fh2.s_id = s2.s_id AND s2.name = \'Los_Angeles\' 
	AND sum_preis = ( 
		SELECT MIN(sum_preis) 
			FROM flug_ny fny2, flughafen fh2, stadt s2 
			WHERE  fny2.ankunftfh_id = fh2.fh_id AND fh2.s_id = s2.s_id AND s2.name = \'Los_Angeles\' 
)

# FLUG_STRECKE	FH_STRECKE	UMSTIEG	ANKUNFT_FH	GESAMT_PREIS
# 7  ->2  	2  ->3  ->4  	2	LAX		1920.00

'	 , tbl => 'zahlen' );









##echo '<pre>'; var_export($aufgaben_list); #echo '/<pre>';










?>