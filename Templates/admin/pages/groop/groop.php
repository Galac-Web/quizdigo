<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: /public/login');
    exit;
}

use Evasystem\Controllers\Librari\Librari;
use Evasystem\Controllers\Librari\LibrariService;

$id_user = (int)$_SESSION['user_id'];

$librariService = new LibrariService();
$librariController = new Librari($librariService);

$folderRandomnId = isset($_GET['folder']) ? (string)$_GET['folder'] : null;
$folderIdFromGet = isset($_GET['id']) ? (int)$_GET['id'] : null;
$view = isset($_GET['view']) ? (string)$_GET['view'] : 'all';

$data = $librariController->index($id_user, $folderRandomnId, $view);

$stats = $data['stats'] ?? [];
$folders = $data['folders'] ?? [];
$quizzes = $data['quizzes'] ?? [];
$activeFolder = $data['active_folder'] ?? null;

$total_quizzes = (int)($stats['total_quizzes'] ?? 0);
$limit = (int)($stats['limit_quizzes'] ?? 200);
$procent = (float)($stats['percent'] ?? 0);

/**
 * Compatibilitate cu layoutul vechi:
 * dacă vine ?id=12&name=Folder, atunci marcăm folderul activ după id
 */
$activeFolderId = null;
if ($activeFolder && isset($activeFolder['id'])) {
    $activeFolderId = (int)$activeFolder['id'];
} elseif ($folderIdFromGet) {
    $activeFolderId = $folderIdFromGet;
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}


$userId = $id_user;
?>
<style>
    .kgroups_app{width:100%}
    .kgroups_app{
        --kg-primary:#1f63a8;
        --kg-primary-dark:#174f88;
        --kg-blue:#2d79c7;
        --kg-blue-dark:#1b5b99;
        --kg-bg:#eef3f9;
        --kg-panel:rgba(255,255,255,0.96);
        --kg-line:#d9e3ef;
        --kg-text:#0f172a;
        --kg-muted:#6b7b93;
        --kg-overlay:rgba(15,23,42,0.58);
        --kg-shadow:0 18px 40px rgba(30,99,169,0.08);
        font-family:Inter,Arial,Helvetica,sans-serif;
        color:var(--kg-text);
        background:var(--kg-bg);
        min-height:100vh
    }
    .kgroups_app *{box-sizing:border-box}
    .kgroups_app button,.kgroups_app input,.kgroups_app textarea{font:inherit}
    .kgroups_main{display:grid;grid-template-columns:280px minmax(0,1fr);gap:20px;padding:20px;min-height:100vh;align-items:start}
    .kgroups_groups_sidebar{background:linear-gradient(180deg,rgba(255,255,255,.96) 0%,rgba(248,251,255,.96) 100%);border:1px solid var(--kg-line);border-radius:28px;padding:24px 18px;box-shadow:var(--kg-shadow)}
    .kgroups_groups_divider{border-top:1px solid var(--kg-line);margin:18px 0 28px}
    .kgroups_groups_title{color:#7a8ca6;font-size:12px;letter-spacing:.08em;text-transform:uppercase;font-weight:800;margin-bottom:16px}
    .kgroups_recent_item{min-height:54px;border-radius:16px;background:linear-gradient(135deg,#d8e9fb 0%,#eef4ff 100%);color:#1d4f86;display:flex;align-items:center;justify-content:space-between;padding:0 16px;font-size:16px;font-weight:800;margin-bottom:12px;box-shadow:0 10px 24px rgba(59,130,246,.08);cursor:pointer;transition:.2s ease;border:1px solid transparent}
    .kgroups_recent_item:hover{transform:translateY(-2px);border-color:rgba(45,121,199,.18)}
    .kgroups_recent_item.is_active{background:linear-gradient(135deg,#2d79c7 0%,#4a99e6 100%);color:#fff}
    .kgroups_group_count{font-size:12px;font-weight:700;opacity:.8}
    .kgroups_create_group_link{color:var(--kg-blue-dark);font-size:15px;cursor:pointer;font-weight:800;padding:12px 14px;border-radius:14px;background:rgba(45,121,199,.06);display:inline-flex;align-items:center;gap:8px;transition:.2s ease}
    .kgroups_create_group_link:hover{background:rgba(45,121,199,.12)}
    .kgroups_content{padding:0}
    .kgroups_toolbar,.kgroups_setup,.kgroups_post_card{background:var(--kg-panel);border:1px solid rgba(217,227,239,.9);box-shadow:var(--kg-shadow)}
    .kgroups_toolbar_modern,.kgroups_setup_modern{border-radius:28px}
    .kgroups_toolbar{min-height:92px;display:flex;align-items:center;justify-content:center;gap:16px;padding:20px;flex-wrap:wrap}
    .kgroups_toolbar_action{border:0;background:#f8fbff;color:var(--kg-blue-dark);font-size:15px;cursor:pointer;display:flex;align-items:center;gap:10px;font-weight:800;padding:14px 18px;border-radius:16px;border:1px solid rgba(45,121,199,.08)}
    .kgroups_toolbar_action:hover{background:#edf5ff}
    .kgroups_setup{padding:22px;margin-top:20px;margin-bottom:20px}
    .kgroups_setup_header{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:16px}
    .kgroups_setup_title{font-size:22px;font-weight:900;color:var(--kg-blue-dark)}
    .kgroups_setup_skip{border:0;background:transparent;color:var(--kg-blue);font-size:15px;cursor:pointer;font-weight:700}
    .kgroups_setup_grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
    .kgroups_setup_box{min-height:170px;border-radius:22px;border:1px solid var(--kg-line);background:linear-gradient(180deg,#fff 0%,#fbfdff 100%);position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:24px 18px;cursor:pointer}
    .kgroups_setup_box:hover,.kgroups_post_card:hover{transform:translateY(-3px);box-shadow:0 22px 50px rgba(29,35,80,0.10)}
    .kgroups_setup_check{position:absolute;left:16px;top:16px;width:30px;height:30px;border-radius:50%;background:#17a34a;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;font-weight:800}
    .kgroups_setup_illustration{font-size:46px;margin-bottom:14px}
    .kgroups_setup_text{font-size:17px;color:#243b57;font-weight:800}
    .kgroups_posts_stack{display:grid;gap:16px}
    .kgroups_post_card{overflow:hidden;border-radius:22px}
    .kgroups_post_header{display:flex;justify-content:space-between;gap:16px;padding:16px 18px}
    .kgroups_post_meta{display:flex;align-items:center;gap:12px;min-width:0}
    .kgroups_post_avatar,.kgroups_member_avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#2d79c7,#4ea3ea);color:#fff;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;flex:0 0 auto}
    .kgroups_post_meta_text{font-size:16px;line-height:1.35;color:#20324d;font-weight:600}
    .kgroups_post_time{color:#6b7280;font-size:13px;white-space:nowrap;display:flex;align-items:center;gap:10px}
    .kgroups_post_body{padding:4px 18px 22px;font-size:16px;white-space:pre-wrap;line-height:1.65;color:#334155}
    .kgroups_post_likes{padding:0 18px 14px;font-size:14px;color:#64748b}
    .kgroups_post_footer{border-top:1px solid rgba(148,163,184,0.14);padding:12px 18px}
    .kgroups_like_btn{border:0;background:transparent;color:var(--kg-blue-dark);font-size:15px;font-weight:800;cursor:pointer;padding:0}
    .kgroups_modal_overlay{position:fixed;inset:0;background:var(--kg-overlay);display:none;align-items:center;justify-content:center;padding:22px;z-index:999}
    .kgroups_modal_overlay.is_open{display:flex}
    .kgroups_modal{width:100%;max-width:640px;border-radius:24px;overflow:hidden}
    .kgroups_modal_wide{max-width:820px}
    .kgroups_modal_glass{background:rgba(255,255,255,0.98);backdrop-filter:blur(12px);box-shadow:0 30px 80px rgba(15,23,42,0.22)}
    .kgroups_modal_header{padding:18px 24px;border-bottom:1px solid rgba(148,163,184,0.14);display:flex;align-items:center;justify-content:space-between;gap:20px}
    .kgroups_modal_title{font-size:28px;font-weight:900;margin:0;color:var(--kg-blue-dark)}
    .kgroups_modal_close{border:0;background:transparent;color:#64748b;font-size:28px;line-height:1;cursor:pointer}
    .kgroups_modal_body{padding:24px}
    .kgroups_field_group{margin-bottom:16px}
    .kgroups_textarea_wrap,.kgroups_input_row,.kgroups_search_large input,.kgroups_members_list,.kgroups_attach_row{border:1px solid var(--kg-line)}
    .kgroups_textarea_wrap{position:relative;border-radius:16px;background:#fff;overflow:hidden}
    .kgroups_textarea{width:100%;min-height:130px;border:0;outline:none;resize:none;padding:16px 70px 16px 18px;font-size:16px;background:transparent}
    .kgroups_counter{position:absolute;right:14px;top:14px;color:#64748b;font-size:14px;font-weight:700}
    .kgroups_attach_row{min-height:76px;border-radius:16px;display:flex;align-items:center;justify-content:space-between;padding:0 20px;margin-bottom:24px;background:#fff}
    .kgroups_attach_title{font-size:16px;font-weight:700}
    .kgroups_attach_icons{display:flex;gap:18px;color:var(--kg-blue);font-size:24px}
    .kgroups_modal_actions,.kgroups_center_actions{display:flex;justify-content:center;gap:12px}
    .kgroups_btn_gray,.kgroups_btn_blue{min-width:144px;height:48px;border-radius:14px;border:0;font-size:15px;font-weight:800;cursor:pointer;transition:.2s ease}
    .kgroups_btn_gray{background:#eef2f7;color:#111827}
    .kgroups_btn_blue,.kgroups_copy_btn{background:linear-gradient(135deg,#1f63a8 0%,#2d79c7 100%);color:#fff}
    .kgroups_btn_blue:hover,.kgroups_btn_gray:hover{transform:translateY(-2px)}
    .kgroups_btn_blue:disabled{background:#cbd5e1;color:#fff;cursor:not-allowed;transform:none}
    .kgroups_label{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:10px;font-size:16px;font-weight:800}
    .kgroups_label_link{color:var(--kg-blue);cursor:pointer;font-weight:700}
    .kgroups_input_row{display:grid;grid-template-columns:1fr auto;border-radius:16px;overflow:hidden;margin-bottom:26px;background:#fff}
    .kgroups_input{height:50px;border:0;outline:none;padding:0 14px;font-size:15px;color:#475569;background:#fff}
    .kgroups_copy_btn{min-width:84px;border:0;font-size:14px;font-weight:800;cursor:pointer;padding:0 18px}
    .kgroups_search_large{position:relative;margin-bottom:18px}
    .kgroups_search_large input{width:100%;height:52px;border-radius:16px;padding:0 56px 0 18px;font-size:15px;outline:none;background:#fff}
    .kgroups_search_large_icon{position:absolute;right:16px;top:50%;transform:translateY(-50%);font-size:24px;color:#64748b}
    .kgroups_empty_box{min-height:380px;border:1px dashed rgba(148,163,184,.26);background:linear-gradient(180deg,#f8fbff 0%,#f3f8ff 100%);border-radius:22px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:36px;margin-bottom:24px}
    .kgroups_empty_icon{font-size:88px;color:#cbd5e1;line-height:1;margin-bottom:18px}
    .kgroups_empty_text{max-width:560px;font-size:18px;color:var(--kg-muted);margin-bottom:24px;line-height:1.6}
    .kgroups_members_list{margin-top:14px;border-radius:18px;overflow:hidden;background:#fff}
    .kgroups_member_row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px;border-bottom:1px solid rgba(148,163,184,.12);background:#fff}
    .kgroups_member_row:last-child{border-bottom:0}
    .kgroups_member_meta{display:flex;align-items:center;gap:12px;min-width:0}
    .kgroups_member_name{font-size:15px;font-weight:800;color:#1f2937}
    .kgroups_member_email{font-size:13px;color:var(--kg-muted)}
    .kgroups_add_member_box{display:grid;grid-template-columns:1fr auto;gap:10px;margin-top:18px}
    .kgroups_status_badge{display:inline-flex;align-items:center;justify-content:center;min-width:86px;height:30px;padding:0 10px;border-radius:999px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
    .kgroups_status_pending{background:#fff7ed;color:#c2410c;border:1px solid #fdba74}
    .kgroups_status_accepted{background:#ecfdf5;color:#047857;border:1px solid #6ee7b7}
    .kgroups_status_rejected{background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5}
    .kgroups_status_expired{background:#f8fafc;color:#475569;border:1px solid #cbd5e1}
    .kgroups_member_actions{display:flex;align-items:center;gap:8px}
    .kgroups_modal_body {
        padding: 24px;
        max-height: 70vh;
        overflow-y: auto;
    }

    /* optional smooth scroll */
    .kgroups_modal_body::-webkit-scrollbar {
        width: 6px;
    }
    .kgroups_modal_body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .quiz-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        padding: 20px 0;
    }

    .quiz {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 1px solid #f0f0f0;
        position: relative;
    }

    .quiz:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 25px rgba(46, 133, 199, 0.15);
    }

    .quiz .cover {
        height: 160px;
        width: 100%;
        background-color: #eee;
    }

    .quiz .badge-circle {
        position: absolute;
        top: 140px;
        right: 20px;
        width: 45px;
        height: 45px;
        background: #fff;
        border-radius: 50%;
        padding: 5px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .quiz .body {
        padding: 25px 20px 20px;
    }

    .quiz .title {
        font-weight: 900;
        font-size: 18px;
        color: #0A5084;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .quiz .desc {
        font-size: 13px;
        color: #64748b;
        line-height: 1.5;
    }

    .quiz-footer {
        padding: 10px 20px;
        border-top: 1px solid #f8fafc;
        background: #fcfcfc;
    }
    .list_datte{
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        align-items: center;
        margin-left: 0;
        padding-left: 6px;
        padding-right: 12px;
    }
    .list_datte li{
        list-style: none;
        margin-bottom: 5px;
    }
    @media (max-width:1100px){.kgroups_setup_grid{grid-template-columns:1fr}}


    @media (max-width:860px){
        .kgroups_main{grid-template-columns:1fr}
        .kgroups_groups_sidebar{border-right:0;border-bottom:1px solid var(--kg-line)}
        .kgroups_content{padding:16px}
        .kgroups_toolbar{justify-content:flex-start}
        .kgroups_add_member_box,.kgroups_input_row{grid-template-columns:1fr}
    }
</style>



<div class="kgroups_app" id="kgroupsApp" data-group-id="" data-user-id="<?= (int)$userId ?>">
    <div class="kgroups_main">
        <aside class="kgroups_groups_sidebar">
            <button class="kgroups_toolbar_action" style="width:100%;" type="button" data-open-modal="inviteModal">🔗 <span>Share</span></button>

            <div class="kgroups_groups_divider"></div>

            <div class="kgroups_groups_title">Recent groups</div>
            <div id="groupsList"></div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <div class="kgroups_create_group_link" id="createGroupBtn">Create group +</div>
                <div class="kgroups_create_group_link" id="deleteGroupBtn" style="background:#fee2e2;color:#991b1b;">Delete group</div>
            </div>
        </aside>

        <section class="kgroups_content">
            <div class="kgroups_setup kgroups_setup_modern">


                <div class="kgroups_setup_grid">
                    <div class="kgroups_setup_box" data-open-modal="inviteModal">
                        <div class="kgroups_setup_check">✓</div>
                        <div class="kgroups_setup_illustration">👥</div>
                        <div class="kgroups_setup_text">Invite members</div>
                    </div>

                    <div class="kgroups_setup_box" data-open-modal="selectKahootModal">
                        <div class="kgroups_setup_illustration">☑</div>
                        <div class="kgroups_setup_text">Assign</div>
                    </div>

                    <div class="kgroups_setup_box" data-open-modal="postModal">
                        <div class="kgroups_setup_check">✓</div>
                        <div class="kgroups_setup_illustration">📣</div>
                        <div class="kgroups_setup_text">Create post</div>
                    </div>
                </div>
            </div>

            <div id="postsContainer" class="kgroups_posts_stack"></div>
            <div style="margin-top:20px;">
                <div class="kgroups_setup kgroups_setup_modern">
                    <div class="kgroups_setup_header">
                        <div class="kgroups_setup_title">Quizuri în grup</div>
                    </div>
                    <div id="attachedQuizzesList" class="kgroups_posts_stack"></div>
                </div>
            </div>
        </section>
    </div>

    <div class="kgroups_modal_overlay" id="postModal">
        <div class="kgroups_modal kgroups_modal_glass">
            <div class="kgroups_modal_header">
                <h3 class="kgroups_modal_title">Create post</h3>
                <button class="kgroups_modal_close" type="button" data-close-modal="postModal">×</button>
            </div>

            <div class="kgroups_modal_body">
                <div class="kgroups_field_group">
                    <div class="kgroups_textarea_wrap">
                        <textarea class="kgroups_textarea" id="postMessage" maxlength="500" placeholder="Enter your message"></textarea>
                        <div class="kgroups_counter"><span id="postCounter">500</span></div>
                    </div>
                </div>

                <div class="kgroups_attach_row">
                    <div class="kgroups_attach_title">Add to your post</div>
                    <div class="kgroups_attach_icons">
                        <span>🔗</span>
                        <span>☑</span>
                    </div>
                </div>

                <div class="kgroups_modal_actions">
                    <button class="kgroups_btn_gray" type="button" data-close-modal="postModal">Cancel</button>
                    <button class="kgroups_btn_blue" type="button" id="postSubmitBtn" disabled>Post</button>
                </div>
            </div>
        </div>
    </div>

    <div class="kgroups_modal_overlay" id="inviteModal">
        <div class="kgroups_modal kgroups_modal_wide kgroups_modal_glass">
            <div class="kgroups_modal_header">
                <h3 class="kgroups_modal_title">Invite users</h3>
                <button class="kgroups_modal_close" type="button" data-close-modal="inviteModal">×</button>
            </div>

            <div class="kgroups_modal_body">
                <div class="kgroups_label">
                    <span>Share invite link</span>
                    <span class="kgroups_label_link" id="resetInviteLink">Reset link</span>
                </div>

                <div class="kgroups_input_row">
                    <input class="kgroups_input" type="text" id="inviteLinkInput" readonly>
                    <button class="kgroups_copy_btn" type="button" id="copyInviteLinkBtn">Copy</button>
                </div>

                <div class="kgroups_label">
                    <span>Add member by link / email</span>
                </div>

                <div class="kgroups_add_member_box">
                    <input class="kgroups_input" type="text" id="memberInput" placeholder="Enter email">
                    <button class="kgroups_btn_blue" type="button" id="addMemberBtn">Add member</button>
                </div>

                <div class="kgroups_members_list" id="membersList"></div>

                <div class="kgroups_label" style="margin-top:18px;">
                    <span>Invitations sent</span>
                </div>

                <div class="kgroups_members_list" id="invitesList"></div>

                <div class="kgroups_center_actions" style="margin-top:30px;">
                    <button class="kgroups_btn_gray" type="button" data-close-modal="inviteModal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="kgroups_modal_overlay" id="selectKahootModal">
        <div class="kgroups_modal kgroups_modal_wide kgroups_modal_glass">
            <div class="kgroups_modal_header">
                <h3 class="kgroups_modal_title">Select content</h3>
                <button class="kgroups_modal_close" type="button" data-close-modal="selectKahootModal">×</button>
            </div>

            <div class="kgroups_modal_body">



                <div class="kgroups_search_large">
                    <input type="text" id="quizSearchInput" placeholder="Search items">
                    <span class="kgroups_search_large_icon">⌕</span>
                </div>

                <div class="kgroups_empty_box">
                    <div class="quiz-row">
                    <?php foreach ($quizzes as $q): ?>
                        <?php
                        $qid = (int)$q['id'];

                        $content = json_decode((string)$q['continut_json'], true) ?: [];
                        $settings = $content['settings'] ?? [];

                        $coverImg = !empty($settings['themeUrl']) ? (string)$settings['themeUrl'] : 'default-cover.png';
                        $titluQuiz = !empty($settings['title']) ? (string)$settings['title'] : (string)$q['titlu'];

                        $descriere = !empty($settings['description']) ? (string)$settings['description'] : 'Nicio descriere disponibilă.';
                        $descriereShort = mb_strimwidth($descriere, 0, 100, "...");
                        $lastUpdated = !empty($q['last_updated']) ? date('d.m.Y', strtotime($q['last_updated'])) : '-';
                        ?>
                        <article class="quiz" data-title="<?php echo strtolower(h($titluQuiz)); ?>" data-quiz-open="<?php echo $qid; ?>">
                            <div class="cover" style="background-image:url('<?php echo h($coverImg); ?>'); background-size:cover; background-position:center;"></div>

                            <div class="badge-circle">
                                <img src="<?php echo getCurrentUrl(); ?>/Templates/admin/dist/img/Mask Group.png" alt="">
                            </div>

                            <div class="body">
                                <div class="box_content" style="display:flex; flex-wrap:wrap; justify-content:space-between">
                                    <div class="left_content">
                                        <div class="title"><?php echo h($titluQuiz); ?></div>
                                        <div class="desc"><?php echo h($descriereShort); ?></div>
                                    </div>
                                </div>
                            </div>


                            <div class="quiz-footer">

                                <div class="button_joca">
                                    <button class="kgroups_btn_blue add_to_groop" type="button" data-attach-quiz="<?= (int)$q['id']; ?>">Add to group</button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    </div>
                    <a href="/public/librari" style="display: block;padding: 15px;" class="kgroups_btn_blue" type="button">Adauga un quizz Nou</a>
                </div>

                <div class="kgroups_center_actions">
                    <button class="kgroups_btn_gray" type="button" data-close-modal="selectKahootModal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const quizSearchInput = document.getElementById('quizSearchInput');

    if (quizSearchInput) {
        quizSearchInput.addEventListener('input', function () {
            const value = this.value.toLowerCase().trim();

            const quizzes = document.querySelectorAll('.quiz');

            quizzes.forEach(q => {
                const title = q.dataset.title || '';

                if (title.includes(value)) {
                    q.style.display = 'block';
                } else {
                    q.style.display = 'none';
                }
            });
        });
    }
</script>
<script>
    (function () {
        "use strict";

        const API_URL = "/public/crudgroop";
        const app = document.getElementById("kgroupsApp");

        if (!app) {
            console.error("kgroupsApp nu a fost găsit.");
            return;
        }

        const state = {
            currentGroupId: sessionStorage.getItem("active_group_id") || "",
            currentUserId: app.dataset.userId || ""
        };

        const el = {
            postModal: document.getElementById("postModal"),
            inviteModal: document.getElementById("inviteModal"),
            selectKahootModal: document.getElementById("selectKahootModal"),

            groupsList: document.getElementById("groupsList"),
            membersList: document.getElementById("membersList"),
            invitesList: document.getElementById("invitesList"),
            postsContainer: document.getElementById("postsContainer"),
            attachedQuizzesList: document.getElementById("attachedQuizzesList"),

            createGroupBtn: document.getElementById("createGroupBtn"),
            deleteGroupBtn: document.getElementById("deleteGroupBtn"),

            memberInput: document.getElementById("memberInput"),
            addMemberBtn: document.getElementById("addMemberBtn"),

            postMessage: document.getElementById("postMessage"),
            postCounter: document.getElementById("postCounter"),
            postSubmitBtn: document.getElementById("postSubmitBtn"),

            inviteLinkInput: document.getElementById("inviteLinkInput"),
            copyInviteLinkBtn: document.getElementById("copyInviteLinkBtn"),
            resetInviteLink: document.getElementById("resetInviteLink")
        };

        let groupsCache = [];

        function escapeHtml(str) {
            return String(str ?? "")
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        async function api(data) {
            const response = await fetch(API_URL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json;charset=UTF-8"
                },
                body: JSON.stringify(data)
            });

            const text = await response.text();

            let json;
            try {
                json = JSON.parse(text);
            } catch (e) {
                throw new Error("Răspuns invalid de la server: " + text);
            }

            if (!json.success) {
                throw new Error(json.message || "Request failed");
            }

            return json;
        }

        function getCurrentGroup() {
            return groupsCache.find(g =>
                String(g.id) === String(state.currentGroupId) ||
                String(g.randomn_id) === String(state.currentGroupId)
            ) || null;
        }

        function openModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;

            const currentGroup = getCurrentGroup();
            if (currentGroup) {
                modal.dataset.groupId = currentGroup.id || currentGroup.randomn_id || "";
            }

            modal.classList.add("is_open");
        }

        function closeModal(id) {

            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove("is_open");
        }

        function closeAllModals() {
            ["postModal", "inviteModal", "selectKahootModal"].forEach(closeModal);
        }

        function updatePostCounter() {
            if (!el.postMessage || !el.postCounter || !el.postSubmitBtn) return;

            const max = 500;
            const left = max - el.postMessage.value.length;
            el.postCounter.textContent = String(left);
            el.postSubmitBtn.disabled = el.postMessage.value.trim().length === 0;
        }

        function renderGroups(groups) {
            if (!el.groupsList) return;

            el.groupsList.innerHTML = "";

            if (!groups.length) {
                el.groupsList.innerHTML = `<div style="padding:10px 0;color:#64748b;">No groups yet.</div>`;
                return;
            }

            groups.forEach(group => {
                const item = document.createElement("div");
                item.className = "kgroups_recent_item" + (String(group.id) === String(state.currentGroupId) ? " is_active" : "");
                item.dataset.groupId = group.id;
                item.innerHTML = `
                <span>${escapeHtml(group.title || group.titlu || "Untitled Group")}</span>
                <span class="kgroups_group_count">${Number(group.members_count || 0)}</span>
            `;
                el.groupsList.appendChild(item);
            });
        }

        function renderMembers(members) {
            if (!el.membersList) return;

            el.membersList.innerHTML = "";

            if (!members || !members.length) {
                el.membersList.innerHTML = `<div style="padding:16px;color:#64748b;">No members yet.</div>`;
                return;
            }

            members.forEach(member => {
                const memberId = member.randomn_id || member.id || "";
                const memberName = member.titlu || member.name || member.email || "Member";

                const row = document.createElement("div");
                row.className = "kgroups_member_row";
                row.innerHTML = `
                <div class="kgroups_member_meta">
                    <div class="kgroups_member_avatar">${escapeHtml(String(memberName).charAt(0).toUpperCase())}</div>
                    <div>
                        <div class="kgroups_member_name">${escapeHtml(memberName)}</div>
                        <div class="kgroups_member_email">User ID: ${escapeHtml(member.id_users || "")}</div>
                    </div>
                </div>
                <button class="kgroups_btn_gray" type="button" data-remove-member="${escapeHtml(memberId)}" style="min-width:110px;height:40px;">Remove</button>
            `;
                el.membersList.appendChild(row);
            });
        }

        function getStatusClass(status) {
            const value = String(status || "").toLowerCase();
            if (value === "accepted") return "kgroups_status_badge kgroups_status_accepted";
            if (value === "rejected") return "kgroups_status_badge kgroups_status_rejected";
            if (value === "expired") return "kgroups_status_badge kgroups_status_expired";
            return "kgroups_status_badge kgroups_status_pending";
        }

        function renderInvites(invites) {
            if (!el.invitesList) return;

            el.invitesList.innerHTML = "";

            if (!invites || !invites.length) {
                el.invitesList.innerHTML = `<div style="padding:16px;color:#64748b;">No invitations sent yet.</div>`;
                return;
            }

            invites.forEach(invite => {
                const inviteId = invite.randomn_id || invite.id || "";
                const email = invite.email || "";
                const status = invite.status || "pending";

                const row = document.createElement("div");
                row.className = "kgroups_member_row";
                row.innerHTML = `
                <div class="kgroups_member_meta">
                    <div class="kgroups_member_avatar">@</div>
                    <div>
                        <div class="kgroups_member_name">${escapeHtml(email)}</div>
                        <div class="kgroups_member_email">Status: ${escapeHtml(status)}</div>
                    </div>
                </div>
                <div class="kgroups_member_actions">
                    <span class="${getStatusClass(status)}">${escapeHtml(status)}</span>
                    <button class="kgroups_btn_gray" type="button" data-delete-invite="${escapeHtml(inviteId)}" style="min-width:90px;height:40px;">Delete</button>
                </div>
            `;
                el.invitesList.appendChild(row);
            });
        }

        function renderPosts(posts) {
            if (!el.postsContainer) return;

            el.postsContainer.innerHTML = "";

            if (!posts || !posts.length) {
                el.postsContainer.innerHTML = `
                <div class="kgroups_post_card">
                    <div class="kgroups_post_body">No posts yet. Create the first post.</div>
                </div>
            `;
                return;
            }

            posts.forEach(post => {
                const postId = post.randomn_id || post.id || "";
                const author = post.titlu || "User";
                const message = post.mesaj || "";
                const likes = Number(post.likes || 0);
                const createdAt = post.created_at || "";

                const card = document.createElement("div");
                card.className = "kgroups_post_card";
                card.innerHTML = `
                <div class="kgroups_post_header">
                    <div class="kgroups_post_meta">
                        <div class="kgroups_post_avatar">${escapeHtml(String(author).charAt(0).toUpperCase())}</div>
                        <div class="kgroups_post_meta_text"><strong>${escapeHtml(author)}</strong> posted a message</div>
                    </div>
                    <div class="kgroups_post_time">${escapeHtml(createdAt)}</div>
                </div>
                <div class="kgroups_post_body">${escapeHtml(message)}</div>
                <div class="kgroups_post_likes">${likes} likes</div>
                <div class="kgroups_post_footer">
                    <button class="kgroups_like_btn" type="button" data-like-post="${escapeHtml(postId)}">👍 Like</button>
                </div>
            `;
                el.postsContainer.appendChild(card);
            });
        }

        function renderAttachedQuizzes(attached) {
            if (!el.attachedQuizzesList) return;

            el.attachedQuizzesList.innerHTML = "";

            if (!attached || !attached.length) {
                el.attachedQuizzesList.innerHTML = `<div class="kgroups_empty_box"><div class="kgroups_empty_text">Nu ai quizuri adăugate în acest grup.</div></div>`;
                return;
            }

            attached.forEach(item => {
                const quizId = Number(item.quiz_id || 0);

                const card = document.createElement("div");
                card.className = "quiz";
                card.innerHTML = `
                <div class="body">
                    <div class="title">Quiz #${quizId}</div>
                    <div class="desc">Quiz atașat grupului.</div>
                </div>
                <div class="quiz-footer">
                    <div class="button_joca" style="display:flex;gap:10px;flex-wrap:wrap;">
                        <a href="/public/desquizz?id=${quizId}" class="kgroups_btn_blue" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;min-width:120px;">Play</a>
                        <button class="kgroups_btn_gray" type="button" data-remove-attached-quiz="${quizId}" style="min-width:140px;">Remove</button>
                    </div>
                </div>
            `;
                el.attachedQuizzesList.appendChild(card);
            });
        }

        async function loadGroups() {
            const res = await api({
                type_product: "init_groups",
                id_users: state.currentUserId
            });

            groupsCache = Array.isArray(res.groups) ? res.groups.map(group => ({
                ...group,
                id: group.randomn_id || group.id || "",
                randomn_id: group.randomn_id || group.id || "",
                title: group.titlu || group.title || "Untitled Group"
            })) : [];

            const exists = state.currentGroupId && groupsCache.some(g => String(g.id) === String(state.currentGroupId));
            if (!exists) {
                state.currentGroupId = groupsCache.length ? String(groupsCache[0].id) : "";
                if (state.currentGroupId) {
                    sessionStorage.setItem("active_group_id", state.currentGroupId);
                }
            }

            renderGroups(groupsCache);

            if (state.currentGroupId) {
                await loadGroup(state.currentGroupId, false);
            } else {
                renderMembers([]);
                renderInvites([]);
                renderPosts([]);
                renderAttachedQuizzes([]);
            }
        }

        async function loadGroup(groupId, syncBackend = true) {
            if (!groupId) return;

            const res = await api({
                type_product: "load_group",
                group_id: groupId,
                id_users: state.currentUserId
            });

            state.currentGroupId = String(groupId);
            sessionStorage.setItem("active_group_id", state.currentGroupId);

            if (syncBackend) {
                try {
                    await api({
                        type_product: "activate",
                        id: groupId
                    });
                } catch (e) {
                    console.warn(e.message);
                }
            }

            if (el.inviteLinkInput) {
                el.inviteLinkInput.value = res.invite_link || "";
            }

            renderGroups(groupsCache);
            renderMembers(res.members || []);
            renderInvites(res.invites || []);
            renderPosts(res.posts || []);
            renderAttachedQuizzes(res.attached_quizzes || []);
        }

        async function createGroup() {
            const title = prompt("Enter group name");
            if (!title || !title.trim()) return;

            const res = await api({
                type_product: "create_group",
                titlu: title.trim(),
                id_users: state.currentUserId
            });

            await loadGroups();

            let newId = "";
            if (res.group && (res.group.randomn_id || res.group.id)) {
                newId = res.group.randomn_id || res.group.id;
            }

            if (newId) {
                await loadGroup(newId, true);
            }
        }

        async function deleteCurrentGroup() {
            const currentGroup = getCurrentGroup();
            if (!currentGroup || !currentGroup.id) {
                alert("No active group selected");
                return;
            }

            if (!confirm("Delete this group?")) return;

            await api({
                type_product: "delete_group",
                group_id: currentGroup.id,
                id_users: state.currentUserId
            });

            state.currentGroupId = "";
            sessionStorage.removeItem("active_group_id");
            await loadGroups();
        }

        async function createPost() {
            const groupId = (el.postModal && el.postModal.dataset.groupId) || state.currentGroupId;
            const message = (el.postMessage && el.postMessage.value.trim()) || "";

            if (!groupId) {
                alert("No active group selected");
                return;
            }

            if (!message) return;

            const res = await api({
                type_product: "create_post",
                group_id: groupId,
                titlu: "You",
                mesaj: message,
                id_users: state.currentUserId
            });

            renderPosts(res.posts || []);
            if (el.postMessage) el.postMessage.value = "";
            updatePostCounter();
            closeModal("postModal");
        }

        async function sendInvite() {
            const groupId = (el.inviteModal && el.inviteModal.dataset.groupId) || state.currentGroupId;
            const email = (el.memberInput && el.memberInput.value.trim()) || "";

            if (!groupId) {
                alert("No active group selected");
                return;
            }

            if (!email) return;

            const res = await api({
                type_product: "send_invite",
                group_id: groupId,
                email: email,
                id_users: state.currentUserId
            });

            if (el.memberInput) el.memberInput.value = "";
            renderInvites(res.invites || []);
            alert(res.message || "Invite sent");
        }

        async function removeMember(memberId) {
            const groupId = (el.inviteModal && el.inviteModal.dataset.groupId) || state.currentGroupId;

            if (!groupId || !memberId) {
                alert("Lipsește datele pentru remove member");
                return;
            }

            const res = await api({
                type_product: "remove_member",
                group_id: groupId,
                member_id: memberId,
                id_users: state.currentUserId
            });

            renderMembers(res.members || []);
            await loadGroups();
        }

        async function deleteInvite(inviteId) {
            const groupId = (el.inviteModal && el.inviteModal.dataset.groupId) || state.currentGroupId;

            if (!groupId || !inviteId) {
                alert("Lipsește datele pentru delete invite");
                return;
            }

            const res = await api({
                type_product: "delete_invite",
                group_id: groupId,
                invite_id: inviteId,
                id_users: state.currentUserId
            });

            renderInvites(res.invites || []);
        }

        async function attachQuizToCurrentGroup(quizId) {
            const currentGroup = getCurrentGroup();
            if (!currentGroup || !currentGroup.id) {
                alert("No active group selected");
                return;
            }

            const res = await api({
                type_product: "attach_quiz",
                group_id: currentGroup.id,
                quiz_id: quizId,
                id_users: state.currentUserId
            });

            renderAttachedQuizzes(res.attached_quizzes || []);
            alert(res.message || "Quiz attached");
        }

        async function removeAttachedQuiz(quizId) {
            const currentGroup = getCurrentGroup();
            if (!currentGroup || !currentGroup.id) {
                alert("No active group selected");
                return;
            }

            const res = await api({
                type_product: "remove_attached_quiz",
                group_id: currentGroup.id,
                quiz_id: quizId,
                id_users: state.currentUserId
            });

            renderAttachedQuizzes(res.attached_quizzes || []);
        }

        async function likePost(postId) {
            const currentGroup = getCurrentGroup();
            if (!currentGroup || !currentGroup.id) return;

            const res = await api({
                type_product: "like_post",
                group_id: currentGroup.id,
                post_id: postId,
                id_users: state.currentUserId
            });

            renderPosts(res.posts || []);
        }

        document.addEventListener("click", function (event) {
            const openBtn = event.target.closest("[data-open-modal]");
            if (openBtn) {
                event.preventDefault();
                openModal(openBtn.getAttribute("data-open-modal"));
                return;
            }

            const closeBtn = event.target.closest("[data-close-modal]");

            if (closeBtn) {
                event.preventDefault();
                closeModal(closeBtn.getAttribute("data-close-modal"));
                return;
            }

            const groupBtn = event.target.closest("[data-group-id]");
            if (groupBtn) {
                event.preventDefault();
                const groupId = groupBtn.getAttribute("data-group-id");
                loadGroup(groupId, true).catch(err => alert(err.message));
                return;
            }

            const removeMemberBtn = event.target.closest("[data-remove-member]");
            if (removeMemberBtn) {
                event.preventDefault();
                const memberId = removeMemberBtn.getAttribute("data-remove-member");
                removeMember(memberId).catch(err => alert(err.message));
                return;
            }

            const deleteInviteBtn = event.target.closest("[data-delete-invite]");
            if (deleteInviteBtn) {
                event.preventDefault();
                const inviteId = deleteInviteBtn.getAttribute("data-delete-invite");
                deleteInvite(inviteId).catch(err => alert(err.message));
                return;
            }

            const attachBtn = event.target.closest("[data-attach-quiz]");
            if (attachBtn) {
                event.preventDefault();
                event.stopPropagation();
                const quizId = Number(attachBtn.getAttribute("data-attach-quiz"));
                attachQuizToCurrentGroup(quizId).catch(err => alert(err.message));
                return;
            }

            const removeAttachedBtn = event.target.closest("[data-remove-attached-quiz]");
            if (removeAttachedBtn) {
                event.preventDefault();
                const quizId = Number(removeAttachedBtn.getAttribute("data-remove-attached-quiz"));
                removeAttachedQuiz(quizId).catch(err => alert(err.message));
                return;
            }

            const likeBtn = event.target.closest("[data-like-post]");
            if (likeBtn) {
                event.preventDefault();
                likePost(likeBtn.getAttribute("data-like-post")).catch(err => alert(err.message));
                return;
            }

            const quizOpen = event.target.closest("[data-quiz-open]");
            if (quizOpen && !event.target.closest("[data-attach-quiz]")) {
                event.preventDefault();
                const quizId = quizOpen.getAttribute("data-quiz-open");
                if (quizId) {
                    window.location.href = "desquizz?id=" + quizId;
                }
            }
        });

        [el.postModal, el.inviteModal, el.selectKahootModal].forEach(modal => {
            if (!modal) return;

            modal.addEventListener("click", function (event) {
                if (event.target === modal) {
                    modal.classList.remove("is_open");
                }
            });

            const inner = modal.querySelector(".kgroups_modal");
            if (inner) {
                inner.addEventListener("click", function (event) {
                    event.stopPropagation();
                });
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                closeAllModals();
            }
        });

        if (el.createGroupBtn) {
            el.createGroupBtn.addEventListener("click", function (event) {
                event.preventDefault();
                createGroup().catch(err => alert(err.message));
            });
        }

        if (el.deleteGroupBtn) {
           
            el.deleteGroupBtn.addEventListener("click", function (event) {
                event.preventDefault();
                deleteCurrentGroup().catch(err => alert(err.message));
            });
        }

        if (el.addMemberBtn) {
            el.addMemberBtn.addEventListener("click", function (event) {
                event.preventDefault();
                sendInvite().catch(err => alert(err.message));
            });
        }

        if (el.memberInput) {
            el.memberInput.addEventListener("keydown", function (event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    sendInvite().catch(err => alert(err.message));
                }
            });
        }

        if (el.postMessage) {
            el.postMessage.addEventListener("input", updatePostCounter);
        }

        if (el.postSubmitBtn) {
            el.postSubmitBtn.addEventListener("click", function (event) {
                event.preventDefault();
                createPost().catch(err => alert(err.message));
            });
        }

        if (el.copyInviteLinkBtn) {
            el.copyInviteLinkBtn.addEventListener("click", async function (event) {
                event.preventDefault();
                try {
                    await navigator.clipboard.writeText((el.inviteLinkInput && el.inviteLinkInput.value) || "");
                    alert("Copied");
                } catch (e) {
                    alert("Copy failed");
                }
            });
        }

        if (el.resetInviteLink) {
            el.resetInviteLink.addEventListener("click", function (event) {
                event.preventDefault();
                const currentGroup = getCurrentGroup();
                if (!currentGroup || !currentGroup.id || !el.inviteLinkInput) return;
                el.inviteLinkInput.value = window.location.origin + "/join_group.php?group=" + encodeURIComponent(currentGroup.id);
            });
        }

        updatePostCounter();

        loadGroups().catch(err => {
            console.error(err);
            alert(err.message);
        });
    })();
</script>