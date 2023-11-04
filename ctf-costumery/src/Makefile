CC = gcc
CFLAGS = -fno-stack-protector -Wl,-z,relro -no-pie -z execstack -Wno-implicit-function-declaration -Wno-builtin-declaration-mismatch

all: main

main: main.c
	$(CC) $(CFLAGS) -o customery $<
	strip customery

clean:
	rm -f customery
