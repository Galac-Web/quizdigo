# Refactor Quiz Builder

## Ce s-a schimbat

Am refăcut modulele principale ca să fie:
- mai clare la citire;
- cu denumiri lungi și explicite;
- cu responsabilități mai bine separate;
- cu comentarii reale în interiorul fiecărui modul.

## Fișiere refăcute

- `QuizApp.js`
- `QuizDom.js`
- `QuizStore.js`
- `PopupInteractions.js`
- `SlideInteractions.js`
- `CanvasInteractions.js`

## Principiul folosit

Fiecare modul trebuie să răspundă clar la 3 întrebări:
1. Ce face?
2. De ce depinde?
3. Ce NU trebuie să facă?

### Exemplu de separare

- `QuizStore` păstrează starea.
- `QuizDom` ține selectorii și referințele DOM.
- `PopupInteractions` ascultă click-urile pentru popup-uri generale.
- `SlideInteractions` ascultă click-urile pentru lista de slide-uri.
- `CanvasInteractions` ascultă editările și modificările din zona slide-ului curent.
- `CanvasRenderer` și `SlideRenderer` doar desenează UI.

## Avantajul acestei structuri

Când revii peste 2 săptămâni sau 2 luni, poți citi mai rapid logica.
Nu mai ai funcții scurte și ambigue.
Când apare o eroare, știi mai exact în ce modul trebuie căutată.

## Pasul următor recomandat

Următorul pas bun este să refaci și:
- `CanvasRenderer.js`
- `SlideRenderer.js`
- `PopupTemplates.js`
- `GlobalPopup.js`

în exact același stil.
