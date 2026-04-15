<?php

require_once __DIR__ . '/../vendor/autoload.php';


use Evasystem\Core\Router;
use Evasystem\Controllers\Verify;
use Evasystem\Core\AdvancedCRUD;
use Config\Database;
use Evasystem\Core\Auth\Permision;
use Evasystem\Core\Auth\RolesRepository;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Încarcă variabilele din .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Încarcă configurația
$config = require __DIR__ . '/../config/config.php';
$ROLES = require __DIR__ . '/../config/roles.php';
// Inițializează baza de date
Database::getInstance(
    $config['db_host'],
    $config['db_name'],
    $config['db_user'],
    $config['db_pass']
);

$veryfi = new Verify;
$return = $veryfi->verifyusers();

// Încărcăm toate rolurile din BD (role_slug → permisiuni, nav etc.)
$ROLES = RolesRepository::loadAll();

// Construim obiectul de permisiuni cu lista tuturor rolurilor
$perm = new Permision($ROLES);

// Prefacem indexurile interne pentru acces rapid (cache la rute active)
// - verifică BD.tabel routes.is_active
$perm->warmRoutesIndex(); // opțional

// ============================
// DEFINIM RUTELE PUBLICE
// ============================

// Setăm lista de rute care nu cer autentificare
// pot fi accesate de oricine (guest inclusiv)
$perm->setPublicPaths([
    '/',                           // pagina principală
    '/public/login',               // login
    '/public/reg',                 // registrare
    '/public/addusersadd',         // adăugare user fără autentificare (probabil onboarding)
    '/public/pages',               // pagini publice
    '/public/crududoc.',           // ?? trebuie verificat, pare greșit, dar îl lași
    '/public/auth/google',         // Google Login
    '/public/auth/google/callback',// răspuns Google OAuth

    // fișiere statice / assets
    '/assets/*',
    '/public/assets/*'
]);

// ============================
// PROTECȚIE PE RUTA CURENTĂ
// ============================

// Metoda HTTP a cererii actuale (GET/POST)
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Calea URL cerută de utilizator
// parse_url() extrage doar path-ul (fără query string)
$path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

// Protejează ruta curentă
// dacă user-ul NU are permisiunea → redirect automat / 403
$perm->guard($method, $path);

// ============================
// GENERARE MENIU DIN BD
// ============================

// Rolul curent (salvat în sesiune la login)
$role = $_SESSION['role'] ?? 'guest';

// array pentru suprascrieri — poți adăuga, elimina sau redenumi itemi din meniu dinamic
$overrides = [];

// Obținem lista de itemi pe care rolul are voie să-i vadă în meniu
// (nu arbore încă, doar flat list)
$menu = $perm->allowedNav($role, $overrides);

// ============================
// WIDGET-URI DIN BD (opțional)
// ============================

// Dacă în clasa Permision există metoda widgetsFor()
// atunci încărcăm widget-urile speciale disponibile pentru acest rol
$widgets = method_exists($perm, 'widgetsFor') ? $perm->widgetsFor($role) : [];

// ============================
// CONSTRUIM ARBORELE DE NAVIGAȚIE
// ============================

// Rolul din sesiune (încă o dată în caz că s-a schimbat)
$role = $_SESSION['role'] ?? 'guest';

// Construim NAV-ul JERARHIC (root → children)
// navTreeFor merge în DB, ia role_nav și îl transformă în arbore
$navTree = $perm->navTreeFor($role);

// ============================
// FACEM NAV DISPONIBIL ÎN TEMPLATE
// ============================

// Structura finală a meniului (arbore)
$GLOBALS['APP_NAVTREE'] = $navTree;

// Rolul curent al userului
$GLOBALS['APP_ROLE'] = $role;

// Obiectul principal de permisiuni (pentru verificări în view)
$GLOBALS['APP_PERM'] = $perm;

$router = new Router();

// Încarcă toate rutele active din `routes`
$rows = AdvancedCRUD::select('routes', '*', "WHERE is_active = 1");


foreach ($rows as $row) {
    $method     = $row['method'];
    $path       = $row['path'];
    $controller = $row['controller'];
    $action     = $row['action'];
    $dir        = $row['dir'];

    if (empty($controller) || empty($action)) {
        // fallback spre Admin::rootFunction($dir)
        $router->addRoute($method, $path, $dir);
    } else {
        $router->addRoute($method, $path, $controller, $action, $dir);
    }
}

// Execută logica de rutare
$router->handleRequest();
?>

