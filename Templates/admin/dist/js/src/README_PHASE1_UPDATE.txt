
Actualizare faza 1

Au fost modificate doar fișierele necesare pentru cerințele noi, fără să fie șters fluxul existent:

1. QuizStore.js
- suport pentru answer objects extinse: text + imageUrl + correct + color
- suport pentru 2 / 4 / 6 răspunsuri
- suport pentru multiple answers reale
- suport pentru împărțirea imaginii principale pe răspunsuri

2. CanvasInteractions.js
- input/change handlers noi pentru:
  - număr răspunsuri
  - imagine pe răspuns prin URL
  - imagine pe răspuns prin upload
  - selecție multiplă pentru răspunsuri corecte
  - activare mod imagine principală împărțită pe răspunsuri

3. CanvasRenderer.js
- UI nou pentru fiecare răspuns:
  - text
  - URL imagine
  - upload imagine
  - thumbnail imagine
- suport pentru 2 / 4 / 6 blocuri
- suport pentru imaginea principală împărțită pe răspunsuri

Integrare:
- folderul conține toate fișierele proiectului originale copiate,
  iar cele 3 de mai sus sunt înlocuite deja cu versiunile noi.
