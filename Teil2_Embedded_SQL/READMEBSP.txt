ACHTUNG!!: im Programm beispiel.sqc muessen alle
Vorkommen von dbprakt9 durch den richtigen, eigenen
Datenbanknamen ersetzt werden.

Dies gilt auch fuer die folgenden Hinweise zum
Uebersetzen (auch ggf. im Makefile aendern).


Man kann das ganze per Hand uebersetzen, indem
man nacheinander folgende Zeilen eingibt (Abfolge
enspricht Abbildung 6 im Skript):


db2 connect to dbprakt9
db2 prep beispiel.sqc
db2 terminate
gcc -I${INSTHOME}/sqllib/include -c beispiel.c
gcc -o beispiel beispiel.o -ldb2 -L${INSTHOME}/sqllib/lib


Es ist auch moeglich, das ganze durch Aufrufen des
mitgelieferten Makefiles zu uebersetzen:

make -f MakeBsp beispiel
