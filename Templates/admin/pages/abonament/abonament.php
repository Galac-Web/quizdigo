<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use Evasystem\Controllers\Users\UsersService;
use Evasystem\Controllers\Mybank\MybankService;
use Evasystem\Core\Mybank\MybankModel;

if (!isset($_SESSION['user_id'])) {
    die('Utilizatorul nu este autentificat.');
}

$usersService = new UsersService();
$currentUserData = $usersService->getIdUserss((int)$_SESSION['user_id']);
$currentUser = (is_array($currentUserData) && isset($currentUserData[0])) ? $currentUserData[0] : $currentUserData;
$userRandomnId = (string)($currentUser['randomn_id'] ?? '');

if ($userRandomnId === '') {
    die('randomn_id utilizator lipsă.');
}

$mybankService = new MybankService();
$account = $mybankService->ensureAccount($userRandomnId);
$accountRandomnId = (string)($account['randomn_id'] ?? '');

$savedCards = [];
if ($accountRandomnId !== '') {
    $savedCards = MybankModel::getCards($accountRandomnId);
}
?>
<style>
    :root {
        --sementicscolorfgdefault: rgba(24, 24, 27, 1);
        --sementicscolorprimaryon-default: rgba(255, 255, 255, 1);
        --sementicscolorfgdisabled: rgba(161, 161, 170, 1);
        --sementicscolorfgon-accent: rgba(255, 255, 255, 1);
        --sementicscolorprimaryon-subtle: rgba(29, 78, 216, 1);
        --sementicscolorprimarydefault: rgba(37, 99, 235, 1);
        --sementicscolorbordermuted: rgba(228, 228, 231, 1);
        --sementicscolorbgsurface: rgba(255, 255, 255, 1);

        --sementic-type-title-xl-font-family: "Inter", Helvetica;
        --sementic-type-title-xl-font-weight: 700;
        --sementic-type-title-xl-font-size: 32px;
        --sementic-type-title-xl-letter-spacing: 0px;
        --sementic-type-title-xl-line-height: 114.99999761581421%;
        --sementic-type-title-xl-font-style: normal;

        --sementic-type-body-xl-font-family: "Inter", Helvetica;
        --sementic-type-body-xl-font-weight: 400;
        --sementic-type-body-xl-font-size: 18px;
        --sementic-type-body-xl-letter-spacing: 0px;
        --sementic-type-body-xl-line-height: 125%;
        --sementic-type-body-xl-font-style: normal;

        --sementic-type-label-m-font-family: "Inter", Helvetica;
        --sementic-type-label-m-font-weight: 600;
        --sementic-type-label-m-font-size: 14px;
        --sementic-type-label-m-letter-spacing: 0px;
        --sementic-type-label-m-line-height: 114.99999761581421%;
        --sementic-type-label-m-font-style: normal;

        --sementic-type-title-l-font-family: "Inter", Helvetica;
        --sementic-type-title-l-font-weight: 700;
        --sementic-type-title-l-font-size: 20px;
        --sementic-type-title-l-letter-spacing: 0px;
        --sementic-type-title-l-line-height: 114.99999761581421%;
        --sementic-type-title-l-font-style: normal;

        --sementic-type-body-m-font-family: "Inter", Helvetica;
        --sementic-type-body-m-font-weight: 400;
        --sementic-type-body-m-font-size: 14px;
        --sementic-type-body-m-letter-spacing: 0px;
        --sementic-type-body-m-line-height: 125%;
        --sementic-type-body-m-font-style: normal;

        --sementic-type-body-s-font-family: "Inter", Helvetica;
        --sementic-type-body-s-font-weight: 400;
        --sementic-type-body-s-font-size: 12px;
        --sementic-type-body-s-letter-spacing: 0px;
        --sementic-type-body-s-line-height: 114.99999761581421%;
        --sementic-type-body-s-font-style: normal;

        --sementic-type-label-l-font-family: "Inter", Helvetica;
        --sementic-type-label-l-font-weight: 600;
        --sementic-type-label-l-font-size: 16px;
        --sementic-type-label-l-letter-spacing: 0px;
        --sementic-type-label-l-line-height: 114.99999761581421%;
        --sementic-type-label-l-font-style: normal;

        --colors-accents-cyan: rgba(0, 192, 232, 1);
        --colors-accents-red: rgba(255, 56, 60, 1);

        --page-bg-1: #f8fbff;
        --page-bg-2: #eef5ff;
        --page-bg-3: #f6f9ff;
        --card-shadow: 0 20px 50px rgba(37, 99, 235, 0.08);
        --card-shadow-hover: 0 28px 70px rgba(37, 99, 235, 0.16);
    }

    html, body {
        margin: 0;
        padding: 0;
        font-family: Inter, Arial, sans-serif;
        background:
                radial-gradient(circle at top left, rgba(37,99,235,.10), transparent 25%),
                radial-gradient(circle at top right, rgba(0,192,232,.10), transparent 20%),
                linear-gradient(180deg, var(--page-bg-1) 0%, var(--page-bg-2) 45%, var(--page-bg-3) 100%);
        min-height: 100%;
    }

    .screen {
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: 1220px;
        margin: 0 auto;
        position: relative;
    }

    .screen::before,
    .screen::after{
        content:"";
        position:absolute;
        border-radius:50%;
        filter: blur(60px);
        pointer-events:none;
        z-index:0;
    }

    .screen::before{
        width:260px;
        height:260px;
        top:70px;
        left:-80px;
        background: rgba(37, 99, 235, 0.10);
    }

    .screen::after{
        width:300px;
        height:300px;
        right:-100px;
        top:220px;
        background: rgba(0, 192, 232, 0.08);
    }

    .screen .container-medium {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 32px;
        padding: 80px 120px;
        width: 100%;
        box-sizing: border-box;
        position: relative;
        z-index: 1;
    }

    .screen .header {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        animation: fadeUp .8s ease both;
    }

    .screen .text-wrapper {
        width: fit-content;
        font-family: var(--sementic-type-title-xl-font-family);
        font-weight: var(--sementic-type-title-xl-font-weight);
        color: var(--sementicscolorfgdefault);
        font-size: clamp(30px, 4vw, 40px);
        letter-spacing: var(--sementic-type-title-xl-letter-spacing);
        line-height: 1.1;
        white-space: nowrap;
        font-style: var(--sementic-type-title-xl-font-style);
        position: relative;
    }

    .screen .text-wrapper::after{
        content:"";
        display:block;
        width:58%;
        height:6px;
        margin:12px auto 0;
        border-radius:999px;
        background: linear-gradient(90deg, var(--sementicscolorprimarydefault), var(--colors-accents-cyan));
        opacity:.9;
    }

    .screen .div {
        width: fit-content;
        font-family: var(--sementic-type-body-xl-font-family);
        font-weight: var(--sementic-type-body-xl-font-weight);
        color: rgba(24,24,27,.72);
        font-size: var(--sementic-type-body-xl-font-size);
        letter-spacing: var(--sementic-type-body-xl-letter-spacing);
        line-height: var(--sementic-type-body-xl-line-height);
        white-space: nowrap;
        font-style: var(--sementic-type-body-xl-font-style);
    }

    .screen .frame {
        display: inline-flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 22px;
        width: 100%;
    }

    .screen .frame-wrapper {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding: 8px;
        box-sizing: border-box;
        animation: fadeUp .95s ease both;
    }

    .screen .frame-2 {
        display: inline-flex;
        align-items: flex-start;
        padding: 4px;
        background-color: rgba(255,255,255,.7);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border-radius: 100px;
        border: 1px solid rgba(228,228,231,.9);
        box-shadow: 0 10px 30px rgba(15,23,42,.06);
    }

    .screen .button,
    .screen .div-wrapper {
        position: relative;
        overflow: hidden;
        transition: all .25s ease;
    }

    .screen .button::before,
    .screen .div-wrapper::before{
        content:"";
        position:absolute;
        inset:0;
        background: linear-gradient(135deg, rgba(255,255,255,.16), rgba(255,255,255,0));
        opacity:0;
        transition:.25s ease;
    }

    .screen .button:hover::before,
    .screen .div-wrapper:hover::before{
        opacity:1;
    }

    .screen .button {
        all: unset;
        box-sizing: border-box;
        display: inline-flex;
        padding: 10px 18px;
        background: linear-gradient(135deg, var(--sementicscolorprimarydefault), #1d4ed8);
        border-radius: 100px;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        box-shadow: 0 10px 24px rgba(37,99,235,.25);
    }

    .screen .button:hover{
        transform: translateY(-1px) scale(1.02);
    }

    .screen .text-wrapper-2 {
        width: fit-content;
        font-family: var(--sementic-type-label-m-font-family);
        font-weight: var(--sementic-type-label-m-font-weight);
        color: var(--sementicscolorprimaryon-default);
        font-size: var(--sementic-type-label-m-font-size);
        letter-spacing: var(--sementic-type-label-m-letter-spacing);
        line-height: var(--sementic-type-label-m-line-height);
        white-space: nowrap;
        font-style: var(--sementic-type-label-m-font-style);
    }

    .screen .div-wrapper {
        all: unset;
        box-sizing: border-box;
        display: inline-flex;
        padding: 10px 18px;
        border-radius: 100px;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
    }

    .screen .div-wrapper:hover{
        background: rgba(37,99,235,.06);
    }

    .screen .text-wrapper-3 {
        width: fit-content;
        font-family: var(--sementic-type-label-m-font-family);
        font-weight: var(--sementic-type-label-m-font-weight);
        color: var(--sementicscolorfgdisabled);
        font-size: var(--sementic-type-label-m-font-size);
        letter-spacing: var(--sementic-type-label-m-letter-spacing);
        line-height: var(--sementic-type-label-m-line-height);
        white-space: nowrap;
        font-style: var(--sementic-type-label-m-font-style);
    }

    .screen .frame-3 {
        display: flex;
        width: 100%;
        align-items: stretch;
        gap: 20px;
    }

    .screen .pricing-card,
    .screen .pricing-card-2 {
        display: flex;
        flex: 1;
        flex-direction: column;
        background:
                linear-gradient(180deg, rgba(255,255,255,.96) 0%, rgba(255,255,255,.92) 100%);
        border-radius: 24px;
        overflow: hidden;
        position: relative;
        transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease;
        animation: fadeUp 1s ease both;
    }

    .screen .pricing-card{
        border: 1px solid rgba(228, 228, 231, 1);
        box-shadow: var(--card-shadow);
    }

    .screen .pricing-card-2{
        border: 2px solid var(--sementicscolorprimarydefault);
        box-shadow: 0 26px 60px rgba(37,99,235,.18);
    }

    .screen .pricing-card::before,
    .screen .pricing-card-2::before{
        content:"";
        position:absolute;
        inset:auto -30px -80px auto;
        width:180px;
        height:180px;
        border-radius:50%;
        background: radial-gradient(circle, rgba(37,99,235,.09) 0%, rgba(37,99,235,0) 70%);
        pointer-events:none;
    }

    .screen .pricing-card:hover,
    .screen .pricing-card-2:hover{
        transform: translateY(-8px);
        box-shadow: 0 28px 70px rgba(37,99,235,.16);
    }

    .screen .puplar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 8px;
        width: 100%;
        background: linear-gradient(90deg, var(--sementicscolorprimarydefault), #1d4ed8);
        box-sizing: border-box;
        position: relative;
        overflow: hidden;
    }

    .screen .puplar::before{
        content:"";
        position:absolute;
        inset:0;
        background: linear-gradient(120deg, transparent 20%, rgba(255,255,255,.18) 50%, transparent 80%);
        transform: translateX(-100%);
        animation: shimmer 3.2s linear infinite;
    }

    .screen .most-popular {
        width: fit-content;
        font-family: var(--sementic-type-label-m-font-family);
        font-weight: var(--sementic-type-label-m-font-weight);
        color: var(--sementicscolorfgon-accent);
        font-size: var(--sementic-type-label-m-font-size);
        letter-spacing: var(--sementic-type-label-m-letter-spacing);
        line-height: var(--sementic-type-label-m-line-height);
        white-space: nowrap;
        font-style: var(--sementic-type-label-m-font-style);
        position: relative;
        z-index: 1;
    }

    .screen .sparkles {
        width: 16px;
        height: 16px;
        position: relative;
        z-index: 1;
        animation: floatY 2s ease-in-out infinite;
    }

    .screen .card-content {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 34px;
        padding: 28px 20px 20px;
        width: 100%;
        box-sizing: border-box;
    }

    .screen .pricing-headear {
        display: inline-flex;
        gap: 28px;
        padding: 0 8px;
        flex-direction: column;
        align-items: flex-start;
    }

    .screen .frame-4 {
        display: inline-flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .screen .text-wrapper-4 {
        width: fit-content;
        font-family: var(--sementic-type-title-l-font-family);
        font-weight: var(--sementic-type-title-l-font-weight);
        color: var(--sementicscolorfgdefault);
        font-size: 24px;
        letter-spacing: var(--sementic-type-title-l-letter-spacing);
        line-height: 1.15;
        white-space: nowrap;
        font-style: var(--sementic-type-title-l-font-style);
    }

    .screen .text-wrapper-5 {
        width: fit-content;
        font-family: var(--sementic-type-body-m-font-family);
        font-weight: 500;
        color: rgba(24,24,27,.62);
        font-size: 14px;
        letter-spacing: var(--sementic-type-body-m-letter-spacing);
        line-height: 1.5;
        white-space: nowrap;
        font-style: var(--sementic-type-body-m-font-style);
    }

    .screen .frame-5 {
        display: inline-flex;
        align-items: flex-end;
        gap: 4px;
    }

    .screen .text-wrapper-6 {
        width: fit-content;
        font-family: "Inter-Bold", Helvetica, Arial, sans-serif;
        font-weight: 800;
        color: var(--sementicscolorfgdefault);
        font-size: 42px;
        letter-spacing: -1px;
        line-height: 1;
        white-space: nowrap;
    }

    .screen .text-wrapper-7 {
        width: fit-content;
        font-family: var(--sementic-type-body-s-font-family);
        font-weight: 600;
        color: rgba(24,24,27,.6);
        font-size: 13px;
        letter-spacing: var(--sementic-type-body-s-letter-spacing);
        line-height: 1.15;
        white-space: nowrap;
        font-style: var(--sementic-type-body-s-font-style);
        padding-bottom: 4px;
    }

    .screen .button-2,
    .screen .button-3,
    .screen .button-4 {
        all: unset;
        box-sizing: border-box;
        display: flex;
        padding: 14px 20px;
        width: 100%;
        border-radius: 14px;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        transition: all .26s ease;
        position: relative;
        overflow: hidden;
    }

    .screen .button-2::before,
    .screen .button-3::before,
    .screen .button-4::before{
        content:"";
        position:absolute;
        inset:0;
        background: linear-gradient(120deg, transparent 25%, rgba(255,255,255,.22) 50%, transparent 75%);
        transform: translateX(-120%);
        transition: transform .55s ease;
    }

    .screen .button-2:hover::before,
    .screen .button-3:hover::before,
    .screen .button-4:hover::before{
        transform: translateX(120%);
    }

    .screen .button-2 {
        background: linear-gradient(135deg, var(--colors-accents-cyan), #0098c9);
        box-shadow: 0 14px 30px rgba(0,192,232,.22);
    }

    .screen .button-3 {
        background: linear-gradient(135deg, var(--colors-accents-red), #e11d48);
        box-shadow: 0 14px 30px rgba(255,56,60,.20);
    }

    .screen .button-4 {
        border: 1px solid rgba(37,99,235,.22);
        background: linear-gradient(180deg, #fff, #f8fbff);
        box-shadow: 0 12px 24px rgba(37,99,235,.06);
    }

    .screen .button-2:hover,
    .screen .button-3:hover,
    .screen .button-4:hover{
        transform: translateY(-2px) scale(1.015);
    }

    .screen .text-wrapper-8 {
        width: fit-content;
        font-family: var(--sementic-type-label-l-font-family);
        font-weight: 700;
        color: var(--sementicscolorprimaryon-default);
        font-size: 16px;
        letter-spacing: var(--sementic-type-label-l-letter-spacing);
        line-height: var(--sementic-type-label-l-line-height);
        white-space: nowrap;
        font-style: var(--sementic-type-label-l-font-style);
        position: relative;
        z-index: 1;
    }

    .screen .text-wrapper-10 {
        width: fit-content;
        font-family: var(--sementic-type-label-l-font-family);
        font-weight: 700;
        color: var(--sementicscolorprimaryon-subtle);
        font-size: 16px;
        letter-spacing: var(--sementic-type-label-l-letter-spacing);
        line-height: var(--sementic-type-label-l-line-height);
        white-space: nowrap;
        font-style: var(--sementic-type-label-l-font-style);
        position: relative;
        z-index: 1;
    }

    .screen .pricing-features {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 0 16px 16px 8px;
        width: 100%;
        box-sizing: border-box;
    }

    .screen .what-you-get {
        width: 100%;
        font-family: var(--sementic-type-label-l-font-family);
        font-weight: var(--sementic-type-label-l-font-weight);
        color: var(--sementicscolorfgdefault);
        font-size: 16px;
        letter-spacing: var(--sementic-type-label-l-letter-spacing);
        line-height: var(--sementic-type-label-l-line-height);
        font-style: var(--sementic-type-label-l-font-style);
        margin-bottom: 2px;
    }

    .screen .feature-row {
        display: flex;
        align-items: center;
        gap: 14px;
        width: 100%;
        padding: 10px 12px;
        border-radius: 14px;
        transition: background .22s ease, transform .22s ease;
    }

    .screen .feature-row:hover{
        background: rgba(37,99,235,.05);
        transform: translateX(4px);
    }

    .screen .checkmark {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
        filter: drop-shadow(0 4px 8px rgba(37,99,235,.16));
        animation: popIn .5s ease both;
    }

    .screen .text-wrapper-9 {
        flex: 1;
        font-family: var(--sementic-type-body-m-font-family);
        font-weight: 500;
        color: rgba(24,24,27,.88);
        font-size: var(--sementic-type-body-m-font-size);
        letter-spacing: var(--sementic-type-body-m-letter-spacing);
        line-height: 1.5;
        font-style: var(--sementic-type-body-m-font-style);
    }

    .modal-overlay{
        position:fixed;
        inset:0;
        background:rgba(15,23,42,.55);
        z-index:9999;
        display:none;
        align-items:center;
        justify-content:center;
        padding:16px;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .modal-box{
        width:min(720px, 100%);
        background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
        border-radius:20px;
        box-shadow:0 28px 80px rgba(0,0,0,.28);
        overflow:hidden;
        font-family: Inter, Arial, sans-serif;
        border: 1px solid rgba(228,228,231,.9);
        animation: modalPop .28s ease;
    }

    .modal-head{
        display:flex;
        justify-content:space-between;
        gap:12px;
        padding:16px 18px;
        border-bottom:1px solid var(--sementicscolorbordermuted);
        background: linear-gradient(180deg, rgba(37,99,235,.04), rgba(255,255,255,0));
    }

    .mh-title{
        font-weight:800;
        color:var(--sementicscolorfgdefault);
        font-size:18px;
    }

    .mh-sub{
        margin-top:4px;
        font-size:12px;
        color:rgba(24,24,27,.65);
    }

    .mh-close{
        all:unset;
        cursor:pointer;
        width:38px;
        height:38px;
        display:grid;
        place-items:center;
        border-radius:12px;
        border:1px solid var(--sementicscolorbordermuted);
        background:#fff;
        transition:.22s ease;
    }

    .mh-close:hover{
        background:#f6f7fb;
        transform: rotate(90deg);
    }

    .modal-body{
        padding:16px 18px 18px;
        display:flex;
        flex-direction:column;
        gap:14px;
    }

    .pay-summary{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:10px;
        background:linear-gradient(180deg,#f8fbff 0%,#f4f8ff 100%);
        border:1px solid var(--sementicscolorbordermuted);
        border-radius:14px;
        padding:12px;
    }

    .pay-item{
        padding:8px 10px;
        border-radius:12px;
        background: rgba(255,255,255,.72);
    }

    .pay-label{
        font-size:12px;
        color:rgba(24,24,27,.65);
        margin-bottom:4px;
    }

    .pay-value{
        font-size:13px;
        font-weight:700;
        color:var(--sementicscolorfgdefault);
    }

    .bank-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:12px;
    }

    .bank-field{
        display:flex;
        flex-direction:column;
        gap:6px;
    }

    .bank-field label{
        font-size:12px;
        color:rgba(24,24,27,.72);
        font-weight:600;
    }

    .bank-field input,
    .bank-field textarea,
    .bank-field select{
        width:100%;
        border:1px solid var(--sementicscolorbordermuted);
        border-radius:12px;
        padding:12px 13px;
        font-size:14px;
        outline:none;
        box-sizing:border-box;
        background:#fff;
        transition:.2s ease;
    }

    .bank-field input:focus,
    .bank-field textarea:focus,
    .bank-field select:focus{
        border-color: var(--sementicscolorprimarydefault);
        box-shadow: 0 0 0 4px rgba(37,99,235,.10);
    }

    .bank-field textarea{
        min-height:90px;
        resize:vertical;
    }

    .modal-actions{
        display:flex;
        justify-content:flex-end;
        gap:10px;
        padding-top:4px;
    }

    .m-btn{
        all:unset;
        cursor:pointer;
        border-radius:12px;
        padding:11px 16px;
        border:1px solid var(--sementicscolorbordermuted);
        background:#fff;
        font-weight:700;
        font-size:14px;
        transition:.22s ease;
    }

    .m-btn:hover{
        background:#f6f7fb;
        transform: translateY(-1px);
    }

    .m-btn.primary{
        background: linear-gradient(135deg, var(--sementicscolorprimarydefault), #1d4ed8);
        border-color: var(--sementicscolorprimarydefault);
        color: var(--sementicscolorprimaryon-default);
        box-shadow: 0 12px 24px rgba(37,99,235,.22);
    }

    .frame-2 .is-active{
        background: linear-gradient(135deg, var(--sementicscolorprimarydefault), #1d4ed8);
        box-shadow: 0 10px 22px rgba(37,99,235,.22);
    }

    .frame-2 .is-active .text-wrapper-3,
    .frame-2 .is-active .text-wrapper-2{
        color: var(--sementicscolorprimaryon-default);
    }

    .frame-2 .is-inactive{
        background: transparent !important;
    }

    .frame-2 .is-inactive .text-wrapper-2,
    .frame-2 .is-inactive .text-wrapper-3{
        color: var(--sementicscolorfgdisabled);
    }

    @keyframes fadeUp{
        from{ opacity:0; transform: translateY(24px); }
        to{ opacity:1; transform: translateY(0); }
    }

    @keyframes shimmer{
        to{ transform: translateX(120%); }
    }

    @keyframes floatY{
        0%,100%{ transform: translateY(0); }
        50%{ transform: translateY(-3px); }
    }

    @keyframes popIn{
        from{ opacity:0; transform: scale(.85); }
        to{ opacity:1; transform: scale(1); }
    }

    @keyframes modalPop{
        from{ opacity:0; transform: translateY(18px) scale(.98); }
        to{ opacity:1; transform: translateY(0) scale(1); }
    }

    .pricing-card .card-content::after,
    .pricing-card-2 .card-content::after{
        content:"";
        position:absolute;
        width:120px;
        height:120px;
        right:-40px;
        bottom:-40px;
        border-radius:50%;
        background: radial-gradient(circle, rgba(0,192,232,.10), transparent 70%);
        pointer-events:none;
    }

    @media (max-width: 1024px) {
        .screen .container-medium { padding: 40px 16px; }
        .screen .frame-3 { flex-direction: column; }
        .screen::before,
        .screen::after{ display:none; }
    }

    @media (max-width:720px){
        .pay-summary{ grid-template-columns:1fr; }
        .bank-grid{ grid-template-columns:1fr; }
    }
</style>

<div class="screen" style="width: 100%;max-width: 100%;">
    <?php include_once $_SERVER['DOCUMENT_ROOT'].'/Templates/admin/static_elements/navbox.php'?>

    <div class="container-medium">
        <header class="header">
            <div class="text-wrapper">Abonamentele mele</div>
            <div class="div">Planul Si abonamentele</div>
        </header>

        <div class="frame">
            <div class="frame-wrapper">
                <div class="frame-2">
                    <button class="button"><div class="text-wrapper-2">Anual</div></button>
                    <button class="div-wrapper"><div class="text-wrapper-3">Lunar</div></button>
                </div>
            </div>

            <div class="frame-3">
                <div class="pricing-card">
                    <div class="card-content">
                        <div class="pricing-headear">
                            <div class="frame-4">
                                <div class="text-wrapper-4">Free</div>
                                <div class="text-wrapper-5">Best for personal use</div>
                            </div>
                            <div class="frame-5">
                                <div class="text-wrapper-6">$0</div>
                                <div class="text-wrapper-7">/month</div>
                            </div>
                        </div>

                        <button class="button-2"><div class="text-wrapper-8">Get started</div></button>

                        <div class="pricing-features">
                            <div class="what-you-get">What you get:</div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">Task Management</div>
                            </div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">Project Planning</div>
                            </div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">Team Collaboration</div>
                            </div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">Notifications and Reminders</div>
                            </div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">What you get</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pricing-card-2">
                    <div class="puplar">
                        <div class="most-popular">Cel mai popular</div>
                    </div>

                    <div class="card-content">
                        <div class="pricing-headear">
                            <div class="frame-4">
                                <div class="text-wrapper-4">Premium</div>
                                <div class="text-wrapper-5">Best for personal use</div>
                            </div>
                            <div class="frame-5">
                                <div class="text-wrapper-6">$16</div>
                                <div class="text-wrapper-7">/month</div>
                            </div>
                        </div>

                        <button class="button-3"><div class="text-wrapper-8">Get started</div></button>

                        <div class="pricing-features">
                            <p class="what-you-get">All starter features, plus:</p>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">Customizable Workflows</div>
                            </div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">Reporting and Analytics</div>
                            </div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">Document Management</div>
                            </div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">Agile Methodology Support</div>
                            </div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">Issue Tracking</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pricing-card">
                    <div class="card-content">
                        <div class="pricing-headear">
                            <div class="frame-4">
                                <div class="text-wrapper-4">Advance</div>
                                <div class="text-wrapper-5">Best for personal use</div>
                            </div>
                            <div class="frame-5">
                                <div class="text-wrapper-6">Custom</div>
                            </div>
                        </div>

                        <button class="button-4"><div class="text-wrapper-10">Get started</div></button>

                        <div class="pricing-features">
                            <div class="what-you-get">All business features, plus:</div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">SSO</div>
                            </div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">All integrations</div>
                            </div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">Client Collaboration with 2FA</div>
                            </div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">Advanced Project Planning</div>
                            </div>
                            <div class="feature-row">
                                <img class="checkmark" src="<?php echo rtrim(getCurrentUrl(), '/'); ?>/Templates/admin/dist/img/checkmark.png" />
                                <div class="text-wrapper-9">Mobile App Integration</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="payModal" style="display:none;">
        <div class="modal-box">
            <div class="modal-head">
                <div class="mh-left">
                    <div class="mh-title">Achitare abonament</div>
                    <div class="mh-sub" id="payModalSub">Alege cardul salvat și confirmă plata.</div>
                </div>
                <button class="mh-close" type="button" id="payModalClose">✕</button>
            </div>

            <div class="modal-body">
                <div class="pay-summary">
                    <div class="pay-item">
                        <div class="pay-label">Plan</div>
                        <div class="pay-value" id="sumPlan">—</div>
                    </div>
                    <div class="pay-item">
                        <div class="pay-label">Perioadă</div>
                        <div class="pay-value" id="sumPeriod">—</div>
                    </div>
                    <div class="pay-item">
                        <div class="pay-label">Sumă</div>
                        <div class="pay-value" id="sumAmount">—</div>
                    </div>
                    <div class="pay-item">
                        <div class="pay-label">Referință</div>
                        <div class="pay-value" id="sumRef">—</div>
                    </div>
                </div>

                <div class="bank-grid">
                    <div class="bank-field" style="grid-column:1/-1;">
                        <label>Alege cardul salvat</label>
                        <select id="fSavedCard">
                            <option value="">Selectează cardul</option>
                            <?php foreach ($savedCards as $card): ?>
                                <option
                                        value="<?= htmlspecialchars((string)$card['randomn_id']) ?>"
                                        data-brand="<?= htmlspecialchars((string)$card['brand']) ?>"
                                        data-last4="<?= htmlspecialchars((string)$card['last4']) ?>"
                                        data-holder="<?= htmlspecialchars((string)$card['card_holder']) ?>"
                                        data-exp="<?= htmlspecialchars((string)$card['exp_month'] . '/' . (string)$card['exp_year']) ?>"
                                    <?= !empty($card['is_primary']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars((string)$card['brand']) ?> **** <?= htmlspecialchars((string)$card['last4']) ?>
                                    — <?= htmlspecialchars((string)$card['card_holder']) ?>
                                    — Exp <?= htmlspecialchars((string)$card['exp_month']) ?>/<?= htmlspecialchars((string)$card['exp_year']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="bank-field">
                        <label>Suma</label>
                        <input id="fAmount" type="text" placeholder="16 USD" readonly>
                    </div>

                    <div class="bank-field">
                        <label>Referință plată</label>
                        <input id="fRef" type="text" placeholder="Abonament Premium - Lunar - userID 123" readonly>
                    </div>

                    <div class="bank-field" style="grid-column:1/-1;">
                        <label>Detalii card selectat</label>
                        <input id="fCardPreview" type="text" value="" readonly>
                    </div>

                    <div class="bank-field" style="grid-column:1/-1;">
                        <label>Notițe</label>
                        <textarea id="fNote" placeholder="Opțional..."></textarea>
                    </div>
                </div>

                <div class="modal-actions">
                    <button class="m-btn" type="button" id="copyBank">Copiază detalii</button>
                    <button class="m-btn primary" type="button" id="confirmPay">Achită acum</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function(){
        const yearlyBtn  = document.querySelector('.frame-2 .button');
        const monthlyBtn = document.querySelector('.frame-2 .div-wrapper');
        const cards = document.querySelectorAll('.frame-3 > .pricing-card, .frame-3 > .pricing-card-2');

        const modal = document.getElementById('payModal');
        const closeBtn = document.getElementById('payModalClose');

        const sumPlan   = document.getElementById('sumPlan');
        const sumPeriod = document.getElementById('sumPeriod');
        const sumAmount = document.getElementById('sumAmount');
        const sumRef    = document.getElementById('sumRef');

        const fSavedCard   = document.getElementById('fSavedCard');
        const fAmount      = document.getElementById('fAmount');
        const fRef         = document.getElementById('fRef');
        const fCardPreview = document.getElementById('fCardPreview');
        const fNote        = document.getElementById('fNote');

        const copyBankBtn = document.getElementById('copyBank');
        const confirmPayBtn = document.getElementById('confirmPay');

        const USER_RANDOMN_ID = <?= json_encode($userRandomnId) ?>;

        const PRICES = {
            "Free":    { monthly: 0,  yearly: 0 },
            "Premium": { monthly: 16, yearly: 160 },
            "Advance": { monthly: 0, yearly: 0 }
        };

        let billing = 'monthly';
        let selectedPlan = '';
        let selectedAmount = 0;

        function exists(el){
            return el !== null && el !== undefined;
        }

        function getPlanName(card){
            const t = card ? card.querySelector('.text-wrapper-4') : null;
            return t ? t.textContent.trim() : '';
        }

        function animatePrice(el, nextValue){
            if(!exists(el)) return;
            el.animate([
                { opacity: .25, transform: 'translateY(8px) scale(.96)' },
                { opacity: 1, transform: 'translateY(0) scale(1)' }
            ], {
                duration: 260,
                easing: 'ease'
            });
            el.textContent = nextValue;
        }

        function updateCardPreview() {
            if (!exists(fSavedCard) || !exists(fCardPreview)) return;

            const opt = fSavedCard.options[fSavedCard.selectedIndex];
            if (!opt || !opt.value) {
                fCardPreview.value = 'Nu este selectat niciun card';
                return;
            }

            const brand = opt.dataset.brand || '';
            const last4 = opt.dataset.last4 || '';
            const holder = opt.dataset.holder || '';
            const exp = opt.dataset.exp || '';

            fCardPreview.value = `${brand} **** ${last4} — ${holder} — Exp ${exp}`;
        }

        function setBilling(mode){
            billing = mode;

            if (exists(monthlyBtn) && exists(yearlyBtn)) {
                if(mode === 'monthly'){
                    monthlyBtn.classList.add('is-active');
                    yearlyBtn.classList.add('is-inactive');
                    monthlyBtn.classList.remove('is-inactive');
                    yearlyBtn.classList.remove('is-active');
                } else {
                    monthlyBtn.classList.remove('is-active');
                    yearlyBtn.classList.remove('is-inactive');
                    monthlyBtn.classList.add('is-inactive');
                    yearlyBtn.classList.add('is-active');
                }
            }

            cards.forEach(card => {
                const plan = getPlanName(card);
                const priceEl  = card.querySelector('.text-wrapper-6');
                const periodEl = card.querySelector('.text-wrapper-7');

                if(!exists(priceEl)) return;

                if(plan === 'Advance'){
                    animatePrice(priceEl, 'Custom');
                    if(exists(periodEl)) periodEl.textContent = '';
                    return;
                }

                const cfg = PRICES[plan];
                if(!cfg) return;

                const value = (mode === 'monthly') ? cfg.monthly : cfg.yearly;
                animatePrice(priceEl, '$' + value);

                if(exists(periodEl)){
                    periodEl.textContent = (mode === 'monthly') ? '/month' : '/year';
                }
            });
        }

        function openModal(payload){
            if(!exists(modal)) return;

            selectedPlan = payload.plan;
            selectedAmount = payload.amountValue;

            if (exists(sumPlan))   sumPlan.textContent = payload.plan;
            if (exists(sumPeriod)) sumPeriod.textContent = payload.periodLabel;
            if (exists(sumAmount)) sumAmount.textContent = payload.amountLabel;
            if (exists(sumRef))    sumRef.textContent = payload.reference;

            if (exists(fAmount)) fAmount.value = payload.amountLabel;
            if (exists(fRef))    fRef.value = payload.reference;

            modal.style.display = 'flex';
            updateCardPreview();

            if (exists(fSavedCard)) {
                setTimeout(() => fSavedCard.focus(), 40);
            }
        }

        function closeModal(){
            if(exists(modal)) modal.style.display = 'none';
        }

        if (exists(closeBtn)) {
            closeBtn.addEventListener('click', closeModal);
        }

        if (exists(modal)) {
            modal.addEventListener('click', (e) => {
                if(e.target === modal) closeModal();
            });
        }

        document.addEventListener('keydown', (e) => {
            if(e.key === 'Escape' && exists(modal) && modal.style.display === 'flex'){
                closeModal();
            }
        });

        if (exists(yearlyBtn)) {
            yearlyBtn.addEventListener('click', () => setBilling('yearly'));
        }

        if (exists(monthlyBtn)) {
            monthlyBtn.addEventListener('click', () => setBilling('monthly'));
        }

        if (exists(fSavedCard)) {
            fSavedCard.addEventListener('change', updateCardPreview);
            updateCardPreview();
        }

        const buyButtons = document.querySelectorAll('.button-2, .button-3, .button-4');
        buyButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const card = btn.closest('.pricing-card, .pricing-card-2');
                const plan = getPlanName(card);
                const periodLabel = (billing === 'monthly') ? 'Lunar' : 'Anual';

                let amountValue = 0;
                let amountLabel = 'Custom';

                if(plan !== 'Advance'){
                    const cfg = PRICES[plan] || { monthly: 0, yearly: 0 };
                    amountValue = (billing === 'monthly') ? cfg.monthly : cfg.yearly;
                    amountLabel = '$' + amountValue + (billing === 'monthly' ? ' / month' : ' / year');
                }

                const ref = `Abonament ${plan} - ${periodLabel} - ${new Date().toISOString().slice(0,10)}`;

                openModal({
                    plan,
                    periodLabel,
                    amountLabel,
                    amountValue,
                    reference: ref
                });
            });
        });

        if (exists(copyBankBtn)) {
            copyBankBtn.addEventListener('click', async () => {
                const selectedOption = exists(fSavedCard) ? fSavedCard.options[fSavedCard.selectedIndex] : null;
                const selectedCardText = selectedOption && selectedOption.value
                    ? selectedOption.textContent.trim()
                    : 'Niciun card selectat';

                const text =
                    `DETALII ACHITARE
Plan: ${exists(sumPlan) ? sumPlan.textContent : '-'}
Perioada: ${exists(sumPeriod) ? sumPeriod.textContent : '-'}
Suma: ${exists(fAmount) ? fAmount.value : '-'}
Card selectat: ${selectedCardText}
Referinta: ${exists(fRef) ? fRef.value : '-'}
Note: ${exists(fNote) ? (fNote.value || '-') : '-'}`;

                try{
                    await navigator.clipboard.writeText(text);
                    alert('Copiat ✅');
                } catch(e){
                    const ta = document.createElement('textarea');
                    ta.value = text;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    ta.remove();
                    alert('Copiat ✅');
                }
            });
        }

        if (exists(confirmPayBtn)) {
            confirmPayBtn.addEventListener('click', async () => {
                if (!USER_RANDOMN_ID) {
                    alert('Lipsește user_randomn_id.');
                    return;
                }

                if (!exists(fSavedCard) || !fSavedCard.value) {
                    alert('Selectează un card pentru achitare.');
                    return;
                }

                const payload = new FormData();
                payload.append('type_product', 'purchase_subscription');
                payload.append('user_randomn_id', USER_RANDOMN_ID);
                payload.append('plan_name', selectedPlan);
                payload.append('billing_cycle', billing);
                payload.append('currency', 'USD');
                payload.append('payment_card_randomn_id', fSavedCard.value);
                payload.append('payment_reference', exists(fRef) ? fRef.value : '');
                payload.append('note', exists(fNote) ? fNote.value : '');

                const oldText = confirmPayBtn.textContent;
                confirmPayBtn.disabled = true;
                confirmPayBtn.textContent = 'Se procesează...';

                try {
                    const response = await fetch('/public/crudmybank', {
                        method: 'POST',
                        body: payload
                    });

                    const result = await response.json();

                    if (!result.success) {
                        alert(result.message || 'Eroare la salvarea abonamentului.');
                        confirmPayBtn.disabled = false;
                        confirmPayBtn.textContent = oldText;
                        return;
                    }

                    alert(
                        'Abonamentul a fost înregistrat.\n' +
                        'Invoice: ' + (result.invoice_no || '-') + '\n' +
                        'Plan: ' + (result.plan_name || '-') + '\n' +
                        'Card: ' + (result.card_mask || '-') + '\n' +
                        'Status: paid'
                    );

                    closeModal();

                    setTimeout(() => {
                        window.location.href = '/public/mybank';
                    }, 400);
                } catch (e) {
                    console.error(e);
                    alert('Eroare la request.');
                    confirmPayBtn.disabled = false;
                    confirmPayBtn.textContent = oldText;
                }
            });
        }

        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(28px)';
            setTimeout(() => {
                card.style.transition = 'all .55s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 140 + index * 120);
        });

        document.querySelectorAll('.feature-row').forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateX(-10px)';
            setTimeout(() => {
                row.style.transition = 'all .4s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateX(0)';
            }, 380 + index * 45);
        });

        setBilling('monthly');
    })();
</script>