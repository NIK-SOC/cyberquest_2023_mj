#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <signal.h>
#include <sys/random.h>
#include <unistd.h>

void init_buffering()
{
    setvbuf(stdout, NULL, _IONBF, 0);
    setvbuf(stdin, NULL, _IONBF, 0);
    setvbuf(stderr, NULL, _IONBF, 0);
}

void kill_on_timeout(int sig)
{
    if (sig == SIGALRM)
    {
        printf("[!] Anti DoS Signal. Patch me out for testing.");
        _exit(0);
    }
}

void ignore_me_init_signal()
{
    signal(SIGALRM, kill_on_timeout);
    alarm(60);
}

void input(char *output) {
    char buffer[1288];

    fgets(buffer, 1280, stdin);
    memcpy(output, buffer, strlen(buffer));
}

void win() {
    FILE *fp;
    char *line = NULL;
    size_t len = 0;
    ssize_t read;

    srand(time(NULL));

    fp = fopen("flag.txt", "r");
    if (fp == NULL)
        printf("Error: flag.txt not found. Contact an admin.\n"),
        exit(EXIT_FAILURE);
    while ((read = getline(&line, &len, fp)) != -1)
        printf("%s", line);
    fclose(fp);
    if (line)
        free(line);
    return;
}

int main()
{
    char *userinput;
    char *random;
    char userinput2[42];
    int iscorrect;

    ignore_me_init_signal();
    init_buffering();

    userinput = malloc(16);
    random = malloc(32);
    memset(random, 0, 32);
    getrandom(random, 32, 0);
    for (int i = 0; i < 32; i++)
    {
        if (random[i] == '\0' || random[i] == '\n')
            random[i] = 42;
    }
    while (1) {
        if (iscorrect == 1) {
            printf("You are a true mind reader.\n");
            win();
            return 0;
        }
        printf("Welcome to the Oracle of Delphi!\n");
        printf("Hic es forsit ut tuum futurum invenias.\n\n");
        printf("I will think of something bright and shiny. If you manage to think of the same thing, I will predict your future.\n");
        printf("If not, shall the gods have mercy on your soul.\n\n");
        printf("What shall I think of (ie What's my favorite instrument)? ");
        input(userinput);
        printf("So you asked: ");
        puts(userinput);
        printf("Okay, now that's a good one. Let me think...\n");
        sleep(2);
        printf("Got it. Any clue what it is I had in mind? ");
        fgets(userinput2, 33, stdin);
        if (strncmp(userinput2, random, 32) != 0) {
            printf("Nope, that's not it. You're doomed.\n");
            break;
        }
        printf("Just double checking... What did you say? \n");
        gets(userinput);
        printf("Woah... You got it. Interested in a job offer? We have some good java coffee.\n");
        free(random);
        free(userinput);
        iscorrect = 1;
    }
}