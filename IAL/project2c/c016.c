
/* c016.c: **********************************************************}
{* Téma:  Tabulka s Rozptýlenými Položkami
**                      První implementace: Petr Přikryl, prosinec 1994
**                      Do jazyka C prepsal a upravil: Vaclav Topinka, 2005
**                      Úpravy: Karel Masařík, říjen 2014
**                              Radek Hranický, říjen 2014
**                              Radek Hranický, listopad 2015
**                              Radek Hranický, říjen 2016
**
** Vytvořete abstraktní datový typ
** TRP (Tabulka s Rozptýlenými Položkami = Hash table)
** s explicitně řetězenými synonymy. Tabulka je implementována polem
** lineárních seznamů synonym.
**
** Implementujte následující procedury a funkce.
**
**  HTInit ....... inicializuje tabulku před prvním použitím
**  HTInsert ..... vložení prvku
**  HTSearch ..... zjištění přítomnosti prvku v tabulce
**  HTDelete ..... zrušení prvku
**  HTRead ....... přečtení hodnoty prvku
**  HTClearAll ... zrušení obsahu celé tabulky (inicializace tabulky
**                 poté, co již byla použita)
**
** Definici typů naleznete v souboru c016.h.
**
** Tabulka je reprezentována datovou strukturou typu tHTable,
** která se skládá z ukazatelů na položky, jež obsahují složky
** klíče 'key', obsahu 'data' (pro jednoduchost typu float), a
** ukazatele na další synonymum 'ptrnext'. Při implementaci funkcí
** uvažujte maximální rozměr pole HTSIZE.
**
** U všech procedur využívejte rozptylovou funkci hashCode.  Povšimněte si
** způsobu předávání parametrů a zamyslete se nad tím, zda je možné parametry
** předávat jiným způsobem (hodnotou/odkazem) a v případě, že jsou obě
** možnosti funkčně přípustné, jaké jsou výhody či nevýhody toho či onoho
** způsobu.
**
** V příkladech jsou použity položky, kde klíčem je řetězec, ke kterému
** je přidán obsah - reálné číslo.
*/

#include "c016.h"

int HTSIZE = MAX_HTSIZE;
int solved;

/*          -------
** Rozptylovací funkce - jejím úkolem je zpracovat zadaný klíč a přidělit
** mu index v rozmezí 0..HTSize-1.  V ideálním případě by mělo dojít
** k rovnoměrnému rozptýlení těchto klíčů po celé tabulce.  V rámci
** pokusů se můžete zamyslet nad kvalitou této funkce.  (Funkce nebyla
** volena s ohledem na maximální kvalitu výsledku). }
*/

int hashCode ( tKey key ) {
	int retval = 1;
	int keylen = strlen(key);
	for ( int i=0; i<keylen; i++ )
		retval += key[i];
	return ( retval % HTSIZE );
}

/*
** Inicializace tabulky s explicitně zřetězenými synonymy.  Tato procedura
** se volá pouze před prvním použitím tabulky.
*/

void htInit ( tHTable* ptrht ) {

 	int i;
 	for(i = 0; i < HTSIZE; i++){
 		(*ptrht)[i] = NULL; // inicializate heads
 	}
}

/* TRP s explicitně zřetězenými synonymy.
** Vyhledání prvku v TRP ptrht podle zadaného klíče key.  Pokud je
** daný prvek nalezen, vrací se ukazatel na daný prvek. Pokud prvek nalezen není, 
** vrací se hodnota NULL.
**
*/

tHTItem* htSearch ( tHTable* ptrht, tKey key ) {
	tHTItem* temp;
	int i;
	if ((*ptrht) != NULL){
		i = hashCode(key); // index of list, which may contains key
		if (((*ptrht)[i]) != NULL){ // if this list is not empty
			temp = (*ptrht)[i];
			while (temp != NULL){
				if (temp->key == key) // if it is found, then return pointer to this item
					return temp;
				else
					temp = temp->ptrnext; // move to next item in list
			}
			return NULL; // if after while is not found then return NULL
		}
		return NULL; // if (*ptrht)[i] is NULL
	}
	return NULL; // if *ptrth is NULL
}

/* 
** TRP s explicitně zřetězenými synonymy.
** Tato procedura vkládá do tabulky ptrht položku s klíčem key a s daty
** data.  Protože jde o vyhledávací tabulku, nemůže být prvek se stejným
** klíčem uložen v tabulce více než jedenkrát.  Pokud se vkládá prvek,
** jehož klíč se již v tabulce nachází, aktualizujte jeho datovou část.
**
** Využijte dříve vytvořenou funkci htSearch.  Při vkládání nového
** prvku do seznamu synonym použijte co nejefektivnější způsob,
** tedy proveďte.vložení prvku na začátek seznamu.
**/

void htInsert ( tHTable* ptrht, tKey key, tData data ) {
	tHTItem* temp;
	int i;
	if ((*ptrht) != NULL){
		if ((temp = htSearch(&(*ptrht), key)) != NULL){ // if item with this key is already existed
			temp->data = data; // rewrite its value
		}
		else{
			if ((temp = (struct tHTItem*) malloc(sizeof(struct tHTItem))) != NULL){ // if not then we make new 
				i = hashCode(key);
				temp->key = key;
				temp->data = data;
				temp->ptrnext = (*ptrht)[i]; // first item will be second
				(*ptrht)[i] = temp; // new item will be in the first place of the list
			}
		}
	}
}

/*
** TRP s explicitně zřetězenými synonymy.
** Tato funkce zjišťuje hodnotu datové části položky zadané klíčem.
** Pokud je položka nalezena, vrací funkce ukazatel na položku
** Pokud položka nalezena nebyla, vrací se funkční hodnota NULL
**
** Využijte dříve vytvořenou funkci HTSearch.
*/

tData* htRead ( tHTable* ptrht, tKey key ) {
	tHTItem* temp;
	if ((*ptrht) != NULL){
		if ((temp = htSearch(&(*ptrht), key)) != NULL) // if we found it then return its value
			return (&(temp->data));
		else	
			return NULL;
	}
	else
	 	return NULL;
}

/*
** TRP s explicitně zřetězenými synonymy.
** Tato procedura vyjme položku s klíčem key z tabulky
** ptrht.  Uvolněnou položku korektně zrušte.  Pokud položka s uvedeným
** klíčem neexistuje, dělejte, jako kdyby se nic nestalo (tj. nedělejte
** nic).
**
** V tomto případě NEVYUŽÍVEJTE dříve vytvořenou funkci HTSearch.
*/

void htDelete ( tHTable* ptrht, tKey key ) {
	tHTItem* temp;
	int i;
	if ((*ptrht) != NULL){
		i = hashCode(key);
		if((*ptrht)[i] != NULL){
			temp = (*ptrht)[i];
			if (temp->key == key){ // if first item is search item
				(*ptrht)[i] = temp->ptrnext; // next item after first item will be the first item
					free(temp);
					temp = NULL;
			}
			else{
				while (temp != NULL){
					if (temp->ptrnext->key == key){ // if the next item is the deleted item
						temp->ptrnext = temp->ptrnext->ptrnext; // then actual item will point to the item next to the next item
						free(temp->ptrnext);
						temp->ptrnext = NULL;	
					}
					temp = temp->ptrnext; // moving to the enxt item in the list
				}
			}
		}
	}
}

/* TRP s explicitně zřetězenými synonymy.
** Tato procedura zruší všechny položky tabulky, korektně uvolní prostor,
** který tyto položky zabíraly, a uvede tabulku do počátečního stavu.
*/

void htClearAll ( tHTable* ptrht ) {
	tHTItem* temp;
	int i;
	if ((*ptrht) != NULL){
		for (i = 0; i < HTSIZE; i++){
			while ((*ptrht)[i] != NULL){ // while this list is not in the end
				temp = (*ptrht)[i];
				(*ptrht)[i] = (*ptrht)[i]->ptrnext; // moving to the next item in list
				free (temp); // remove items in the actual list with i index
				temp = NULL;
			}
		}
	}
}
