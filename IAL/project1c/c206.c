
/* c206.c **********************************************************}
{* Téma: Dvousměrně vázaný lineární seznam
**
**                   Návrh a referenční implementace: Bohuslav Křena, říjen 2001
**                            Přepracované do jazyka C: Martin Tuček, říjen 2004
**                                            Úpravy: Bohuslav Křena, říjen 2016
**
** Implementujte abstraktní datový typ dvousměrně vázaný lineární seznam.
** Užitečným obsahem prvku seznamu je hodnota typu int.
** Seznam bude jako datová abstrakce reprezentován proměnnou
** typu tDLList (DL znamená Double-Linked a slouží pro odlišení
** jmen konstant, typů a funkcí od jmen u jednosměrně vázaného lineárního
** seznamu). Definici konstant a typů naleznete v hlavičkovém souboru c206.h.
**
** Vaším úkolem je implementovat následující operace, které spolu
** s výše uvedenou datovou částí abstrakce tvoří abstraktní datový typ
** obousměrně vázaný lineární seznam:
**
**      DLInitList ...... inicializace seznamu před prvním použitím,
**      DLDisposeList ... zrušení všech prvků seznamu,
**      DLInsertFirst ... vložení prvku na začátek seznamu,
**      DLInsertLast .... vložení prvku na konec seznamu,
**      DLFirst ......... nastavení aktivity na první prvek,
**      DLLast .......... nastavení aktivity na poslední prvek,
**      DLCopyFirst ..... vrací hodnotu prvního prvku,
**      DLCopyLast ...... vrací hodnotu posledního prvku,
**      DLDeleteFirst ... zruší první prvek seznamu,
**      DLDeleteLast .... zruší poslední prvek seznamu,
**      DLPostDelete .... ruší prvek za aktivním prvkem,
**      DLPreDelete ..... ruší prvek před aktivním prvkem,
**      DLPostInsert .... vloží nový prvek za aktivní prvek seznamu,
**      DLPreInsert ..... vloží nový prvek před aktivní prvek seznamu,
**      DLCopy .......... vrací hodnotu aktivního prvku,
**      DLActualize ..... přepíše obsah aktivního prvku novou hodnotou,
**      DLSucc .......... posune aktivitu na další prvek seznamu,
**      DLPred .......... posune aktivitu na předchozí prvek seznamu,
**      DLActive ........ zjišťuje aktivitu seznamu.
**
** Při implementaci jednotlivých funkcí nevolejte žádnou z funkcí
** implementovaných v rámci tohoto příkladu, není-li u funkce
** explicitně uvedeno něco jiného.
**
** Nemusíte ošetřovat situaci, kdy místo legálního ukazatele na seznam
** předá někdo jako parametr hodnotu NULL.
**
** Svou implementaci vhodně komentujte!
**
** Terminologická poznámka: Jazyk C nepoužívá pojem procedura.
** Proto zde používáme pojem funkce i pro operace, které by byly
** v algoritmickém jazyce Pascalovského typu implemenovány jako
** procedury (v jazyce C procedurám odpovídají funkce vracející typ void).
**/

#include "c206.h"

int solved;
int errflg;

void DLError() {
/*
** Vytiskne upozornění na to, že došlo k chybě.
** Tato funkce bude volána z některých dále implementovaných operací.
**/
    printf ("*ERROR* The program has performed an illegal operation.\n");
    errflg = TRUE;             /* globální proměnná -- příznak ošetření chyby */
    return;
}

void DLInitList (tDLList *L) {
/*
** Provede inicializaci seznamu L před jeho prvním použitím (tzn. žádná
** z následujících funkcí nebude volána nad neinicializovaným seznamem).
** Tato inicializace se nikdy nebude provádět nad již inicializovaným
** seznamem, a proto tuto možnost neošetřujte. Vždy předpokládejte,
** že neinicializované proměnné mají nedefinovanou hodnotu.
**/

    L->First = NULL;
    L->Last = NULL;
    L->Act = NULL;
}

void DLDisposeList (tDLList *L) {
/*
** Zruší všechny prvky seznamu L a uvede seznam do stavu, v jakém
** se nacházel po inicializaci. Rušené prvky seznamu budou korektně
** uvolněny voláním operace free.
**/
    tDLElemPtr pom;
    for(L->Act = L->First; L->Act != NULL; L->Act = L->First){ // cycle begin with first unit in the list and end when next unit is NULL
        pom = L->Act; // pom point to the unit which will be deleted
        L->First = pom->rptr; // next unit, which will be deleted, is the unit next to the pom unit
        free(pom); // delete pom unit
    }
}

void DLInsertFirst (tDLList *L, int val) {
/*
** Vloží nový prvek na začátek seznamu L.
** V případě, že není dostatek paměti pro nový prvek při operaci malloc,
** volá funkci DLError().
**/
	tDLElemPtr pom;
	pom =  malloc(sizeof(tDLElemPtr));
	if(pom == NULL){ // if malloc failed
        DLError();
	}
	else{
        pom->data = val;      // new unit will have value val
        pom->rptr = L->First; // new unit will point to right to the actual first unit
        pom->lptr = NULL;     // new unit will be first unit so it point to the left to NULL
        if(L->First != NULL){ // if the list is not empty
            L->First->lptr = pom; // actual new unit will point to left to the new unit
        }
        else{
            L->Last = pom; // if the list is empty the new unit will be also last unit
        }
        L->First = pom; // new unit will be first unit
	}
}

void DLInsertLast(tDLList *L, int val) {
/*
** Vloží nový prvek na konec seznamu L (symetrická operace k DLInsertFirst).
** V případě, že není dostatek paměti pro nový prvek při operaci malloc,
** volá funkci DLError().
**/
	tDLElemPtr pom;
	pom =  malloc(sizeof(tDLElemPtr));
	if(pom == NULL){
        DLError();
	}
	else{
        pom->data = val;
        pom->rptr = NULL;
        pom->lptr = L->Last;
        if(L->Last != NULL){
            L->Last->rptr = pom;
        }
        else{
            L->First = pom;
        }
        L->Last = pom;
	}
}

void DLFirst (tDLList *L) {
/*
** Nastaví aktivitu na první prvek seznamu L.
** Funkci implementujte jako jediný příkaz (nepočítáme-li return),
** aniž byste testovali, zda je seznam L prázdný.
**/
    L->Act = L->First;
}

void DLLast (tDLList *L) {
/*
** Nastaví aktivitu na poslední prvek seznamu L.
** Funkci implementujte jako jediný příkaz (nepočítáme-li return),
** aniž byste testovali, zda je seznam L prázdný.
**/
    L->Act = L->Last;
}

void DLCopyFirst (tDLList *L, int *val) {
/*
** Prostřednictvím parametru val vrátí hodnotu prvního prvku seznamu L.
** Pokud je seznam L prázdný, volá funkci DLError().
**/
   if(L->First == NULL){
        DLError();
   }
   else{
        *val = L->First->data;  // val will point to the value of first unit
   }
}

void DLCopyLast (tDLList *L, int *val) {
/*
** Prostřednictvím parametru val vrátí hodnotu posledního prvku seznamu L.
** Pokud je seznam L prázdný, volá funkci DLError().
**/
    if(L->Last == NULL){
        DLError();
    }
    else{
        *val = L->Last->data;
    }
}

void DLDeleteFirst (tDLList *L) {
/*
** Zruší první prvek seznamu L. Pokud byl první prvek aktivní, aktivita
** se ztrácí. Pokud byl seznam L prázdný, nic se neděje.
**/

    if(L->First != NULL){  // if the list is not empty
        tDLElemPtr pom;
        pom = L->First; // pom is the actual first unit
        if(L->Act == L->First){ // if the first is active
            L->Act = NULL; // the list loses activity
        }
        if(L->Last == L->First){ // if the list has only 1 unit
            L->First = NULL;    // the list will be empty
            L->Last = NULL;
            L->Act = NULL;
        }
        else{
            L->First = pom->rptr; // new first unit will be the next unit after actual first unit
            L->First->lptr = NULL; // new first unit point to left to NULL
        }
        free(pom); // delete actual first unit
    }
}

void DLDeleteLast (tDLList *L) {
/*
** Zruší poslední prvek seznamu L. Pokud byl poslední prvek aktivní,
** aktivita seznamu se ztrácí. Pokud byl seznam L prázdný, nic se neděje.
**/
    if(L->Last != NULL){
        tDLElemPtr pom;
        pom = L->Last;
        if(L->Act == L->Last){
            L->Act = NULL;
        }
        if(L->Last == L->First){
            L->First = NULL;
            L->Last = NULL;
            L->Act = NULL;
        }
        else{
            L->Last = pom->lptr;
            L->Last->rptr = NULL;
        }
        free(pom);
    }
}

void DLPostDelete (tDLList *L) {
/*
** Zruší prvek seznamu L za aktivním prvkem.
** Pokud je seznam L neaktivní nebo pokud je aktivní prvek
** posledním prvkem seznamu, nic se neděje.
**/
	if(L->Act != NULL){
        if(L->Act != L->Last){
            tDLElemPtr pom;
            pom = L->Act->rptr; // pom point to the next unit after active unit
            L->Act->rptr = pom->rptr; // active unit will point to the right to the next unit after pom unit
            if(pom == L->Last){ // if pom unit is last unit
                L->Last = L->Act; // active unit will be last unit
            }
            pom->rptr = pom; // pom point to next unit after unit which will be deleted
            pom->lptr = L->Act; // unit after deleted unit will point to the left to active unit
            pom = L->Act->rptr; // pom point again to the unit which will be deleted
            free(pom); // delete this unit
        }
	}
}

void DLPreDelete (tDLList *L) {
/*
** Zruší prvek před aktivním prvkem seznamu L .
** Pokud je seznam L neaktivní nebo pokud je aktivní prvek
** prvním prvkem seznamu, nic se neděje.
**/
    if(L->Act != NULL){
        if(L->Act != L->First){
            tDLElemPtr pom;
            pom = L->Act->lptr;
            L->Act->lptr = pom->lptr;
            if(pom == L->First){
                L->First = L->Act;
            }
            pom->lptr = pom;
            pom->rptr = L->Act;
            pom = L->Act->lptr;
            free(pom);
        }
	}
}

void DLPostInsert (tDLList *L, int val) {
/*
** Vloží prvek za aktivní prvek seznamu L.
** Pokud nebyl seznam L aktivní, nic se neděje.
** V případě, že není dostatek paměti pro nový prvek při operaci malloc,
** volá funkci DLError().
**/
    if(L->Act != NULL){
        tDLElemPtr pom;
        pom =  malloc(sizeof(tDLElemPtr));
        if(pom == NULL){
            DLError();
        }
        else{
            pom->data = val; // new unit will have value val
            pom->rptr = L->Act->rptr; // new unit will point to the right to next unit after active unit
            pom->lptr = L->Act; // new unit will point to the left to the active unit
            L->Act->rptr = pom; // active unit will point to the right to the new unit
            if(L->Act == L->Last){ // if active unit is last unit
                L->Last = pom; // new unit will be last unit
            }
            else{
                pom->rptr = pom; // pom will point to next unit after new unit now
                pom->lptr = L->Act->rptr; // pom unit will point to the left to new unit
            }
        }
    }

}

void DLPreInsert (tDLList *L, int val) {
/*
** Vloží prvek před aktivní prvek seznamu L.
** Pokud nebyl seznam L aktivní, nic se neděje.
** V případě, že není dostatek paměti pro nový prvek při operaci malloc,
** volá funkci DLError().
**/
    if(L->Act != NULL){
        tDLElemPtr pom;
        pom =  malloc(sizeof(tDLElemPtr));
        if(pom == NULL){
            DLError();
        }
        else{
            pom->data = val;
            pom->lptr = L->Act->lptr;
            pom->rptr = L->Act;
            L->Act->lptr = pom;
            if(L->Act == L->First){
                L->First = pom;
            }
            else{
                pom->lptr = pom;
                pom->rptr = L->Act->lptr;
            }
        }
    }
}

void DLCopy (tDLList *L, int *val) {
/*
** Prostřednictvím parametru val vrátí hodnotu aktivního prvku seznamu L.
** Pokud seznam L není aktivní, volá funkci DLError ().
**/
    if(L->Act == NULL){
        DLError();
    }
    else{
       *val = L->Act->data;
    }
}

void DLActualize (tDLList *L, int val) {
/*
** Přepíše obsah aktivního prvku seznamu L.
** Pokud seznam L není aktivní, nedělá nic.
**/
    if(L->Act != NULL){
        L->Act->data = val;
    }

}

void DLSucc (tDLList *L) {
/*
** Posune aktivitu na následující prvek seznamu L.
** Není-li seznam aktivní, nedělá nic.
** Všimněte si, že při aktivitě na posledním prvku se seznam stane neaktivním.
**/
    if(L->Act != NULL){
        if(L->Act == L->Last){
            L->Act = NULL;
        }
        else
            L->Act = L->Act->rptr; // active unit will be the unit after the actual active unit
    }
}


void DLPred (tDLList *L) {
/*
** Posune aktivitu na předchozí prvek seznamu L.
** Není-li seznam aktivní, nedělá nic.
** Všimněte si, že při aktivitě na prvním prvku se seznam stane neaktivním.
**/
    if(L->Act != NULL){
        if(L->Act == L->First){
            L->Act = NULL;
        }
        else{
            L->Act = L->Act->lptr; // active unit will be the unit before the actual active unit
        }
    }
}

int DLActive (tDLList *L) {
/*
** Je-li seznam L aktivní, vrací nenulovou hodnotu, jinak vrací 0.
** Funkci je vhodné implementovat jedním příkazem return.
**/
    return(L->Act != NULL); // if L->Act is not NULL is true -> return 1 else return 0
}

/* Konec c206.c*/
