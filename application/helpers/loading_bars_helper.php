<?php
function flushLoadingBar($showPreloading = false)
{
    flushLoadingBarLegacy($showPreloading);
}
function flushLoadingBarLegacy($showPreloading = false)
{
?>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <div style="height:100%">
        <div class="container" style="margin-top: 3rem;">
            <div class="panel panel-primary">
                <div class="panel-body">
                    <h2>Do not close this window until the export is completed!</h3>
                </div>
            </div>

            <?php if ($showPreloading) : ?>
                <div class="loader-section">
                    <h4>Preparing Data</h4>
                    <div id="preparing-data">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                0%
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <div class="loader-section">
                <h4>Extracting Data</h4>
                <div id="loading1">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                            0%
                        </div>
                    </div>
                </div>
            </div>
            <div class="loader-section">
                <h4>Generating File <img id="imm" style="width:70px; margin-bottom:5px" src="https://media.giphy.com/media/3oEjI6SIIHBdRxXI40/giphy.gif"></h4>
                <div id="loading2">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                            0%
                        </div>
                    </div>
                </div>
            </div>
            <script>
                $(document).ready(function() {
                    $("#imm").hide()
                })
            </script>
        <?php
        ob_flush();
        flush();
    }

    function flushLoadingBarModern($showPreloading = false)
    {
        ?>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
            <style>
                :root {
                    --brand-start: #00c5fb;
                    --brand-end: #0253cc;
                    --brand-mid: #0688e8;
                }

                html,
                body {
                    min-height: 100%;
                }

                body {
                    margin: 0;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                    background:
                        radial-gradient(circle at top left, rgba(63, 142, 208, 0.16), transparent 30%),
                        radial-gradient(circle at top right, rgba(23, 50, 77, 0.10), transparent 26%),
                        linear-gradient(180deg, #f5f8fc 0%, #eaf0f7 100%);
                    color: #203244;
                    overflow-x: hidden;
                }

                .export-progress-page {
                    min-height: 100vh;
                    display: flex;
                    align-items: flex-start;
                    justify-content: center;
                    padding: 8px 12px 10px;
                }

                .export-progress-shell {
                    width: 100%;
                    max-width: 1080px;
                }

                .export-progress-hero {
                    position: relative;
                    overflow: hidden;
                    border-radius: 24px;
                    padding: 16px 20px;
                    color: #fff;
                    background: -webkit-linear-gradient(left, var(--brand-start) 0%, var(--brand-end) 100%);
                    background: linear-gradient(to right, var(--brand-start) 0%, var(--brand-end) 100%);
                    box-shadow: 0 22px 50px rgba(19, 41, 67, 0.16);
                    margin-bottom: 10px;
                }

                .export-progress-hero:before,
                .export-progress-hero:after {
                    content: '';
                    position: absolute;
                    border-radius: 999px;
                    background: rgba(255, 255, 255, 0.10);
                    pointer-events: none;
                }

                .export-progress-hero:before {
                    width: 220px;
                    height: 220px;
                    top: -110px;
                    right: -90px;
                }

                .export-progress-hero:after {
                    width: 150px;
                    height: 150px;
                    bottom: -70px;
                    left: 64%;
                }

                .export-progress-kicker {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    padding: 5px 10px;
                    border-radius: 999px;
                    background: rgba(255, 255, 255, 0.14);
                    font-size: 11px;
                    letter-spacing: 0.05em;
                    text-transform: uppercase;
                    margin-bottom: 8px;
                }

                .export-progress-title {
                    margin: 0;
                    font-size: 24px;
                    line-height: 1.18;
                    font-weight: 700;
                    letter-spacing: -0.02em;
                    color: #fff;
                }

                .export-progress-subtitle {
                    margin: 6px 0 0;
                    max-width: 860px;
                    font-size: 13px;
                    line-height: 1.4;
                    color: rgba(255, 255, 255, 0.9);
                }

                .export-progress-card {
                    background: rgba(255, 255, 255, 0.96);
                    border-radius: 24px;
                    box-shadow: 0 20px 45px rgba(24, 42, 68, 0.10);
                    border: 1px solid rgba(134, 159, 189, 0.22);
                    overflow: hidden;
                    margin-bottom: 4px;
                }

                .export-progress-card-body {
                    padding: 14px 16px 14px;
                    display: grid;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                    gap: 10px 12px;
                    align-items: start;
                }

                .export-progress-warning {
                    margin-bottom: 0;
                    padding: 10px 12px;
                    border-radius: 18px;
                    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                    border: 1px solid #d9e4f1;
                    color: #1e2e3f;
                    box-shadow: 0 10px 24px rgba(20, 39, 62, 0.06);
                    grid-column: 1 / -1;
                }

                .export-progress-warning h2 {
                    margin: 0;
                    font-size: 16px;
                    line-height: 1.35;
                    font-weight: 700;
                    color: #17324d;
                }

                .loader-section {
                    margin-bottom: 0;
                }

                .loader-section h4 {
                    margin: 0 0 6px;
                    font-size: 12px;
                    font-weight: 700;
                    letter-spacing: 0.02em;
                    color: #50657d;
                    text-transform: uppercase;
                }

                .loader-section {
                    padding: 8px 10px;
                    border-radius: 12px;
                    border: 1px solid #e1eaf4;
                    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                }

                .progress {
                    height: 25px;
                    margin-bottom: 0;
                    border-radius: 999px;
                    background-color: #e8eef6;
                    box-shadow: inset 0 1px 2px rgba(16, 30, 47, 0.06);
                    overflow: hidden;
                }

                .progress-bar {
                    border-radius: 999px;
                    background: -webkit-linear-gradient(left, var(--brand-start) 0%, var(--brand-end) 100%);
                    background: linear-gradient(to right, var(--brand-start) 0%, var(--brand-end) 100%);
                    font-size: 10px;
                    font-weight: 700;
                    line-height: 25px;
                    box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.2);
                    transition: width 0.25s ease;
                }

                #preparing-data .progress {
                    background-color: #d6f4ff;
                }

                #preparing-data .progress-bar {
                    background: -webkit-linear-gradient(left, #53dcff 0%, #00c5fb 100%);
                    background: linear-gradient(to right, #53dcff 0%, #00c5fb 100%);
                }

                #loading1 .progress {
                    background-color: #d8e6ff;
                }

                #loading1 .progress-bar {
                    background: -webkit-linear-gradient(left, var(--brand-start) 0%, var(--brand-end) 100%);
                    background: linear-gradient(to right, var(--brand-start) 0%, var(--brand-end) 100%);
                }

                #loading2 .progress {
                    background-color: #d4def7;
                }

                #loading2 .progress-bar {
                    background: -webkit-linear-gradient(left, var(--brand-mid) 0%, var(--brand-end) 100%);
                    background: linear-gradient(to right, var(--brand-mid) 0%, var(--brand-end) 100%);
                }

                #imm {
                    width: 56px;
                    margin-left: 8px;
                    margin-bottom: 3px;
                    vertical-align: middle;
                }

                .export-progress-footer {
                    margin-top: 2px;
                    font-size: 11px;
                    color: #6b7f93;
                    grid-column: 1 / -1;
                }

                .export-complete-slot {
                    grid-column: 1 / -1;
                    margin-top: 2px;
                    padding-top: 10px;
                    border-top: 1px solid #e2ebf5;
                    display: none;
                }

                .export-complete-title {
                    margin: 0 0 8px;
                    color: #17324d;
                    font-size: 17px;
                    font-weight: 700;
                    text-align: center;
                }

                .export-complete-actions {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 10px;
                    align-items: start;
                }

                .export-complete-actions center {
                    display: block;
                    width: 100%;
                    margin: 0;
                }

                .export-complete-actions center>div {
                    width: 100% !important;
                    max-width: none;
                    margin: 0 !important;
                }

                .export-progress-page .btn.btn-primary.btn-block {
                    height: 42px;
                    border-radius: 12px;
                    border: 0;
                    background: -webkit-linear-gradient(left, var(--brand-start) 0%, var(--brand-end) 100%);
                    background: linear-gradient(to right, var(--brand-start) 0%, var(--brand-end) 100%);
                    box-shadow: 0 8px 18px rgba(47, 111, 168, 0.22);
                    font-weight: 600;
                    letter-spacing: 0.01em;
                    transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
                }

                .export-progress-page .btn.btn-primary.btn-block:hover,
                .export-progress-page .btn.btn-primary.btn-block:focus {
                    transform: translateY(-1px);
                    box-shadow: 0 14px 28px rgba(47, 111, 168, 0.30);
                    filter: brightness(1.02);
                }

                .export-progress-page .btn.btn-primary.btn-block:active {
                    transform: translateY(0);
                    box-shadow: 0 8px 18px rgba(47, 111, 168, 0.22);
                }

                .export-progress-page .btn.btn-primary.btn-block+.btn.btn-primary.btn-block {
                    margin-top: 8px;
                }

                @media (max-width: 767px) {
                    .export-progress-page {
                        padding: 6px 8px 8px;
                    }

                    .export-progress-hero {
                        padding: 14px 12px;
                    }

                    .export-progress-title {
                        font-size: 20px;
                    }

                    .export-progress-card-body {
                        padding: 12px 10px 12px;
                        grid-template-columns: 1fr;
                    }

                    .export-progress-warning h2 {
                        font-size: 15px;
                    }

                    .export-complete-actions {
                        grid-template-columns: 1fr;
                    }

                    .export-complete-actions center>div {
                        max-width: 100%;
                    }
                }
            </style>
            <div class="export-progress-page">
                <div class="export-progress-shell">
                    <div class="export-progress-hero">
                        <div class="export-progress-kicker">Export in progress</div>
                        <h1 class="export-progress-title">Preparing your file</h1>
                        <p class="export-progress-subtitle">The export is running in the background. Keep this tab open while the report is being prepared, extracted, and generated.</p>
                    </div>

                    <div class="export-progress-card">
                        <div class="export-progress-card-body">
                            <div class="export-progress-warning">
                                <h2>Do not close this window until the export is completed!</h2>
                            </div>

                            <?php if ($showPreloading) : ?>
                                <div class="loader-section">
                                    <h4>Preparing Data</h4>
                                    <div id="preparing-data">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                                0%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif ?>

                            <div class="loader-section">
                                <h4>Extracting Data</h4>
                                <div id="loading1">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                            0%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="loader-section" style="margin-bottom: 0;">
                                <h4>Generating File </h4>
                                <div id="loading2">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                            0%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="export-progress-footer">The progress bars will update automatically while the export runs.</div>

                            <div id="export-complete-slot" class="export-complete-slot">
                                <div id="export-complete-title" class="export-complete-title">Export Completed</div>
                                <div id="export-complete-actions" class="export-complete-actions"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                $(document).ready(function() {
                    $("#imm").hide();

                    function moveCompletionNodesIntoCard() {
                        var slot = document.getElementById('export-complete-slot');
                        var title = document.getElementById('export-complete-title');
                        var actions = document.getElementById('export-complete-actions');

                        if (!slot || !title || !actions) {
                            return;
                        }

                        var bodyChildren = Array.prototype.slice.call(document.body.children);
                        var completionLabel = null;
                        var actionCenters = [];

                        bodyChildren.forEach(function(node) {
                            if (node.tagName === 'B' && /Export\s+Completed/i.test(node.textContent || '')) {
                                completionLabel = node;
                            }

                            if (node.tagName === 'CENTER' && node.querySelector('.btn')) {
                                actionCenters.push(node);
                            }
                        });

                        if (!completionLabel && actionCenters.length === 0) {
                            return;
                        }

                        if (completionLabel) {
                            title.textContent = (completionLabel.textContent || 'Export Completed').trim();
                            completionLabel.remove();
                        }

                        actionCenters.forEach(function(centerNode) {
                            actions.appendChild(centerNode);
                        });

                        slot.style.display = 'block';
                    }

                    moveCompletionNodesIntoCard();

                    var observer = new MutationObserver(function() {
                        moveCompletionNodesIntoCard();
                    });

                    observer.observe(document.body, {
                        childList: true
                    });
                });
            </script>
        <?php
        ob_flush();
        flush();
    }
