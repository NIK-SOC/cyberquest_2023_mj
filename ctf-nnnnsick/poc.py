import re
import struct
from subprocess import Popen, PIPE
import lzma

# needs ffmpeg to be installed and to be in PATH

amp_values = []
outlen = None

amp_pattern = re.compile(r"amp\[(\d+)\]: (-?\d+)")
outlen_pattern = re.compile(r"outlen: (\d+)")

with lzma.open("out/challenge.xz", "rt") as f:
    c_code_output = f.readlines()

for line in c_code_output:
    amp_match = amp_pattern.match(line)
    if amp_match:
        amp_values.append(int(amp_match.group(2)))

    outlen_match = outlen_pattern.match(line)
    if outlen_match:
        outlen = int(outlen_match.group(1))

if outlen is not None:
    with open("out/recovered_audio.raw", "wb") as audio_file:
        for value in amp_values:
            audio_file.write(struct.pack("<h", value))

print("Length of recovered Amp Values:", len(amp_values))
print("Outlen:", outlen)

process = Popen(
    [
        "ffmpeg",
        "-f",
        "s16le",
        "-ar",
        "8000",
        "-i",
        "out/recovered_audio.raw",
        "-acodec",
        "pcm_s16le",
        "-ar",
        "16000",
        "-y",
        "out/recovered_audio.wav",
    ],
    stdout=PIPE,
    stderr=PIPE,
)
stdout, stderr = process.communicate()
print(stdout.decode(), stderr.decode())
print("Done, check out/recovered_audio.wav")
