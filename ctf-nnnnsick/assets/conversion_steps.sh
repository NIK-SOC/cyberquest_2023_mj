ffmpeg -i ./flag_dictated.mp3 ./flag_dictated.wav
ffmpeg -i flag_dictated.wav -f g722 -ar 16000 ./flag_converted.g722