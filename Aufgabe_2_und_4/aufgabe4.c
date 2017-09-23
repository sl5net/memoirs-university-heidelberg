
#include <string.h>
#include <sqlenv.h>
#include <sqludf.h>
#include <math.h>

#define ERDRADIUS 6371.032
#define PI 3.141592654

/*
Aus teil2.pdf
3.2.2 Eine kleine Applikation
1. Schreiben Sie ein C-Programm, das als Eingabe zwei Stadtenamen erhalt und die
Entfernung (Luftlinie) zwischen diesen Stadten berechnet.
Nehmen Sie fur diese Aufgabe an, da die Erde eine Kugel ist (um das ganze zu
vereinfachen). Rechnen Sie zunachst die Langen-/ Breitengradangaben mit Hilfe
...
3.2.2
2.          
Erweitern Sie das Programm um einen Eingabeteil der nichtvorhandene Stadte
einfugt (INSERT) bzw. nichtvorhandene Koordinatenangaben andert (UPDATE).
          
*/
typedef struct {
  unsigned long length;
  double breite;
  double laenge;
} coordinate_struct;

double rad(double x) {
  return x * ((2 * PI) / 360);
}

void double2coordinates(
	     SQLUDF_DOUBLE* inLaenge,
         SQLUDF_DOUBLE* inBreite,
	     SQLUDF_BLOB* outCoordinate,
	     short* inLaengeNull,
	     short* inBreiteNull,
	     short* outNull,
	     char* sqlstate,
	     char* fnName,
	     char* specificName,
	     char* message,
	     struct sqludf_scratchpad* scratchpad,
	     long* calltype) {

  coordinate_struct *pCoordinate;

  pCoordinate = (coordinate_struct*) outCoordinate;

  if(*calltype == -1) {
    /* erster Aufruf */
  }

  if(*calltype == 1) {
    /* letzter Aufraeumaufruf */
  }

  if(*calltype < 1) {
    /* erster oder mittlerer Aufruf */

    if (*inLaengeNull < 0 || *inBreiteNull < 0) {
      *outNull = -1;
      pCoordinate->laenge = 0.0;
      pCoordinate->breite = 0.0;
      return;
    }

    *outNull = 0;
    pCoordinate->laenge = *inLaenge;
    pCoordinate->breite = *inBreite;
  }
}

void coordinates2Char(
	     SQLUDF_BLOB* inCoordinate,
         SQLUDF_VARCHAR* outCoordinate,
	     short* inNull,
	     short* outNull,
	     char* sqlstate,
	     char* fnName,
	     char* specificName,
	     char* message,
	     struct sqludf_scratchpad* scratchpad,
	     long* calltype) {

  int laenge1;
  int breite1;
  char tmpstr[100];
  char laenge2str[3], laenge3str[3];
  char breite2str[3], breite3str[3];
  char laengeRichtung[2], breiteRichtung[2];

  coordinate_struct *pCoordinate;

  pCoordinate = (coordinate_struct*) inCoordinate;

  if(*calltype == -1) {
    /* erster Aufruf */
  }

  if(*calltype == 1) {
    /* letzter Aufraeumaufruf */
  }

  if(*calltype < 1) {
    /* erster oder mittlerer Aufruf */

    if (*inNull < 0 ) {
      *outNull = -1;
      strcpy(outCoordinate, "");;
      return;
    }

    if (pCoordinate->laenge < 0.0)
      strcpy(laengeRichtung, "W");
    else
      strcpy(laengeRichtung, "O");

    sprintf(tmpstr, "%010.4f", pCoordinate->laenge);
    laenge2str[0] = tmpstr[6];
    laenge2str[1] = tmpstr[7];
    laenge2str[2] = '\0';
    laenge3str[0] = tmpstr[8];
    laenge3str[1] = tmpstr[9];
    laenge3str[2] = '\0';

    if (pCoordinate->breite < 0.0)
      strcpy(breiteRichtung, "S");
    else
      strcpy(breiteRichtung, "N");

    sprintf(tmpstr, "%010.4f", pCoordinate->breite);
    breite2str[0] = tmpstr[6];
    breite2str[1] = tmpstr[7];
    breite2str[2] = '\0';
    breite3str[0] = tmpstr[8];
    breite3str[1] = tmpstr[9];
    breite3str[2] = '\0';

    laenge1 = (int) abs(pCoordinate->laenge);
    breite1 = (int) abs(pCoordinate->breite);

    *outNull = 0;
    sprintf(outCoordinate, "%d %s'%s''%s / %d %s'%s''%s",
      laenge1, laenge2str, laenge3str, laengeRichtung,
      breite1, breite2str, breite3str, breiteRichtung);
  }
}

void distance(
	     SQLUDF_BLOB* inCoordinate1,
	     SQLUDF_BLOB* inCoordinate2,
         SQLUDF_DOUBLE* outDistance,
	     short* in1Null,
	     short* in2Null,
	     short* outNull,
	     char* sqlstate,
	     char* fnName,
	     char* specificName,
	     char* message,
	     struct sqludf_scratchpad* scratchpad,
	     long* calltype) {

  double x1, x2, y1, y2, z1, z2;

  coordinate_struct *pCoordinate1, *pCoordinate2;

  pCoordinate1 = (coordinate_struct*) inCoordinate1;
  pCoordinate2 = (coordinate_struct*) inCoordinate2;

  if(*calltype == -1) {
    /* erster Aufruf */
  }

  if(*calltype == 1) {
    /* letzter Aufraeumaufruf */
  }

  if(*calltype < 1) {
    /* erster oder mittlerer Aufruf */

    if (*in1Null < 0 || *in2Null < 0) {
      *outNull = -1;
      *outDistance = 0.0;
      return;
    }

    x1 = cos(rad(pCoordinate1->breite)) * cos(rad(pCoordinate1->laenge));
    x2 = cos(rad(pCoordinate2->breite)) * cos(rad(pCoordinate2->laenge));
    y1 = cos(rad(pCoordinate1->breite)) * sin(rad(pCoordinate1->laenge));
    y2 = cos(rad(pCoordinate2->breite)) * sin(rad(pCoordinate2->laenge));
    z1 = sin(rad(pCoordinate1->breite));
    z2 = sin(rad(pCoordinate2->breite));

    *outNull = 0;
    *outDistance = acos(x1*x2 + y1*y2 + z1*z2) * ERDRADIUS;
  }

}
