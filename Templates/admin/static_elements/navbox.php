<?php

use Evasystem\Controllers\Avion\AvionService;
use Evasystem\Controllers\Users\UsersService;

// $_SESSION['user_id']
$users = $_SESSION['user_id'] ?? null;

$allroomsAvion = new AvionService();
$usersAll = new UsersService();

$userid = $usersAll->getIdUserss($users);

if (!empty($userid[0]['photo'])) {
    $img = $userid[0]['photo'];
} else {
    $img = getCurrentUrl() . '/logo_new.png';
}
?>
<div class="topbar">
    <div class="top-left">
        <div class="search">
            <span class="glass"></span>
            <input type="text" placeholder="Search" />
        </div>


    </div>
    <div class="top-right">
        <div class="profile" id="profileToggle">
            <div class="avatar" style="background-image: url('<?php echo $img?>');"></div>
            <span><?=$userid[0]['fullname'];?></span>
            <span class="chev" id="chevIcon">▾</span>

            <div class="dropdown-menu" id="profileDropdown">
                <a href="/public/userslist" class="dropdown-item">
                    <span>All Users</span>
                </a>
                <a href="/public/profileuserslist?id=<?= $_SESSION['user_id']?>" class="dropdown-item">
                    <span>Profilul Meu</span>
                </a>
                <a href="/public/mybank" class="dropdown-item">
                    <span>Achitarile</span>
                </a>
                <hr>
                <a href="/public/login" class="dropdown-item logout">
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</div>