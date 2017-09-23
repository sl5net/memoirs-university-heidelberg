
DB2INCL= /home/db2inst2/sqllib/include
DB2LIB= /home/db2inst2/sqllib/lib

CC= g++
CCFLAGS= -I$(DB2INCL)
LDLIBS= -ldb2
LDFLAGS= -L$(DB2LIB)


aufgabe2 : aufgabe2.o
	$(CC) -o aufgabe2 aufgabe2.o $(LDFLAGS) $(LDLIBS)

aufgabe2.o : aufgabe2.c
	$(CC) -c $(CCFLAGS) aufgabe2.c

aufgabe2.c : aufgabe2.sqc
	db2 connect to test 
	db2 prep aufgabe2.sqc 
	db2 terminate

clean : 
	rm aufgabe2 aufgabe2.o aufgabe2.c

