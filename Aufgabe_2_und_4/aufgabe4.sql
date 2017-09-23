SQL Statements für Aufgabe 4
============================

1) Benutzerdefinierter Datentyp "coordinates" definieren

create distinct type coordinates as blob(16);


2) Die entsprechenden Funktionen gemäss Aufgabestellung erstellen.
   Die Implementierung der Funktionen ist auf ".../aufgabe4.c" zu finden

create function coordinates(double, double)
returns coordinates
external name '/home/kwong/heidelberg/db/Praktikum/Teil4/aufgabe4!double2coordinates'
variant
no sql
no external action
language c
fenced
parameter style db2sql
scratchpad
final call;

create function coordinates(coordinates)
returns varchar(40)
external name '/home/kwong/heidelberg/db/Praktikum/Teil4/aufgabe4!coordinates2Char'
variant
no sql
no external action
language c
fenced
parameter style db2sql
scratchpad
final call;

create function distance(coordinates, coordinates)
returns double
external name '/home/kwong/heidelberg/db/Praktikum/Teil4/aufgabe4!distance'
variant
no sql
no external action
language c
fenced
parameter style db2sql
scratchpad
final call;


3) Eine Kopie der Tabelle "stadt" mit dem Benutzerdefinierten Datentyp erzeugen

create table stadt2 
(
  s_id smallint not null,
  name char(25) not null,
  einwohner int,
  coordinate coordinates,
  primary key(s_id)
);

insert into stadt2 
select s_id, name, einwohner, coordinates(double(laenge),double(breite))
from stadt;
