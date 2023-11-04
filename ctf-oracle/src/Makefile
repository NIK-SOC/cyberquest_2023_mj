CC = gcc
CFLAGS = -Wl,-z,relro -no-pie -fstack-protector -Wno-implicit-function-declaration -Wno-builtin-declaration-mismatch

all: main

main: main.c
	$(CC) $(CFLAGS) -o oracle $<
	strip oracle

clean:
	rm -f oracle