<style>
    :root{
        --bg:#f4f7fb;
        --card:#ffffff;
        --text:#0f172a;
        --muted:#64748b;
        --line:#e2e8f0;
        --primary:#0A5084;
        --primary2:#2E85C7;
        --success:#16a34a;
        --danger:#dc2626;
        --warning:#f59e0b;
        --shadow:0 14px 34px rgba(15,23,42,.08);
        --radius-xl:28px;
        --radius-lg:20px;
        --radius-md:14px;
    }

    *{box-sizing:border-box}

    .page{
        max-width:1500px;
        margin:0 auto;
      
    }

    .topbar{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:20px;
        flex-wrap:wrap;
        margin-bottom:26px;
    }

    .title-wrap h1{
        margin:0;
        font-size:34px;
        line-height:1.1;
        color:var(--primary);
        font-weight:900;
        letter-spacing:-.7px;
    }

    .title-wrap p{
        margin:8px 0 0;
        font-size:15px;
        color:var(--muted);
    }

    .top-actions{
        display:flex;
        gap:12px;
        flex-wrap:wrap;
    }

    .btn{
        border:none;
        outline:none;
        cursor:pointer;
        border-radius:14px;
        padding:12px 18px;
        font-size:14px;
        font-weight:800;
        transition:.25s ease;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        text-decoration:none;
    }

    .btn:hover{ transform:translateY(-2px); }

    .btn-primary{
        color:#fff;
        background:linear-gradient(135deg,var(--primary),var(--primary2));
        box-shadow:0 12px 24px rgba(46,133,199,.22);
    }

    .btn-light{
        color:var(--primary);
        background:#fff;
        border:1px solid var(--line);
    }

    .btn-danger{
        color:#fff;
        background:linear-gradient(135deg,#ef4444,#dc2626);
    }

    .btn-warning{
        color:#fff;
        background:linear-gradient(135deg,#f59e0b,#d97706);
    }

    .btn-secondary{
        color:#334155;
        background:#eef2f7;
        border:1px solid var(--line);
    }

    .btn-sm{
        padding:8px 12px;
        font-size:12px;
        border-radius:10px;
    }

    .msg-box{
        display:none;
        margin-bottom:18px;
        padding:14px 16px;
        border-radius:14px;
        font-size:14px;
        font-weight:700;
    }

    .msg-success{
        display:block;
        background:rgba(22,163,74,.10);
        color:var(--success);
        border:1px solid rgba(22,163,74,.18);
    }

    .msg-error{
        display:block;
        background:rgba(220,38,38,.08);
        color:var(--danger);
        border:1px solid rgba(220,38,38,.14);
    }

    .stats-grid{
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:20px;
        margin-bottom:26px;
    }

    .stat-card{
        background:var(--card);
        border-radius:var(--radius-xl);
        border:1px solid #edf2f7;
        box-shadow:var(--shadow);
        padding:22px;
        min-height:135px;
    }

    .stat-label{
        font-size:14px;
        color:var(--muted);
        margin-bottom:12px;
    }

    .stat-value{
        font-size:32px;
        font-weight:900;
        line-height:1;
        margin-bottom:10px;
    }

    .stat-meta{
        font-size:13px;
        color:var(--muted);
    }

    .layout{
        display:grid;
        grid-template-columns: 1.08fr .92fr;
        gap:24px;
        margin-bottom:24px;
    }

    .panel{
        background:var(--card);
        border-radius:var(--radius-xl);
        border:1px solid #edf2f7;
        box-shadow:var(--shadow);
        padding:24px;
    }

    .panel-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:16px;
        margin-bottom:20px;
        flex-wrap:wrap;
    }

    .panel-head h2{
        margin:0;
        font-size:24px;
        font-weight:900;
        color:var(--primary);
    }

    .panel-head p{
        margin:6px 0 0;
        font-size:14px;
        color:var(--muted);
    }

    .tabs{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin-bottom:20px;
    }

    .tab-btn{
        border:none;
        background:#f1f5f9;
        color:#334155;
        padding:11px 16px;
        border-radius:12px;
        cursor:pointer;
        font-size:13px;
        font-weight:800;
        transition:.2s ease;
    }

    .tab-btn.active{
        background:linear-gradient(135deg,var(--primary),var(--primary2));
        color:#fff;
        box-shadow:0 10px 20px rgba(46,133,199,.20);
    }

    .tab-pane{ display:none; }
    .tab-pane.active{ display:block; }

    .form-grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:16px;
    }

    .field{
        display:flex;
        flex-direction:column;
        gap:8px;
    }

    .field.full{ grid-column:1 / -1; }

    .field label{
        font-size:13px;
        font-weight:800;
        color:#334155;
    }

    .field input,
    .field textarea,
    .field select{
        width:100%;
        border:1px solid var(--line);
        border-radius:14px;
        padding:13px 14px;
        font-size:14px;
        outline:none;
        background:#fff;
        transition:.2s ease;
        resize:vertical;
    }

    .field textarea{
        min-height:110px;
    }

    .field input:focus,
    .field textarea:focus,
    .field select:focus{
        border-color:var(--primary2);
        box-shadow:0 0 0 4px rgba(46,133,199,.10);
    }

    .save-row{
        display:flex;
        justify-content:flex-end;
        gap:12px;
        flex-wrap:wrap;
        margin-top:22px;
    }

    .preview-wrap{
        display:grid;
        gap:20px;
    }

    .preview-card{
        background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);
        border:1px solid #e7eff8;
        border-radius:26px;
        box-shadow:var(--shadow);
        overflow:hidden;
    }

    .preview-top{
        padding:16px 18px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        border-bottom:1px solid #e7eff8;
        background:#fff;
    }

    .preview-brand{
        display:flex;
        align-items:center;
        gap:10px;
        font-weight:900;
        color:var(--primary);
    }

    .preview-logo{
        width:38px;
        height:38px;
        border-radius:12px;
        background:linear-gradient(135deg,var(--primary),var(--primary2));
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight:900;
    }

    .preview-hero{
        padding:26px;
        background:
                radial-gradient(circle at top left, rgba(46,133,199,.10), transparent 26%),
                linear-gradient(180deg, #f9fbfe 0%, #f3f7fb 100%);
    }

    .preview-badge{
        display:inline-flex;
        padding:8px 12px;
        background:#fff;
        border:1px solid var(--line);
        border-radius:999px;
        font-size:12px;
        font-weight:800;
        color:var(--primary);
        margin-bottom:14px;
    }

    .preview-hero h3{
        margin:0 0 12px;
        font-size:32px;
        line-height:1.1;
        color:var(--primary);
    }

    .preview-hero p{
        margin:0 0 18px;
        color:var(--muted);
        line-height:1.7;
        font-size:15px;
    }

    .preview-actions{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        margin-bottom:18px;
    }

    .preview-pill{
        display:inline-flex;
        padding:10px 14px;
        border-radius:12px;
        font-size:13px;
        font-weight:800;
    }

    .preview-pill.primary{
        background:linear-gradient(135deg,var(--primary),var(--primary2));
        color:#fff;
    }

    .preview-pill.light{
        background:#fff;
        border:1px solid var(--line);
        color:var(--primary);
    }

    .preview-features{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:10px;
    }

    .preview-feature{
        background:#fff;
        border:1px solid var(--line);
        border-radius:14px;
        padding:12px 14px;
        font-size:13px;
        font-weight:700;
        color:#334155;
    }

    .manager-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:24px;
        margin-top:24px;
    }

    .section-manager-box{
        border:1px solid var(--line);
        border-radius:18px;
        background:#f8fbff;
        padding:18px;
    }

    .section-manager-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:18px;
    }

    .section-manager-title{
        font-size:20px;
        font-weight:900;
        color:var(--primary);
    }

    .section-meta{
        font-size:13px;
        color:var(--muted);
        display:grid;
        gap:4px;
        margin-top:8px;
    }

    .section-actions,
    .block-actions{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
    }

    .blocks-stack{
        display:grid;
        gap:12px;
        margin-top:18px;
    }

    .block-item{
        border:1px solid var(--line);
        background:#fff;
        border-radius:14px;
        padding:14px;
    }

    .block-title{
        font-weight:900;
        color:#0f172a;
        margin-bottom:6px;
    }

    .block-meta{
        font-size:13px;
        color:var(--muted);
        display:grid;
        gap:4px;
        margin-bottom:12px;
    }

    .empty-box{
        border:1px dashed var(--line);
        background:#fff;
        border-radius:14px;
        padding:16px;
        text-align:center;
        color:var(--muted);
    }

    .editor-modal-wrap{
        position:fixed;
        inset:0;
        background:rgba(15,23,42,.55);
        display:flex;
        align-items:center;
        justify-content:center;
        padding:20px;
        z-index:9999;
    }

    .editor-modal-wrap.hidden{
        display:none;
    }

    .editor-modal-box{
        width:min(980px,100%);
        max-height:90vh;
        overflow:auto;
        background:#fff;
        border-radius:24px;
        padding:24px;
        box-shadow:0 20px 60px rgba(0,0,0,.25);
    }

    .editor-modal-head{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        margin-bottom:18px;
    }

    .json-note{
        font-size:12px;
        color:var(--muted);
        margin-top:4px;
    }

    @media (max-width: 1150px){
        .stats-grid{
            grid-template-columns:repeat(2,minmax(0,1fr));
        }
        .layout{
            grid-template-columns:1fr;
        }
        .manager-grid{
            grid-template-columns:1fr;
        }
    }

    @media (max-width: 760px){
        .page{
            padding:18px;
        }
        .stats-grid{
            grid-template-columns:1fr;
        }
        .form-grid{
            grid-template-columns:1fr;
        }
        .preview-features{
            grid-template-columns:1fr;
        }
    }
</style>

<div class="page" style="width: 100%;max-width: 100%;">
    <?php include_once $_SERVER['DOCUMENT_ROOT'].'/Templates/admin/static_elements/navbox.php'?>

    <div class="topbar">
        <div class="title-wrap">
            <h1>Website Editor</h1>
            <p>Redactează conținutul website-ului, secțiunile homepage, blocurile, JSON-ul, footerul și datele SEO.</p>
        </div>

        <div class="top-actions">
            <button class="btn btn-light" id="previewPulseBtn">Refresh Preview</button>
            <button class="btn btn-primary" id="saveAllBtn">Save All Changes</button>
        </div>
    </div>

    <div id="globalMessage" class="msg-box"></div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Pages Managed</div>
            <div class="stat-value">1</div>
            <div class="stat-meta">Homepage și conținut public</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Active Sections</div>
            <div class="stat-value" id="statSections">0</div>
            <div class="stat-meta">Secțiuni dinamice</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Blocks</div>
            <div class="stat-value" id="statBlocks">0</div>
            <div class="stat-meta">Blocuri totale</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Website Status</div>
            <div class="stat-value">Live</div>
            <div class="stat-meta">Website public publicat</div>
        </div>
    </div>

    <div class="layout">
        <div class="panel" id="editorPanel">
            <div class="panel-head">
                <div>
                    <h2>Content Manager</h2>
                    <p>Toate secțiunile și blocurile sunt gestionate din acest panou.</p>
                </div>
            </div>

            <div class="tabs" id="editorTabs">

            </div>

            <div class="tab-pane active" id="tab-general">
                <form class="form-grid editor-form" data-group="general">
                    <div class="field">
                        <label>Website Name</label>
                        <input type="text" id="siteName" name="site_name">
                    </div>

                    <div class="field">
                        <label>Website Tagline</label>
                        <input type="text" id="siteTagline" name="site_tagline">
                    </div>

                    <div class="field full">
                        <label>Short Website Description</label>
                        <textarea id="siteShortDesc" name="site_short_desc"></textarea>
                    </div>

                    <div class="field full">
                        <label>Logo URL</label>
                        <input type="text" name="logo_url">
                    </div>

                    <div class="save-row">
                        <button type="submit" class="btn btn-primary">Save General</button>
                    </div>
                </form>
            </div>

            <div class="tab-pane" id="tab-hero">
                <form class="form-grid editor-form" data-group="hero">
                    <div class="field full">
                        <label>Hero Badge Text</label>
                        <input type="text" id="heroBadge" name="hero_badge">
                    </div>

                    <div class="field full">
                        <label>Hero Title</label>
                        <textarea id="heroTitle" name="hero_title"></textarea>
                    </div>

                    <div class="field full">
                        <label>Hero Description</label>
                        <textarea id="heroDesc" name="hero_desc"></textarea>
                    </div>

                    <div class="field">
                        <label>Primary Button Text</label>
                        <input type="text" id="heroBtn1" name="hero_btn1">
                    </div>

                    <div class="field">
                        <label>Secondary Button Text</label>
                        <input type="text" id="heroBtn2" name="hero_btn2">
                    </div>

                    <div class="field">
                        <label>Button 1 Link</label>
                        <input type="text" name="hero_btn1_link">
                    </div>

                    <div class="field">
                        <label>Button 2 Link</label>
                        <input type="text" name="hero_btn2_link">
                    </div>

                    <div class="save-row">
                        <button type="submit" class="btn btn-primary">Save Hero</button>
                    </div>
                </form>
            </div>

            <div class="tab-pane" id="tab-footer">
                <form class="form-grid editor-form" data-group="footer">
                    <div class="field">
                        <label>Support Email</label>
                        <input type="email" name="support_email">
                    </div>

                    <div class="field">
                        <label>Phone</label>
                        <input type="text" name="phone">
                    </div>

                    <div class="field full">
                        <label>Address</label>
                        <input type="text" name="address">
                    </div>

                    <div class="field full">
                        <label>Footer Description</label>
                        <textarea name="footer_desc"></textarea>
                    </div>

                    <div class="save-row">
                        <button type="submit" class="btn btn-primary">Save Footer</button>
                    </div>
                </form>
            </div>

            <div class="tab-pane" id="tab-seo">
                <form class="form-grid editor-form" data-group="seo">
                    <div class="field full">
                        <label>Meta Title</label>
                        <input type="text" name="meta_title">
                    </div>

                    <div class="field full">
                        <label>Meta Description</label>
                        <textarea name="meta_description"></textarea>
                    </div>

                    <div class="field full">
                        <label>Meta Keywords</label>
                        <textarea name="meta_keywords"></textarea>
                    </div>

                    <div class="save-row">
                        <button type="submit" class="btn btn-primary">Save SEO</button>
                    </div>
                </form>
            </div>

            <div id="dynamicSectionTabsContainer"></div>
        </div>

        <div class="preview-wrap">
            <div class="panel" id="previewPanel">
                <div class="panel-head">
                    <div>
                        <h2>Live Preview</h2>
                        <p>Previzualizare rapidă a homepage-ului.</p>
                    </div>
                </div>

                <div class="preview-card">
                    <div class="preview-top">
                        <div class="preview-brand">
                            <div class="preview-logo">Q</div>
                            <span id="previewSiteName">QuizDigo</span>
                        </div>
                        <span class="badge badge-success">Live</span>
                    </div>

                    <div class="preview-hero">
                        <div class="preview-badge" id="previewBadge">Badge</div>
                        <h3 id="previewHeroTitle">Titlu Hero</h3>
                        <p id="previewHeroDesc">Descriere Hero</p>

                        <div class="preview-actions">
                            <div class="preview-pill primary" id="previewBtn1">Button 1</div>
                            <div class="preview-pill light" id="previewBtn2">Button 2</div>
                        </div>

                        <div class="preview-features">
                            <div class="preview-feature">Builder vizual clar</div>
                            <div class="preview-feature">Quizuri live cu acces rapid</div>
                            <div class="preview-feature">Organizare pe mape</div>
                            <div class="preview-feature">Rezultate și analiză</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Adaugă Secțiune</h2>
                        <p>Creează o secțiune nouă.</p>
                    </div>
                </div>

                <form id="addSectionForm" class="form-grid">
                    <div class="field">
                        <label>Section Key</label>
                        <input type="text" name="section_key" placeholder="ex: quiz_blocks">
                    </div>

                    <div class="field">
                        <label>Section Name</label>
                        <input type="text" name="section_name" placeholder="ex: Quiz Blocks">
                    </div>

                    <div class="field">
                        <label>Section Type</label>
                        <select name="section_type">
                            <option value="default">default</option>
                            <option value="hero">hero</option>
                            <option value="features">features</option>
                            <option value="quiz_cards">quiz_cards</option>
                            <option value="pricing">pricing</option>
                            <option value="faq">faq</option>
                            <option value="contact">contact</option>
                            <option value="cta">cta</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="0">
                    </div>

                    <div class="field full">
                        <label>Section Title</label>
                        <input type="text" name="title" placeholder="Titlu secțiune">
                    </div>

                    <div class="field full">
                        <label>Section Subtitle</label>
                        <textarea name="subtitle" placeholder="Subtitlu secțiune"></textarea>
                    </div>

                    <div class="field full">
                        <label>Settings JSON</label>
                        <textarea name="settings_json" placeholder='{"image":"assets/img/imghome.png"}'></textarea>
                        <div class="json-note">JSON opțional pentru configurări speciale.</div>
                    </div>

                    <div class="save-row">
                        <button type="submit" class="btn btn-primary">Add Section</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="panel" style="margin-top:24px;">
        <div class="panel-head">
            <div>
                <h2>Adaugă Bloc Nou</h2>
                <p>Adaugă rapid un bloc într-o secțiune existentă.</p>
            </div>
        </div>

        <form id="addBlockForm" class="form-grid">
            <div class="field">
                <label>Section</label>
                <select name="section_id" id="blockSectionSelect"></select>
            </div>

            <div class="field">
                <label>Block Key</label>
                <input type="text" name="block_key" placeholder="ex: quiz_1">
            </div>

            <div class="field">
                <label>Block Type</label>
                <select name="block_type">
                    <option value="text">text</option>
                    <option value="feature_card">feature_card</option>
                    <option value="quiz_card">quiz_card</option>
                    <option value="faq_item">faq_item</option>
                    <option value="price_card">price_card</option>
                    <option value="button">button</option>
                    <option value="nav_item">nav_item</option>
                    <option value="news_card">news_card</option>
                    <option value="info_card">info_card</option>
                    <option value="social_icon">social_icon</option>
                </select>
            </div>

            <div class="field">
                <label>Sort Order</label>
                <input type="number" name="sort_order" value="0">
            </div>

            <div class="field full">
                <label>Title</label>
                <input type="text" name="title">
            </div>

            <div class="field full">
                <label>Subtitle</label>
                <input type="text" name="subtitle">
            </div>

            <div class="field full">
                <label>Content</label>
                <textarea name="content"></textarea>
            </div>

            <div class="field">
                <label>Image URL</label>
                <input type="text" name="image_url">
            </div>

            <div class="field">
                <label>Button Text</label>
                <input type="text" name="button_text">
            </div>

            <div class="field">
                <label>Button URL</label>
                <input type="text" name="button_url">
            </div>

            <div class="field">
                <label>Badge</label>
                <input type="text" name="badge" placeholder="ex: #FF5722 / fa-solid fa-star / 1">
            </div>

            <div class="field full">
                <label>Data JSON</label>
                <textarea name="data_json" placeholder='{"visible_group":1,"target":180}'></textarea>
            </div>

            <div class="field full">
                <label>Extra JSON</label>
                <textarea name="extra_json" placeholder='{"modal_title":"Titlu","modal_text":"Descriere"}'></textarea>
            </div>

            <div class="save-row">
                <button type="submit" class="btn btn-primary">Add Block</button>
            </div>
        </form>
    </div>
</div>

<!-- SECTION MODAL -->
<div id="sectionModal" class="editor-modal-wrap hidden">
    <div class="editor-modal-box">
        <div class="editor-modal-head">
            <h3>Edit Section</h3>
            <button type="button" class="btn btn-light" onclick="closeSectionModal()">Închide</button>
        </div>

        <form id="sectionEditForm" class="form-grid">
            <input type="hidden" name="section_id" id="section_edit_id">

            <div class="field">
                <label>Section Key</label>
                <input type="text" name="section_key" id="section_edit_key">
            </div>

            <div class="field">
                <label>Section Name</label>
                <input type="text" name="section_name" id="section_edit_name">
            </div>

            <div class="field">
                <label>Section Type</label>
                <input type="text" name="section_type" id="section_edit_type">
            </div>

            <div class="field">
                <label>Sort Order</label>
                <input type="number" name="sort_order" id="section_edit_sort_order">
            </div>

            <div class="field full">
                <label>Title</label>
                <input type="text" name="title" id="section_edit_title">
            </div>

            <div class="field full">
                <label>Subtitle</label>
                <textarea name="subtitle" id="section_edit_subtitle"></textarea>
            </div>

            <div class="field full">
                <label>Settings JSON</label>
                <textarea name="settings_json" id="section_edit_settings_json"></textarea>
            </div>

            <div class="field">
                <label>Active</label>
                <select name="is_active" id="section_edit_active">
                    <option value="1">Da</option>
                    <option value="0">Nu</option>
                </select>
            </div>

            <div class="save-row full">
                <button type="submit" class="btn btn-primary">Save Section</button>
            </div>
        </form>
    </div>
</div>

<!-- BLOCK MODAL -->
<div id="blockModal" class="editor-modal-wrap hidden">
    <div class="editor-modal-box">
        <div class="editor-modal-head">
            <h3>Edit Block</h3>
            <button type="button" class="btn btn-light" onclick="closeBlockModal()">Închide</button>
        </div>

        <form id="blockEditForm" class="form-grid">
            <input type="hidden" name="block_id" id="block_edit_id">

            <div class="field">
                <label>Section ID</label>
                <input type="number" name="section_id" id="block_edit_section_id">
            </div>

            <div class="field">
                <label>Block Key</label>
                <input type="text" name="block_key" id="block_edit_key">
            </div>

            <div class="field">
                <label>Block Type</label>
                <input type="text" name="block_type" id="block_edit_type">
            </div>

            <div class="field">
                <label>Sort Order</label>
                <input type="number" name="sort_order" id="block_edit_sort_order">
            </div>

            <div class="field full">
                <label>Title</label>
                <input type="text" name="title" id="block_edit_title">
            </div>

            <div class="field full">
                <label>Subtitle</label>
                <input type="text" name="subtitle" id="block_edit_subtitle">
            </div>

            <div class="field full">
                <label>Content</label>
                <textarea name="content" id="block_edit_content"></textarea>
            </div>

            <div class="field">
                <label>Image URL</label>
                <input type="text" name="image_url" id="block_edit_image_url">
            </div>

            <div class="field">
                <label>Button Text</label>
                <input type="text" name="button_text" id="block_edit_button_text">
            </div>

            <div class="field">
                <label>Button URL</label>
                <input type="text" name="button_url" id="block_edit_button_url">
            </div>

            <div class="field">
                <label>Badge</label>
                <input type="text" name="badge" id="block_edit_badge">
            </div>

            <div class="field">
                <label>Active</label>
                <select name="is_active" id="block_edit_active">
                    <option value="1">Da</option>
                    <option value="0">Nu</option>
                </select>
            </div>

            <div class="field full">
                <label>Data JSON</label>
                <textarea name="data_json" id="block_edit_data_json"></textarea>
            </div>

            <div class="field full">
                <label>Extra JSON</label>
                <textarea name="extra_json" id="block_edit_extra_json"></textarea>
            </div>

            <div class="save-row full">
                <button type="submit" class="btn btn-primary">Save Block</button>
            </div>
        </form>
    </div>
</div>

<script>
    const API_URL = 'https://quizdigo.com/website-editor-api.php';

    const globalMessage = document.getElementById('globalMessage');
    let allSectionsState = [];

    function showMessage(type, text) {
        globalMessage.className = 'msg-box';
        globalMessage.classList.add(type === 'success' ? 'msg-success' : 'msg-error');
        globalMessage.textContent = text;
        globalMessage.style.display = 'block';

        setTimeout(() => {
            globalMessage.style.display = 'none';
        }, 3000);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function prettyJson(value) {
        if (!value) return '';
        if (typeof value === 'string') {
            try {
                return JSON.stringify(JSON.parse(value), null, 2);
            } catch {
                return value;
            }
        }
        try {
            return JSON.stringify(value, null, 2);
        } catch {
            return '';
        }
    }

    function bindAllTabs() {
        const tabButtons = document.querySelectorAll('.tab-btn');
        const panes = document.querySelectorAll('.tab-pane');

        tabButtons.forEach(btn => {
            btn.onclick = function () {
                const tab = this.dataset.tab;
                tabButtons.forEach(b => b.classList.remove('active'));
                panes.forEach(p => p.classList.remove('active'));

                this.classList.add('active');

                const pane = document.getElementById('tab-' + tab);
                if (pane) pane.classList.add('active');
            };
        });
    }

    function updatePreview() {
        const siteName = document.getElementById('siteName');
        const heroBadge = document.getElementById('heroBadge');
        const heroTitle = document.getElementById('heroTitle');
        const heroDesc = document.getElementById('heroDesc');
        const heroBtn1 = document.getElementById('heroBtn1');
        const heroBtn2 = document.getElementById('heroBtn2');

        document.getElementById('previewSiteName').textContent = siteName ? siteName.value || 'QuizDigo' : 'QuizDigo';
        document.getElementById('previewBadge').textContent = heroBadge ? heroBadge.value || '' : '';
        document.getElementById('previewHeroTitle').textContent = heroTitle ? heroTitle.value || '' : '';
        document.getElementById('previewHeroDesc').textContent = heroDesc ? heroDesc.value || '' : '';
        document.getElementById('previewBtn1').textContent = heroBtn1 ? heroBtn1.value || '' : '';
        document.getElementById('previewBtn2').textContent = heroBtn2 ? heroBtn2.value || '' : '';
    }

    async function postAjax(formData) {
        formData.append('ajax', '1');

        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });

        return response.json();
    }

    function fdWithAction(fd, action) {
        fd.append('action', action);
        return fd;
    }

    async function sendSimpleAction(action, payload = {}) {
        const fd = new FormData();
        fd.append('action', action);

        Object.keys(payload).forEach(key => {
            fd.append(key, payload[key]);
        });

        return postAjax(fd);
    }

    async function loadAll() {
        try {
            const fd = new FormData();
            fd.append('action', 'load_all');
            const result = await postAjax(fd);

            if (!result.ok) {
                showMessage('error', result.message || 'Eroare la încărcare');
                return;
            }

            allSectionsState = result.sections || [];

            fillSettings(result.settings || {});
            fillSectionSelects(allSectionsState);
            renderDynamicSectionTabs(allSectionsState);
            updateCounters(allSectionsState);
            updatePreview();
        } catch (e) {
            showMessage('error', 'Eroare AJAX la încărcare');
        }
    }

    function fillSettings(settings) {
        const map = {};
        Object.values(settings).forEach(group => {
            Object.assign(map, group);
        });

        Object.keys(map).forEach(key => {
            const input = document.querySelector(`[name="${key}"]`) || document.getElementById(key);
            if (!input) return;

            if (input.type === 'checkbox') {
                input.checked = map[key] === '1';
            } else {
                input.value = map[key] ?? '';
            }
        });
    }

    function fillSectionSelects(sections) {
        const selects = [
            document.getElementById('blockSectionSelect')
        ];

        selects.forEach(select => {
            if (!select) return;

            const currentValue = select.value;
            select.innerHTML = '<option value="">Selectează secțiunea</option>';

            sections.forEach(section => {
                const option = document.createElement('option');
                option.value = section.id;
                option.textContent = `${section.section_name} (${section.section_type})`;
                select.appendChild(option);
            });

            if ([...select.options].some(opt => opt.value === currentValue)) {
                select.value = currentValue;
            }
        });
    }

    function updateCounters(sections) {
        const statSections = document.getElementById('statSections');
        const statBlocks = document.getElementById('statBlocks');

        let totalBlocks = 0;
        sections.forEach(section => {
            totalBlocks += (section.blocks || []).length;
        });

        statSections.textContent = sections.length;
        statBlocks.textContent = totalBlocks;
    }

    function renderDynamicSectionTabs(sections) {
        const tabsWrap = document.getElementById('editorTabs');
        const dynamicContainer = document.getElementById('dynamicSectionTabsContainer');

        if (!tabsWrap || !dynamicContainer) return;

        tabsWrap.querySelectorAll('.dynamic-section-tab').forEach(el => el.remove());
        dynamicContainer.innerHTML = '';

        sections.forEach(section => {
            const tabBtn = document.createElement('button');
            tabBtn.className = 'tab-btn dynamic-section-tab';
            tabBtn.dataset.tab = 'section_' + section.id;
            tabBtn.textContent = section.section_name || section.section_key;
            tabsWrap.appendChild(tabBtn);

            const pane = document.createElement('div');
            pane.className = 'tab-pane';
            pane.id = 'tab-section_' + section.id;

            let blocksHtml = '<div class="empty-box">Nu există blocuri în această secțiune.</div>';

            if (section.blocks && section.blocks.length) {
                blocksHtml = `
                    <div class="blocks-stack">
                        ${section.blocks.map(block => `
                            <div class="block-item">
                                <div class="block-title">${escapeHtml(block.title || block.block_key)}</div>
                                <div class="block-meta">
                                    <div><strong>Key:</strong> ${escapeHtml(block.block_key || '')}</div>
                                    <div><strong>Type:</strong> ${escapeHtml(block.block_type || '')}</div>
                                    <div><strong>Sort:</strong> ${escapeHtml(String(block.sort_order ?? 0))}</div>
                                    <div><strong>Badge:</strong> ${escapeHtml(block.badge || '')}</div>
                                </div>
                                <div class="block-actions">
                                    <button class="btn btn-secondary btn-sm" type="button" onclick="openBlockModalById(${block.id})">Edit</button>
                                    <button class="btn btn-light btn-sm" type="button" onclick="moveBlock(${block.id}, 'up')">↑</button>
                                    <button class="btn btn-light btn-sm" type="button" onclick="moveBlock(${block.id}, 'down')">↓</button>
                                    <button class="btn btn-danger btn-sm" type="button" onclick="deleteBlock(${block.id})">Delete</button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            pane.innerHTML = `
                <div class="section-manager-box">
                    <div class="section-manager-head">
                        <div>
                            <div class="section-manager-title">${escapeHtml(section.section_name || '')}</div>
                            <div class="section-meta">
                                <div><strong>Key:</strong> ${escapeHtml(section.section_key || '')}</div>
                                <div><strong>Type:</strong> ${escapeHtml(section.section_type || '')}</div>
                                <div><strong>Sort:</strong> ${escapeHtml(String(section.sort_order ?? 0))}</div>
                                <div><strong>Title:</strong> ${escapeHtml(section.title || '')}</div>
                            </div>
                        </div>

                        <div class="section-actions">
                            <button class="btn btn-secondary btn-sm" type="button" onclick="openSectionModalById(${section.id})">Edit Section</button>
                            <button class="btn btn-light btn-sm" type="button" onclick="moveSection(${section.id}, 'up')">↑</button>
                            <button class="btn btn-light btn-sm" type="button" onclick="moveSection(${section.id}, 'down')">↓</button>
                            <button class="btn btn-danger btn-sm" type="button" onclick="deleteSection(${section.id})">Delete Section</button>
                            <button class="btn btn-primary btn-sm" type="button" onclick="prefillAddBlockSection(${section.id})">Add Block</button>
                        </div>
                    </div>

                    ${blocksHtml}
                </div>
            `;

            dynamicContainer.appendChild(pane);
        });

        bindAllTabs();
    }

    function openSectionModal() {
        document.getElementById('sectionModal').classList.remove('hidden');
    }

    function closeSectionModal() {
        document.getElementById('sectionModal').classList.add('hidden');
    }

    function openBlockModal() {
        document.getElementById('blockModal').classList.remove('hidden');
    }

    function closeBlockModal() {
        document.getElementById('blockModal').classList.add('hidden');
    }

    function openSectionModalById(id) {
        const section = allSectionsState.find(s => Number(s.id) === Number(id));
        if (!section) return;

        document.getElementById('section_edit_id').value = section.id || '';
        document.getElementById('section_edit_key').value = section.section_key || '';
        document.getElementById('section_edit_name').value = section.section_name || '';
        document.getElementById('section_edit_type').value = section.section_type || '';
        document.getElementById('section_edit_sort_order').value = section.sort_order || 0;
        document.getElementById('section_edit_title').value = section.title || '';
        document.getElementById('section_edit_subtitle').value = section.subtitle || '';
        document.getElementById('section_edit_active').value = String(section.is_active ?? 1);
        document.getElementById('section_edit_settings_json').value =
            section.settings_json ? prettyJson(section.settings_json) : prettyJson(section.settings || {});

        openSectionModal();
    }

    function openBlockModalById(id) {
        let foundBlock = null;

        allSectionsState.forEach(section => {
            (section.blocks || []).forEach(block => {
                if (Number(block.id) === Number(id)) {
                    foundBlock = block;
                }
            });
        });

        if (!foundBlock) return;

        document.getElementById('block_edit_id').value = foundBlock.id || '';
        document.getElementById('block_edit_section_id').value = foundBlock.section_id || '';
        document.getElementById('block_edit_key').value = foundBlock.block_key || '';
        document.getElementById('block_edit_type').value = foundBlock.block_type || '';
        document.getElementById('block_edit_sort_order').value = foundBlock.sort_order || 0;
        document.getElementById('block_edit_title').value = foundBlock.title || '';
        document.getElementById('block_edit_subtitle').value = foundBlock.subtitle || '';
        document.getElementById('block_edit_content').value = foundBlock.content || '';
        document.getElementById('block_edit_image_url').value = foundBlock.image_url || '';
        document.getElementById('block_edit_button_text').value = foundBlock.button_text || '';
        document.getElementById('block_edit_button_url').value = foundBlock.button_url || '';
        document.getElementById('block_edit_badge').value = foundBlock.badge || '';
        document.getElementById('block_edit_active').value = String(foundBlock.is_active ?? 1);
        document.getElementById('block_edit_data_json').value =
            foundBlock.data_json ? prettyJson(foundBlock.data_json) : prettyJson(foundBlock.data || {});
        document.getElementById('block_edit_extra_json').value =
            foundBlock.extra_json ? prettyJson(foundBlock.extra_json) : prettyJson(foundBlock.extra || {});

        openBlockModal();
    }

    async function deleteSection(id) {
        if (!confirm('Sigur vrei să ștergi această secțiune?')) return;

        try {
            const result = await sendSimpleAction('delete_section', { section_id: id });
            if (result.ok) {
                showMessage('success', result.message || 'Secțiune ștearsă');
                closeSectionModal();
                loadAll();
            } else {
                showMessage('error', result.message || 'Eroare');
            }
        } catch {
            showMessage('error', 'Eroare la ștergerea secțiunii.');
        }
    }

    async function deleteBlock(id) {
        if (!confirm('Sigur vrei să ștergi acest bloc?')) return;

        try {
            const result = await sendSimpleAction('delete_block', { block_id: id });
            if (result.ok) {
                showMessage('success', result.message || 'Bloc șters');
                closeBlockModal();
                loadAll();
            } else {
                showMessage('error', result.message || 'Eroare');
            }
        } catch {
            showMessage('error', 'Eroare la ștergerea blocului.');
        }
    }

    async function moveSection(id, direction) {
        try {
            const result = await sendSimpleAction('move_section', {
                section_id: id,
                direction
            });

            if (result.ok) {
                showMessage('success', result.message || 'Secțiune mutată');
                loadAll();
            } else {
                showMessage('error', result.message || 'Eroare');
            }
        } catch {
            showMessage('error', 'Eroare la mutarea secțiunii.');
        }
    }

    async function moveBlock(id, direction) {
        try {
            const result = await sendSimpleAction('move_block', {
                block_id: id,
                direction
            });

            if (result.ok) {
                showMessage('success', result.message || 'Bloc mutat');
                loadAll();
            } else {
                showMessage('error', result.message || 'Eroare');
            }
        } catch {
            showMessage('error', 'Eroare la mutarea blocului.');
        }
    }

    function prefillAddBlockSection(sectionId) {
        const select = document.getElementById('blockSectionSelect');
        if (select) {
            select.value = String(sectionId);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindAllTabs();

        ['siteName','heroBadge','heroTitle','heroDesc','heroBtn1','heroBtn2'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', updatePreview);
        });

        document.querySelectorAll('.editor-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const group = this.dataset.group;
                const fd = new FormData(this);
                const payload = {};

                for (const [key, value] of fd.entries()) {
                    payload[key] = value;
                }

                this.querySelectorAll('input[type="checkbox"]').forEach(ch => {
                    payload[ch.name] = ch.checked ? '1' : '0';
                });

                const send = new FormData();
                send.append('action', 'save_group');
                send.append('group', group);
                send.append('payload', JSON.stringify(payload));

                try {
                    const result = await postAjax(send);
                    if (result.ok) {
                        showMessage('success', result.message || 'Salvat');
                        updatePreview();
                        loadAll();
                    } else {
                        showMessage('error', result.message || 'Eroare la salvare');
                    }
                } catch {
                    showMessage('error', 'Eroare AJAX.');
                }
            });
        });

        document.getElementById('saveAllBtn').addEventListener('click', async function() {
            const forms = document.querySelectorAll('.editor-form');

            try {
                for (const form of forms) {
                    const group = form.dataset.group;
                    const fd = new FormData(form);
                    const payload = {};

                    for (const [key, value] of fd.entries()) {
                        payload[key] = value;
                    }

                    form.querySelectorAll('input[type="checkbox"]').forEach(ch => {
                        payload[ch.name] = ch.checked ? '1' : '0';
                    });

                    const send = new FormData();
                    send.append('action', 'save_group');
                    send.append('group', group);
                    send.append('payload', JSON.stringify(payload));

                    await postAjax(send);
                }

                showMessage('success', 'Toate modificările au fost salvate.');
                updatePreview();
                loadAll();
            } catch {
                showMessage('error', 'Eroare la salvarea tuturor datelor.');
            }
        });

        document.getElementById('previewPulseBtn').addEventListener('click', updatePreview);

        document.getElementById('addSectionForm').addEventListener('submit', async function(e){
            e.preventDefault();
            const fd = new FormData(this);

            try {
                const result = await postAjax(fdWithAction(fd, 'add_section'));
                if (result.ok) {
                    showMessage('success', result.message || 'Secțiune adăugată');
                    this.reset();
                    loadAll();
                } else {
                    showMessage('error', result.message || 'Eroare');
                }
            } catch {
                showMessage('error', 'Eroare la adăugarea secțiunii.');
            }
        });

        document.getElementById('addBlockForm').addEventListener('submit', async function(e){
            e.preventDefault();
            const fd = new FormData(this);

            try {
                const result = await postAjax(fdWithAction(fd, 'add_block'));
                if (result.ok) {
                    showMessage('success', result.message || 'Bloc adăugat');
                    this.reset();
                    loadAll();
                } else {
                    showMessage('error', result.message || 'Eroare');
                }
            } catch {
                showMessage('error', 'Eroare la adăugarea blocului.');
            }
        });

        document.getElementById('sectionEditForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const fd = new FormData(this);
            fd.append('action', 'update_section');

            try {
                const result = await postAjax(fd);
                if (result.ok) {
                    showMessage('success', result.message || 'Secțiune actualizată');
                    closeSectionModal();
                    loadAll();
                } else {
                    showMessage('error', result.message || 'Eroare la actualizare');
                }
            } catch {
                showMessage('error', 'Eroare la actualizarea secțiunii.');
            }
        });

        document.getElementById('blockEditForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const fd = new FormData(this);
            fd.append('action', 'update_block');

            try {
                const result = await postAjax(fd);
                if (result.ok) {
                    showMessage('success', result.message || 'Bloc actualizat');
                    closeBlockModal();
                    loadAll();
                } else {
                    showMessage('error', result.message || 'Eroare la actualizare');
                }
            } catch {
                showMessage('error', 'Eroare la actualizarea blocului.');
            }
        });

        loadAll();
    });
</script>