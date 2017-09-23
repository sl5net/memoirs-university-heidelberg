
DB2INCL= /home/db2inst2/sqllib/include
DB2LIB= /home/db2inst2/sqllib/lib

CC= gcc
CCFLAGS= -I$(DB2INCL) -fpic
LDLIBS= -ldb2
LDFLAGS= -L$(DB2LIB) -shared


aufgabe4 : aufgabe4.o
	$(CC) -o aufgabe4 aufgabe4.o $(LDFLAGS) $(LDLIBS)

aufgabe4.o : aufgabe4.c
	$(CC) -c $(CCFLAGS) aufgabe4.c

clean : 
	rm aufgabe4 aufgabe4.o

