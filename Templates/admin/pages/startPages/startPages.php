<?php
function getCurrentUrl()
{
    // Определяем протокол
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    // Формируем URL
    $currentUrl = $protocol . "://" . $_SERVER['HTTP_HOST'];
    return $currentUrl;
}
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <link rel="icon" href="<?=getCurrentUrl();?>/Templates/admin/assets/images/favicon-32x32.png" type="image/png" />
    <!--plugins-->
    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet" />
    <!-- loader-->
    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/css/pace.min.css" rel="stylesheet" />
    <script src="<?=getCurrentUrl();?>/Templates/admin/assets/js/pace.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/css/bootstrap-extended.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/css/app.css" rel="stylesheet">
    <link href="<?=getCurrentUrl();?>/Templates/admin/assets/css/icons.css" rel="stylesheet">
    <title>Rocker - Bootstrap 5 Admin Dashboard Template</title>
</head>

<body class="">
<!--wrapper-->
<div class="wrapper">
    <div class="section-authentication-cover">
        <div class="">
            <div class="row g-0">

        



            </div>
            <!--end row-->
        </div>
    </div>
</div>
<!--end wrapper-->
<!-- Bootstrap JS -->
<script src="<?=getCurrentUrl();?>/Templates/admin/assets/js/bootstrap.bundle.min.js"></script>
<!--plugins-->
<script src="<?=getCurrentUrl();?>/Templates/admin/assets/js/jquery.min.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/assets/plugins/simplebar/js/simplebar.min.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/assets/plugins/metismenu/js/metisMenu.min.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
<!--Password show & hide js -->
<script>
    $(document).ready(function () {
        $("#show_hide_password a").on('click', function (event) {
            event.preventDefault();
            if ($('#show_hide_password input').attr("type") == "text") {
                $('#show_hide_password input').attr('type', 'password');
                $('#show_hide_password i').addClass("bx-hide");
                $('#show_hide_password i').removeClass("bx-show");
            } else if ($('#show_hide_password input').attr("type") == "password") {
                $('#show_hide_password input').attr('type', 'text');
                $('#show_hide_password i').removeClass("bx-hide");
                $('#show_hide_password i').addClass("bx-show");
            }
        });
    });
</script>
<!--app JS-->
<script src="<?=getCurrentUrl();?>/Templates/admin/assets/js/app.js"></script>
</body>

<script>'undefined'=== typeof _trfq || (window._trfq = []);'undefined'=== typeof _trfd && (window._trfd=[]),_trfd.push({'tccl.baseHost':'secureserver.net'},{'ap':'cpsh-oh'},{'server':'p3plzcpnl509132'},{'dcenter':'p3'},{'cp_id':'10399385'},{'cp_cl':'8'}) // Monitoring performance to make your website faster. If you want to opt-out, please contact web hosting support.</script><script src='https://img1.wsimg.com/traffic-assets/js/tccl.min.js'></script></html>