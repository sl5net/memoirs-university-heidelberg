# memoirs-university-heidelberg
memoirs university heidelberg

please download: 
aufgabe4.4.5_v0503082041.php%40aufgabennr%3D15.htm
https://raw.githubusercontent.com/sl5net/memoirs-university-heidelberg/master/Teil3/php-loes/05031620/aufgabe4.4.5_v0503082041.php%40aufgabennr%3D15.htm
was created 08.03.2005 at 8:41pm :)

and BTW its using this SQL-Command:

# Die Abbruchbedingung aus der Rekursion kann man hier direkt angaben, da hier die Verbindung mit den wenigsten Umstiegen gesucht wird. 

WITH flug_ny ( flug_id, flug_strecke, fh_strecke, umstieg, abflugfh_id, ankunftfh_id, preis, sum_preis) AS (
( 	SELECT f.flug_id, 
		CAST(CAST(f.flug_id AS char(3)) AS varchar(100)), 
		CAST(CAST(f.abflugfh_id AS char(3)) AS varchar(100)) || '->' 
			|| CAST(CAST(f.ankunftfh_id AS char(3)) AS varchar(100)), 
		1, f.abflugfh_id, f.ankunftfh_id, f.preis, f.preis 
		FROM flug f, flughafen fh, stadt s 
		WHERE f.abflugfh_id = fh.fh_id AND fh.s_id = s.s_id AND s.name = 'Frankfurt'
	) 
	UNION ALL ( 
	SELECT f1.flug_id, 
		fny.flug_strecke || '->' || CAST(CAST(f1.flug_id AS char(3)) AS varchar(100)), 
		fny.fh_strecke || '->' || CAST(CAST(f1.ankunftfh_id AS char(3)) AS varchar(100)), 
		fny.umstieg+1, f1.abflugfh_id, f1.ankunftfh_id, f1.preis, f1.preis + fny.sum_preis 
		FROM flug f1, flug_ny fny, flughafen fh1, stadt s1 
		WHERE f1.abflugfh_id = fny.ankunftfh_id 
		AND f1.ankunftfh_id = fh1.fh_id AND fh1.s_id = s1.s_id AND s1.name = 'Los_Angeles'
		#AND umstieg < 5 */
	)
) 
SELECT flug_strecke, fh_strecke, umstieg, code AS ankunft_fh, sum_preis AS gesamt_preis 
	FROM flug_ny fny2, flughafen fh2, stadt s2 
	WHERE  fny2.ankunftfh_id = fh2.fh_id AND fh2.s_id = s2.s_id AND s2.name = 'Los_Angeles' 
	AND sum_preis = ( 
		SELECT MIN(sum_preis) 
			FROM flug_ny fny2, flughafen fh2, stadt s2 
			WHERE  fny2.ankunftfh_id = fh2.fh_id AND fh2.s_id = s2.s_id AND s2.name = 'Los_Angeles' 
)

# FLUG_STRECKE	FH_STRECKE	UMSTIEG	ANKUNFT_FH	GESAMT_PREIS
# 7  ->2  	2  ->3  ->4  	2	LAX		1920.00
                                                                             ; # focus hier ALT+2
# Die Standard SELECTS * oben, erhällt man auch über
# ALT+(Erster Buchstabe der Tabelle.)+ENTER 
# odbc_num_rows: 1 ,  odbc_num_fields: 5
