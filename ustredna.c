/*

	Program for simmulating of work of phone center 
	========================================
	
	Structure of code:
	
	1. includs
	2. declarations of structures
	3. declarations of functions heads
	
	4. main function
	5. run function
	6. parse function
	
	7. functions for three main instructions:
		new
		delete
		search
	8. stringToT9Translate
	
	=========================

	HOW IT WORKS:
	Program creates a phone center and call the run function with it.
	
	In loop, it reads line by line, and try to parse an instruction.
	In case of parsed instruction, program calls specified instruction with the center and read parametres.
	
	The loop ends, when EOF is read. All dyn. arrays are freed and program ends.
*/



/* 
	1,includes 
*/
#include <stdio.h>
#include <ctype.h>
#include <stdlib.h>
#include <string.h>



/* 
	2, declarations of structures 
*/
typedef struct user {
	char * number;
	char * name;
	char * T9;
} TUSER;

typedef struct center {
	TUSER * users;
	int nr;
	int max;
} TCENTER;



/* 
	3, declarations of functions heads
*/
void runCenter(TCENTER * center);
int parseAnotherLine(char ** numberPointer, char ** namePointer, char * lastChar);
int newUser(TCENTER * center, char * number, char * name);
int deleteUser(TCENTER * center, char * number);
int searchUser(TCENTER * center, char * pattern);
int stringToT9(char * name, char ** T9pointer);

		
	
	

/*
	4, main function
	
	Creates the center and call the run function.
	When done, it frees the inner arrays of center and ends.
*/
int main(void) {
	
	/*
		creates the main structure representing the call center; allocates and initialized inner variables
	*/
	TCENTER center;
	
	center.max = 2;
	center.users = (TUSER *)malloc(sizeof(TUSER)*center.max);
	center.nr = 0;
	
	// run the function for simmulating the working of call center 
	runCenter(&center);
	// the input ended -> end of program
	
	// just free all the inner dynamic arrays of USERS
	for(int i = 0; i < center.nr; i++) {
		free(center.users[i].name);
		center.users[i].name = NULL;
		free(center.users[i].number);
		center.users[i].number = NULL;
		free(center.users[i].T9);
		center.users[i].T9 = NULL;
	}
	
	// frees the inner array of USERS in center
	free(center.users);
	center.users = NULL;
	
	// everything is freed, we can end the program
	return 0;
}



/*
	5, run function
	The run function for simmulating of center work.
	
	In loop, it reads line and 
		call an instruction
		error -> writes error call
		EOF -> ends
*/
void runCenter(TCENTER * center) {
	
	printf("PBX configuration (+ = set, - = delete, ? = test, EOF = quit):\n"); // just the activation call
	
	char lastChar = '-'; // parse function gives the last char read when error
	char * number = NULL; // read number
	char * name = NULL; // when NEW instruction - read name
	
	int cont = 1; // aux var for loops
	int retCode = 0; // aux var for return code of parse function
	char c; // aux var
	
	
	
	// the main loop for reading and executing
	while(cont) {
		number = NULL;
		name = NULL;
		retCode = parseAnotherLine(&number, &name, &lastChar); // parse a line and returns the parametres for an instruction OR error OR EOF
		
		/*
			-2 -> error + EOF
			-1 -> EOF
			0 -> error
			1 -> new
			2 -> delete
			3 -> search 
		*/
		
			
		switch(retCode) {
			case -2:
				// some invalid text ending with EOF
				printf("INVALID COMMAND\n");
				cont = 0; // end of loop
				break;
				
			case -1:
				// EOF
				cont = 0; // end of loop
				break;
				
			case 0:
				printf("INVALID COMMAND\n");
				c = lastChar;
				
				while(1) { // error occured -> just skipp the rest of chars till NEWLINE or EOF
					if(c == '\n')
						break;
					
					if(c == EOF) {
						cont = 0;
						break;
					}
					
					c = getchar();
				}			
				
				break;
				
			case 1:
				// NEW instruction parsed
				if(number != NULL && name != NULL) {
					newUser(center, number, name);
				}
				number = NULL;
				name = NULL;
				break;
				
			case 2:
				// DELETE instruction parsed
				if(number != NULL) {
					deleteUser(center, number);
				}
				number = NULL;
				break;
				
			case 3:
				// SEARCH instruction parsed
				if(number != NULL) {
					searchUser(center, number);
				}
				number = NULL;
				break;
				
		}
		
		/*
		printf("Vypis:\n");
		for(int i = 0; i < center->nr; i++) {
			printf("%d. ", i);
			printf("%s ", center->users[i].name);
			printf("%s ", center->users[i].number);
			printf("%s", center->users[i].T9);
			printf("\n");
		}
		printf("\n");*/
		
	}
	
	// end of input -> end of program
	
}



/*
	6, parse function
	Try to parse line and return the arguments
		OR EOF
		OR error + last char
		(or mix of both)


	-2 -> error + EOF
	-1 -> EOF
	0 -> error
	1 -> new
	2 -> delete
	3 -> search 

*/ 
int parseAnotherLine(char ** numberPointer, char ** namePointer, char * lastChar) {
	
	char c = 0; // aux var for reading another char
	int cont = 1; // aux var used in loops
	
	char instructionChar = 0; // saves the type of instruction (read as a first char)
		
	
	/*
		1, reading the type of instruction
	*/
	
	c = getchar(); 

	if(c == EOF) // just end of file
		return -1;	
	
	
	if(  // incorrect name of instruction -> error
		(c != '+') &&
		(c != '-') &&
		(c != '?')
	)
	{
		(*lastChar) = c;
		return 0;
	}
		
	// correct instruction char
	instructionChar = c;	
	// so now, we know the type of instructions and we try to parse the first parameter - number
	
	
	
	
	
	/* 
		2, skipping white spaces and reading first digit
	*/
	
	int numberMax = 2;
	int numberNr = 0;
	char * number = (char *)malloc(numberMax*sizeof(char));
	
	
	c = getchar();
	if(c != ' ') // no space after ins. char -> error
	{
		(*lastChar) = c;
		free(number);
		return 0;
	}
	
	
	else // after ins. char has been space -> continue rading till first digit
	{
		cont = 1;
		while(cont) {
			c = getchar();
			
			if(isdigit(c)) // first digit found
			{
				number[numberNr] = c;
				numberNr++;
				cont = 0;
			}
			
			else if(c != ' ') // error
			{
		        (*lastChar) = c;
				free(number);
				return 0;
			}
			
			// space -> continue
		}
	}
	
	
	
	
	/* 
		3, first digit read; reading rest of number
	*/	
	
	// read the rest of digits
	cont = 1;
	while(cont) {
		c = getchar();
		
		if(c == EOF) // end of file
		{
			free(number);
			return -2;
		}
		
		// digit read -> just add
		if(isdigit(c)) {
			
			if(numberMax <= (numberNr+1)) { // just realloc when full
				numberMax *= 2;
				char * tmp = (char *)realloc(number, sizeof(char) * numberMax);
				number = tmp;
			}
			
			
			number[numberNr] = c;
			numberNr++;
		}
			
			
		// no EOF and non-digit value -> end of parsing?
		else if(instructionChar != '+')	{
			// we want NEWLINE, otherwise error
			if(c == '\n')
			{  
				 /*
				 
					correctly parsed instruction -> return
					
				*/
				
				number[numberNr] = '\0';
				(*numberPointer) = number;
				return (instructionChar == '-') ? 2 : 3;
			}
			else {
				// error
		        (*lastChar) = c;
				free(number);
				return 0;
			}
				
			
		}
		
		
		else { // NEW instruction -> we want space, otherwise wrong
			
			if(c == ' ')
			{
				cont = 0;
			}
			
			else { // error
		        (*lastChar) = c;
				free(number);
				return 0;
			}
		}					
	}
	
	
	
	
	
	// so now we know, that it is NEW + instruction, the number is parsed and also after it has been single space
	
	
	
	
	/* 
		4, reading of name
		so now, we skip the other spaces and find the opening "
	*/
	
	
	cont = 1;
	while(cont) {
		c = getchar();
			
		if(c == '"') // open of name
		{
			cont = 0;
		}
			
		else if(c != ' ') // error
		{
		    (*lastChar) = c;
			free(number);
			return 0;
		}
		
		// space -> continue
	}
	
	
	
	// we found the opening " so we can read the name
	int nameMax = 2;
	int nameNr = 0;
	char * name = (char *)malloc(nameMax*sizeof(char));
	
	cont = 1;
	while(cont) {
		c = getchar();
		
		
		
		// not " 
		if(c != '"') {
			if(c == EOF) // end of file + error
			{
				free(number);
				free(name);
				return -2;
			}
			
			else if(c == '\n') { // error
		        (*lastChar) = c;
				free(number);
				free(name);
				return 0;
			}
			
			
			// just another char of name -> save
			
			if(nameMax <= (nameNr+1)) { // just realloc when full
				nameMax *= 2;
				char * tmp = (char *)realloc(name, sizeof(char) * nameMax);
				name = tmp;
			}
			
			name[nameNr] = c;
			nameNr++;
		}
		
		
		
			
		// char is " -> try to close
		else {
			c = getchar();
			// we want NEWLINE, otherwise wrong
			if(c == '\n') 
			{
				/* 
					correct -> return
				*/
				number[numberNr] = '\0';
				(*numberPointer) = number;
				name[nameNr] = '\0';
				(*namePointer) = name;
				return 1;
			}
			else { // error
		        (*lastChar) = c;
				free(number);
				free(name);
				return 0;
			}
		}				
	}


	
	/*
	
		END OF PARSING
	
	*/
	
	free(number);
	free(name);
	return 0;
}





/*
	7, functions for three main instructions:
		NEW
		DELETE
		SEARCH
*/


/*
	NEW function for adding/updating new user
*/
int newUser(TCENTER * center, char * number, char * name) {
	
	// searching for existing user with the number
	for(int i = 0; i < (center->nr); i++) {
		
		if(strcmp(center->users[i].number, number) == 0) // number found
		{
			free(center->users[i].name);
			center->users[i].name = name;
			
			free(center->users[i].T9);
			stringToT9(center->users[i].name, &center->users[i].T9);
			
			free(number);
			printf("UPDATED\n");
			
			return 0;
		}
		
	}
	
	// number not found -> new user
	
	if(center->nr == center->max) {
		// realloc
		center->max *= 2;
		TUSER * tmp = (TUSER *)realloc(center->users, sizeof(TUSER) * center->max);
		center->users = tmp;
	}
	
	center->users[center->nr].number = number;
	center->users[center->nr].name = name;
	stringToT9(center->users[center->nr].name, &center->users[center->nr].T9);
	
	center->nr++;
	printf("NEW\n");

	return 0;
}


/*
	DELETE function for removing user from list in center
*/
int deleteUser(TCENTER * center, char * number) {
	
	// searching for user with the number
	for(int i = 0; i < (center->nr); i++) {
		
		if(strcmp(center->users[i].number, number) == 0) // number found
		{
			// remove all stufs
			free(center->users[i].number);
				center->users[i].number = NULL;
				
			free(center->users[i].name);
				center->users[i].name = NULL;
				
			free(center->users[i].T9);
				center->users[i].T9 = NULL;
				
			
			int lastUserIndex = ((center->nr)-1);
			if(i != lastUserIndex) { // if the user was not in the end of list
			// we move the last user to the created space, and shorten NR
				center->users[i].number = center->users[lastUserIndex].number;
				center->users[lastUserIndex].number = NULL;
				
				center->users[i].name = center->users[lastUserIndex].name;
				center->users[lastUserIndex].name = NULL;
				
				center->users[i].T9 = center->users[lastUserIndex].T9;
				center->users[lastUserIndex].T9 = NULL;
				
			}
			
			center->nr--;
			free(number);
			
			printf("DELETED\n");
			return 0;
		}
	}
	
	
	
	// not found
	printf("NOT FOUND\n");
	free(number);
	return 0;
}


/*
	SEARCH function for finding user(s) by the pattern corresponding to number or T9
*/
int searchUser(TCENTER * center, char * pattern) {
	
	//printf("SEARCH: %s\n", pattern);
	int amount = 0; // amount of found users
	int userIndex = -1; // the index of found user (used for printf when single user found -> when amount == 1)
	
	
	// searching for user(s) with corresponding attribute(s)
	for(int i = 0; i < (center->nr); i++) {
		
		if(
			(strcmp(center->users[i].number, pattern) == 0) 
		|| 
			(strcmp(center->users[i].T9, pattern) == 0)
		) // user found 
		{
			if(amount == 0) // first user -> saving index for printf
				userIndex = i;
			
			amount++;
		}
		
	}
	
	free(pattern);
	
	
	switch(amount) {
		case 0: // no user found
			printf("NOT FOUND\n");
			return 0;
			break;
			
		case 1: // single user found -> print number and name
			printf("FOUND %s (%s)\n", center->users[userIndex].number, center->users[userIndex].name);
			return 0;
			break;
			
		default: // 2 or more users found -> print amount
			printf("AMBIGUOUS (%d matches)\n", amount);
			return 0;
			break;
	}
	
	return 0; 
}





/*
	8, stringToT9Translate 
	Creates a new T9 from given string
*/
int stringToT9(char * name, char ** T9pointer) {
	
	size_t len = strlen(name); // len of name -> len of T9
	char c; // aux var
	int numOfAlpha = 26;
	
	(*T9pointer) = (char *)malloc((len+1)*sizeof(char));
	char nums[]  = {'2', '2', '2', '3', '3', '3', '4', '4', '4', '5', '5', '5', '6', '6', '6', '7', '7', '7', '7', '8', '8', '8', '9', '9', '9', '9'};
	char chars[] = {'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'}; 
	
	
	
	for(size_t i = 0; i < len; i++) {
		c = name[i];
		
		if(isdigit(c)) // number -> just copy
			(*T9pointer)[i] = c;
		
		else if(c == ' ') // space -> 1
			(*T9pointer)[i] = '1';
		
		else if(isalpha(c)) // alpha -> translate to digit
		{
			c = tolower(c);
			(*T9pointer)[i] = '-';
			
			for(int j = 0; j < numOfAlpha; j++) {
				if(chars[j] == c) {
					(*T9pointer)[i] = nums[j];
					break;
				}
			}
			
			if((*T9pointer)[i] == '-')
				(*T9pointer)[i] = '0';
		}
		
		else // no option for digits 1-9 -> something else -> 0
			(*T9pointer)[i] = '0';
	}
	
	(*T9pointer)[len] = '\0'; // adding end of string
	return 0;
}
