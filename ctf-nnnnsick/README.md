# nnnnsick

A painful <ins>forensic challenge</ins> where the players are given an [xz file](out/challenge.xz) and some description:

> Upon modifying an open-source library for our company's needs for the super secret interworkings the developer caught a ransom that corrupted all data present on their developer PC. All files produced by this library were lost. The only thing we have now is a dump of all variable set calls since this was successfully restored from the RAM. Think of this dump like placing `printf` calls at every single variable update. Altough the developer was so desperate that he quit the other day, I trust you to find this open-source library and recover the original file. All I know is that it was written in C. Probably a decoder? How would I know, I am just a CEO... The company awaits for a hero!

<details>
<summary>Writeup (Spoiler)</summary>

If we extract the file, we can see a simple text file with this content repeating with different values:

```
code: 250
wd1: 58
ihigh: 3
wd2: 1040
wd1: 14
wd2: 1
rlow: 1
wd2: 1200
dlowt: 1
wd2: 1
wd1: 0
wd1: -30
wd1: 0
s->band[0].nb: 0
wd1: 0
wd2: 8
wd3: 8
s->band[0].det: 32
s->band[0].s: 0
amp[0]: 2
```

So we know that it's the variable states of an open-source C program. We can then look some longer variable name up in github search: https://github.com/search?q=s-%3Eband%5B0%5D.det&type=code

And bump into G722 codec codes or we can just assume that the amp[0]: 2 is raw audio which we can extract from the file and reconstruct the original output. I will do the latter with a not too perfect [poc script](poc.py). It doesn't handle cases when the last bit isn't 10 chunks long, but it's enough for this challenge to get the flag since the input is perfectly aligned to 10.

If we run `python3 ./poc.py`, it extracts the audio to `out/recovered_audio.wav`. Play that, listen to the audio and you will hear the flag.
</details>