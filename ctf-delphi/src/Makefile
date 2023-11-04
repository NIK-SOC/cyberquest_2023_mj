CC = gcc
CFLAGS = -Wl,-z,relro -fstack-protector -Wno-implicit-function-declaration -Wno-builtin-declaration-mismatch

all: main

main: main.c
	$(CC) $(CFLAGS) -o delphi $<
	strip delphi

clean:
	rm -f delphi