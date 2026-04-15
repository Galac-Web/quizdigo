# CRM-Framework by Galac Radu

Acest framework JavaScript modular este conceput pentru aplicații CRM și dashboard-uri moderne. Scris în ES6 nativ, framework-ul pune accent pe claritate, modularitate și extensibilitate.

## 🔧 Structură generală
/crm-framework/
│
├── boot.js ← Inițializare aplicație
├── router.js ← Router pentru pagini pe bază de data-page
├── transport.js ← AJAX helper (GET/POST/PUT/DELETE)
│
├── /system/ ← Nucleu aplicație (stare, events, helpers)
├── /domains/ ← Module per pagină (ex: dashboard, firm, team)
├── /widgets/ ← Componente UI dinamice și reutilizabile

## 🧠 Principii de dezvoltare

- Fiecare modul din `/domains/` este încărcat dinamic în funcție de pagina activă.
- `EventBus` permite comunicare între module fără tight coupling.
- `transport.js` unifică cererile AJAX într-un mod reutilizabil.
- `/widgets/` conține componente UI simple, fără framework (React/Vue/etc.).

## 🔒 Drepturi de autor

Acest cod a fost dezvoltat de către **Galac Radu**, pe baza experienței personale acumulate în peste 18 ani de activitate IT.

> **Declarație de originalitate:**  
> Codul din acest proiect este scris independent și nu este copiat sau reutilizat din alte proiecte comerciale sau protejate de NDA.  
> Framework-ul reflectă stilul și abordarea autorului și este destinat utilizării personale sau în proiecte proprii.

## 📝 Licență

Proprietate intelectuală: Galac Radu  
Licență: Custom Personal License – poate fi folosit, modificat și distribuit cu acordul autorului.