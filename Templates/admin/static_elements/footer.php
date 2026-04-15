<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileToggle = document.getElementById('profileToggle');
        const profileDropdown = document.getElementById('profileDropdown');
        const chevIcon = document.getElementById('chevIcon');

        // Toggle dropdown la click
        profileToggle.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevenim închiderea imediată
            profileDropdown.classList.toggle('show');

            // Rotim chevon-ul (opțional)
            if(profileDropdown.classList.contains('show')) {
                chevIcon.style.transform = 'rotate(180deg)';
            } else {
                chevIcon.style.transform = 'rotate(0deg)';
            }
        });

        // Închide dropdown-ul dacă se dă click în afara lui
        window.addEventListener('click', function() {
            if (profileDropdown.classList.contains('show')) {
                profileDropdown.classList.remove('show');
                chevIcon.style.transform = 'rotate(0deg)';
            }
        });
    });
</script>
<script>
    // ============================
    // CONFIG ANALYTICS
    // ============================
    const CUBE_POLL_MS          = 200;   // cât de des citim starea cubului
    const INACTIVITY_THRESHOLD  = 3000;  // > 3 sec = pauză / inactivitate
    const SOLVED_STATE          = null;  // de setat ulterior dacă vrei detectare „rezolvat”

    // ============================
    // STRUCTURA SESIUNII
    // ============================
    let cubeSession      = null;
    let cubeInitialized  = false;
    let cubePollInterval = null;
    let cubeLastState    = null;
    let cubeMoveIndex    = 0;

    // pentru întrebări
    let currentQuestionId   = null;
    let currentQuestionText = '';

    function nowMs() {
        return (window.performance && performance.now) ? performance.now() : Date.now();
    }

    // ============================
    // POPUP ÎNTREBARE
    // ============================
    function showGameQuestion(questionId, questionText) {
        currentQuestionId   = questionId || null;
        currentQuestionText = questionText || '';

        const qTextEl   = document.getElementById('gameQuestionText');
        const answerEl  = document.getElementById('gameQuestionAnswer');
        const modalEl   = document.getElementById('gameQuestionModal');

        if (!qTextEl || !answerEl || !modalEl) {
            console.warn('[GAME_QUESTION] Elementele modalului nu sunt prezente în DOM.');
            return;
        }

        qTextEl.textContent = questionText;
        answerEl.value      = '';

        if (cubeSession) {
            const t = nowMs();
            cubeSession.questions.push({
                id:           questionId,
                text:         questionText,
                askedAtMs:    t,
                askedAt:      new Date().toISOString(),
                answeredAtMs: null,
                answeredAt:   null,
                reactionMs:   null,
                answer:       null
            });
            cubeSession.lastActivityMs = t;
        }

        if (typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        } else {
            alert(questionText);
        }
    }

    // ============================
    // STACKED MODALS
    // ============================
    document.addEventListener('shown.bs.modal', function () {
        document.querySelectorAll('.modal-backdrop').forEach((el, index) => {
            el.style.zIndex = 1050 + index * 20;
        });

        document.querySelectorAll('.modal.show').forEach((el, index) => {
            el.style.zIndex = 1060 + index * 20;
        });
    });

    // ============================
    // UTIL: citire stare cub
    // ============================
    function snapshotCube() {
        const cube = window.acjs_cube ? acjs_cube['cubeTest'] : null;
        if (!cube || !cube.length) return null;

        let s = "";
        for (let f = 0; f < cube.length; f++) {
            const face = cube[f];
            for (let i = 0; i < face.length; i++) {
                let v = face[i] | 0;
                s += v.toString(16);
            }
        }
        return s;
    }

    function isSolvedState(state) {
        if (!SOLVED_STATE) return false;
        return state === SOLVED_STATE;
    }

    function computeDifficulty() {
        if (!cubeSession || !cubeSession.solvedAtMs) return null;

        const totalMs       = cubeSession.solvedAtMs - cubeSession.startedAtMs;
        const totalSeconds  = totalMs / 1000;
        const moveCount     = cubeSession.moves.length;
        const inactivitySum = cubeSession.inactivity.reduce((acc, ev) => acc + ev.durationMs, 0);
        const inactiveRatio = inactivitySum / totalMs;

        let level = 'necunoscut';

        if (totalSeconds <= 60 && moveCount <= 80 && inactiveRatio < 0.2) {
            level = 'foarte rapid / ușor';
        } else if (totalSeconds <= 180 && inactiveRatio < 0.35) {
            level = 'mediu';
        } else {
            level = 'greu / cu multe blocaje';
        }

        return {
            difficultyLabel: level,
            totalSeconds:    totalSeconds,
            moves:           moveCount,
            inactivityMs:    inactivitySum,
            inactivityRatio: inactiveRatio
        };
    }

    // ============================
    // INITIALIZARE CUB
    // ============================
    function initCubeGame() {
        if (cubeInitialized) return;
        cubeInitialized = true;

        const tStart = nowMs();
        cubeSession = {
            sessionId:      'cube-' + Math.random().toString(36).substring(2, 10),
            startedAtMs:    tStart,
            startedAt:      new Date().toISOString(),
            lastActivityMs: tStart,
            moves:          [],
            inactivity:     [],
            questions:      [],
            solvedAtMs:     null,
            solvedAt:       null,
            totalTimeMs:    null,
            difficulty:     null
        };

        console.log('[CUBE] Pornesc sesiunea:', cubeSession.sessionId);

        if (typeof AnimCube3 === 'undefined') {
            console.error('[CUBE] AnimCube3 nu este definit. Verifică includerea scriptului animcube3.js.');
            return;
        }

        AnimCube3(
            "id=cubeTest" +
            "&scale=0" +
            "&edit=1" +
            "&scramble=2" +
            "&randmoves=40" +
            "&buttonbar=0" +
            "&hint=0"
        );

        // 🔹 TRIGGER 1: ÎNTREBARE DUPĂ 15 SECUNDE
        setTimeout(function () {
            if (!cubeSession) return;
            showGameQuestion(
                'q_time_15s',
                'Cum te simți după primele 15 secunde de joc: curios, stresat sau relaxat?'
            );
        }, 15000);

        setTimeout(function () {
            console.log('[CUBE] Încep tracking-ul de mișcări...');
            cubeLastState = snapshotCube();

            cubePollInterval = setInterval(function () {
                const state = snapshotCube();
                if (state === null) return;

                const now    = nowMs();
                const delta  = now - cubeSession.lastActivityMs;

                // inactivitate
                if (delta >= INACTIVITY_THRESHOLD) {
                    cubeSession.inactivity.push({
                        fromMs:     cubeSession.lastActivityMs,
                        toMs:       now,
                        durationMs: delta,
                        from:       new Date().toISOString(),
                        to:         new Date().toISOString()
                    });
                    console.log('[CUBE INACTIVITY]', delta + 'ms pauză');
                }

                // micro-mișcare
                if (state !== cubeLastState) {
                    cubeMoveIndex++;

                    const moveEvent = {
                        index:        cubeMoveIndex,
                        atMs:         now,
                        at:           new Date().toISOString(),
                        sinceLastMs:  delta,
                        state:        state
                    };

                    cubeSession.moves.push(moveEvent);
                    cubeSession.lastActivityMs = now;
                    cubeLastState              = state;

                    console.log(
                        '%c[CUBE MOVE #' + cubeMoveIndex + ']',
                        'color:#00e5ff;font-weight:bold;',
                        moveEvent
                    );

                    // 🔹 TRIGGER 2: ÎNTREBARE LA 10 MIȘCĂRI
                    if (cubeMoveIndex === 10) {
                        showGameQuestion(
                            'q_move_10',
                            'După primele 10 mișcări, ai senzația că ai o strategie sau încă experimentezi la întâmplare?'
                        );
                    }

                    // 🔹 TRIGGER 3: ÎNTREBARE LA 25 MIȘCĂRI
                    if (cubeMoveIndex === 25) {
                        showGameQuestion(
                            'q_move_25',
                            'Ce faci când simți că te blochezi: continui, schimbi metoda sau te oprești?'
                        );
                    }

                    // Detectare „rezolvat”, dacă vei seta SOLVED_STATE
                    if (!cubeSession.solvedAtMs && isSolvedState(state)) {
                        cubeSession.solvedAtMs  = now;
                        cubeSession.solvedAt    = new Date().toISOString();
                        cubeSession.totalTimeMs = now - cubeSession.startedAtMs;
                        cubeSession.difficulty  = computeDifficulty();

                        console.log('%c[CUBE SOLVED]', 'color:#00ff7f;font-weight:bold;', {
                            totalTimeMs: cubeSession.totalTimeMs,
                            difficulty:  cubeSession.difficulty
                        });
                    }
                }
            }, CUBE_POLL_MS);
        }, 500);
    }

    // ============================
    // HOOK-URI DOMContentLoaded
    // ============================
    document.addEventListener('DOMContentLoaded', function () {
        const modalCubeEl = document.getElementById('cube_game_modal');
        const statusEl    = document.getElementById('cubeGameStatus');

        const saveBtn  = document.getElementById('gameQuestionSaveBtn');
        const answerEl = document.getElementById('gameQuestionAnswer');
        const qModalEl = document.getElementById('gameQuestionModal');

        if (saveBtn && answerEl && qModalEl) {
            saveBtn.addEventListener('click', function () {
                const answer    = answerEl.value.trim();
                const timestamp = nowMs();

                let questionRecord = null;

                if (cubeSession && cubeSession.questions.length) {
                    for (let i = cubeSession.questions.length - 1; i >= 0; i--) {
                        const q = cubeSession.questions[i];
                        if (q.id === currentQuestionId && q.answeredAtMs === null) {
                            questionRecord = q;
                            break;
                        }
                    }
                }

                if (questionRecord) {
                    questionRecord.answeredAtMs = timestamp;
                    questionRecord.answeredAt   = new Date().toISOString();
                    questionRecord.reactionMs   = questionRecord.answeredAtMs - questionRecord.askedAtMs;
                    questionRecord.answer       = answer;

                    console.log('[CUBE_QUESTION]', questionRecord);
                } else {
                    console.log('[CUBE_QUESTION_NO_MATCH]', {
                        id:      currentQuestionId,
                        text:    currentQuestionText,
                        answer:  answer,
                        timeISO: new Date().toISOString()
                    });
                }

                if (cubeSession) {
                    cubeSession.lastActivityMs = timestamp;
                }

                const modal = bootstrap.Modal.getInstance(qModalEl);
                if (modal) modal.hide();
            });
        } else {
            console.warn('[GAME_QUESTION] Nu am găsit toate elementele pentru modalul de întrebare.');
        }

        if (!modalCubeEl) return;

        modalCubeEl.addEventListener('shown.bs.modal', function () {
            if (statusEl) statusEl.textContent = 'Cubul se încarcă...';
            initCubeGame();
            setTimeout(function () {
                if (statusEl) {
                    statusEl.textContent = 'Cubul este gata. Joacă-te și urmărește mutările în Console (F12).';
                }
            }, 700);
        });

        modalCubeEl.addEventListener('hidden.bs.modal', function () {
            if (cubePollInterval) {
                clearInterval(cubePollInterval);
                cubePollInterval = null;
            }
            if (cubeSession) {
                console.log('%c[CUBE SESSION SUMMARY]', 'color:#ffa500;font-weight:bold;', cubeSession);
            }
        });
    });
</script>


<!-- /Wrapper -->

<!-- jQuery -->
<script src="<?=getCurrentUrl();?>/Templates/admin/vendors/jquery/dist/jquery.min.js"></script>

<!-- Bootstrap Core JS -->
<script src="<?=getCurrentUrl();?>/Templates/admin/vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

<!-- FeatherIcons JS -->
<script src="<?=getCurrentUrl();?>/Templates/admin/dist/js/feather.min.js"></script>

<!-- Fancy Dropdown JS -->
<script src="<?=getCurrentUrl();?>/Templates/admin/dist/js/dropdown-bootstrap-extended.js"></script>

<!-- Simplebar JS -->
<script src="<?=getCurrentUrl();?>/Templates/admin/vendors/simplebar/dist/simplebar.min.js"></script>

<!-- Data Table JS -->
<script src="<?=getCurrentUrl();?>/Templates/admin/vendors/datatables.net/js/dataTables.min.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/vendors/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/vendors/datatables.net-select/js/dataTables.select.min.js"></script>

<!-- Daterangepicker JS -->
<script src="<?=getCurrentUrl();?>/Templates/admin/vendors/moment/min/moment.min.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/vendors/daterangepicker/daterangepicker.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/dist/js/daterangepicker-data.js"></script>

<!-- Amcharts Maps JS -->
<script src="<?=getCurrentUrl();?>/Templates/admin/dist/index.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/dist/map.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/dist/geodata/worldLow.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/dist/Animated.js"></script>

<!-- Apex JS -->
<script src="<?=getCurrentUrl();?>/Templates/admin/vendors/apexcharts/dist/apexcharts.min.js"></script>

<!-- Init JS -->
<script src="<?=getCurrentUrl();?>/Templates/admin/dist/js/init.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/dist/js/chips-init.js"></script>
<script src="<?=getCurrentUrl();?>/Templates/admin/dist/js/dashboard-data.js"></script>