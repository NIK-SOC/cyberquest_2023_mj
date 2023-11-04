#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <signal.h>

#define MAX_COSTUMES 10

typedef struct
{
    char name[50];
    double price;
} Costume;

typedef struct
{
    Costume items[MAX_COSTUMES];
    int itemCount;
} Basket;

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

void printMainMenu()
{
    printf("\n==== Costumery Shop Main Menu ====\n\n");
    printf("Available Costumes to Buy:\n");
    char *costumeNames[MAX_COSTUMES] = {
        "Hacktoplasma Jacket",
        "High heels for skeletons",
        "Zombie Tuxedo",
        "Witch's Hat",
        "Pirate Costume",
        "Vampire Cape",
        "Ghost Sheet",
        "Frankenstein's Monster Outfit",
        "Mummy Wrappings",
        "Werewolf Costume"};
    printf("%-4s %-30s %-10s\n", "No.", "Costume", "Price");

    for (int i = 0; i < MAX_COSTUMES; i++)
    {
        printf("%-4d %-30s $%8.2f\n", i + 1, costumeNames[i], strlen(costumeNames[i]) * 10.5);
    }
    printf("\nActions:\n");
    printf("1. Buy Costume\n");
    printf("2. View Basket\n");
    printf("3. Exit\n");
    printf("===================================\n");
}

int getDiscountCard()
{
    char hasDiscountCard[5];
    while (1)
    {
        printf("Do you have a CreepyChain:tm: discount card? (yes/no): ");
        if (fgets(hasDiscountCard, sizeof(hasDiscountCard), stdin) == NULL)
        {
            break;
        }
        if (strcmp(hasDiscountCard, "yes\n") == 0 || strcmp(hasDiscountCard, "no\n") == 0)
        {
            break;
        }
    }
}

int getIban()
{
    char iban[100];
    printf("Enter your IBAN number: ");
    fflush(stdout);
    read(0, iban, 0x100);
}

int main()
{
    Basket userBasket;
    userBasket.itemCount = 0;

    int choice;
    char input[100];

    ignore_me_init_signal();
    init_buffering();

    while (1)
    {
        printMainMenu();
        printf("Enter your choice (1-3): ");
        if (fgets(input, sizeof(input), stdin) != NULL)
        {
            choice = atoi(input);
            switch (choice)
            {
            case 1:
                if (userBasket.itemCount >= MAX_COSTUMES)
                {
                    printf("Your basket is full. You can't buy more costumes.\n");
                    break;
                }
                int costumeNumber;
                char iban[100];

                printf("Enter the number of the costume you want to buy (1-%d): ", MAX_COSTUMES);
                if (fgets(input, sizeof(input), stdin) != NULL)
                {
                    costumeNumber = atoi(input);
                    if (costumeNumber < 1 || costumeNumber > MAX_COSTUMES)
                    {
                        printf("Invalid costume number.\n");
                        break;
                    }
                }
                else
                {
                    break;
                }

                getDiscountCard();
                getIban();

                strcpy(userBasket.items[userBasket.itemCount].name, "Costume Name");
                userBasket.items[userBasket.itemCount].price = costumeNumber * 10.0;
                userBasket.itemCount++;

                printf("Costume added to your basket.\n");
                break;

            case 2:
                printf("\n==== Your Basket ====\n");
                for (int i = 0; i < userBasket.itemCount; i++)
                {
                    printf("%d. %s - $%.2f\n", i + 1, userBasket.items[i].name, userBasket.items[i].price);
                }
                printf("\nActions:\n");
                printf("1. Delete Costume\n");
                printf("2. Back to Main Menu\n");

                printf("Enter your choice (1-2): ");
                if (fgets(input, sizeof(input), stdin) != NULL)
                {
                    choice = atoi(input);
                    if (choice == 1)
                    {
                        printf("Enter the number of the costume you want to delete: ");
                        if (fgets(input, sizeof(input), stdin) != NULL)
                        {
                            int deleteNumber = atoi(input);
                            if (deleteNumber >= 1 && deleteNumber <= userBasket.itemCount)
                            {
                                for (int i = deleteNumber - 1; i < userBasket.itemCount - 1; i++)
                                {
                                    userBasket.items[i] = userBasket.items[i + 1];
                                }
                                userBasket.itemCount--;
                                printf("Costume deleted from your basket.\n");
                            }
                            else
                            {
                                printf("Invalid costume number.\n");
                            }
                        }
                    }
                    else if (choice == 2)
                    {
                        break;
                    }
                }
                break;

            case 3:
                printf("Goodbye!\n");
                exit(0);

            default:
                printf("Invalid choice. Please enter a valid option.\n");
                break;
            }
        }
    }

    return 0;
}

void win()
{
    system("/bin/sh");
}
