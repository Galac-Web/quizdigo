<?php
// Obținem calea paginii curente
$current_page = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/**
 * Funcție pentru redarea unui item din sidebar cu imagini alese manual
 * @param string $path Calea URL
 * @param string $label Textul de sub iconiță
 * @param string $icon_normal Numele fișierului pentru starea inactivă (ex: 'Books.png')
 * @param string $icon_active Numele fișierului pentru starea activă (ex: 'Books_white.png')
 * @param string $current_page Pagina curentă detectată de server
 */
function render_sb_item($path, $label, $icon_normal, $icon_active, $current_page) {
    // Verificăm dacă suntem pe pagina respectivă
    $is_active = ($current_page === $path);

    // Alegem clasa și imaginea în funcție de starea activă
    $class = $is_active ? 'sb-item active' : 'sb-item';
    $final_icon = $is_active ? $icon_active : $icon_normal;

    $img_url = getCurrentUrl() . "/Templates/admin/dist/img/" . $final_icon;

    return "
    <a class='{$class}' href='{$path}'>
        <img src='{$img_url}' alt='{$label}'>
        <small>{$label}</small>
    </a>";
}
?>

<aside class="sidebar">
    <?php
    // Acum tu decizi exact numele fiecărui fișier pentru fiecare stare:

    echo render_sb_item(
        '/public/homepages',
        'Home',
        'Home Page_nonactice.png',        // Imagine normală
        'Home (1).png ',      // Imagine activă
        $current_page
    );
    echo render_sb_item(
        'dashboard',
        'Dasbord',
        'Dashboard Gauge.png',
        'Location_active.png',
        $current_page
    );
    echo render_sb_item(
        '/public/librari',
        'Librarii',
        'Books.png',    // Imagine normală
        'Books_active.png',  // Imagine activă (orice nume dorești)
        $current_page
    );
    echo render_sb_item(
        '/public/groop',
        'Groop',
        'groop_humman.png',    // Imagine normală
        'groop_hover.png',  // Imagine activă (orice nume dorești)
        $current_page
    );
    /*
    echo render_sb_item(
        '/public/mympas',
        'My maps',
        'My Location.png',
        'My Location_active.png',
        $current_page
    );
    */
    echo render_sb_item(
        '/public/abonament',
        'Abonamet',
        'Membership Card.png',
        'Membership active.png',
        $current_page
    );
    echo render_sb_item(
        'mybank',
        'My Bank',
        'Bank.png',
        'Bank_active.png',
        $current_page
    );

    echo render_sb_item(
        'websait',
        'Web sait',
        'Website.png',
        'Location_active.png',
        $current_page
    );
    echo render_sb_item(
        'participanti',
        'Participants',
        'Dice.png',
        'Location_active.png',
        $current_page
    );
    ?>
</aside>