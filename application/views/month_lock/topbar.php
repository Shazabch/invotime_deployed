<style>
    /* =============== TOP BAR =============== */
    .topbar {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;

    }

    .topbar-inner {
        display: flex;
        align-items: center;
        padding: 8px 15px;
        gap: 15px;
        flex-wrap: wrap;
    }

    .topbar-search {
        margin-right: 10px;
    }

    .search-wrapper {
        position: relative;
        width: 280px;
    }

    .topbar-search input {
        width: 100%;
        height: 38px;
        padding-left: 36px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
        font-size: 13.5px;
    }

    .topbar-search i {
        position: absolute;
        left: 12px;
        top: 11px;
        color: #94a3b8;
    }

    /* Navigation */
    .topbar-nav-list {
        display: flex;
        align-items: center;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 3px;
        flex-wrap: wrap;
    }

    .topbar-nav-item {
        position: relative;
    }

    .topbar-nav-link {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 10px 16px;
        color: #334155;
        text-decoration: none;
        border-radius: 7px;
        font-size: 14px;
        white-space: nowrap;
        transition: all 0.2s;
    }

    .topbar-nav-link:hover,
    .topbar-nav-item.active .topbar-nav-link {
        background: #f1f5f9;
        color: #1e40af;
    }

    .topbar-has-submenu .topbar-arrow {
        font-size: 10px;
        margin-left: 4px;
    }

    /* Dropdown */
    .topbar-submenu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        min-width: 210px;
        padding: 6px 0;
        z-index: 1000;
        list-style: none;
    }

    .topbar-has-submenu:hover>.topbar-submenu {
        display: block;
    }

    .topbar-submenu a {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        color: #334155;
        text-decoration: none;
    }

    .topbar-submenu a:hover,
    .topbar-submenu .active {
        background: #f8fafc;
        color: #1e40af;
    }

    .topbar-menu-title {
        padding: 10px 12px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
<!-- ====================== TOP BAR MENU ====================== -->
<div class="page-top-wrapper">
    <div class="topbar " id="topbar">
        <div class="topbar-inner">

            <!-- Search (only shows when month lock is active) -->
            <?php if (isset($is_month_lock) && $is_month_lock): ?>
                <div class="topbar-search">
                    <div class="search-wrapper">
                        <i class="fa fa-search"></i>
                        <input type="text" id="topbar-menu-search" class="topbar-search-input" placeholder="Search menus...">
                    </div>
                </div>
            <?php endif; ?>

            <!-- Horizontal Navigation -->
            <nav class="topbar-nav">
                <ul class="topbar-nav-list">
                    <?php
                    $bottom_item = null;

                    foreach ($menusTop as $key => $value) {
                        if (isset($value['status']) && !$value['status']) continue;
                        if (isset($value['is_bottom']) && $value['is_bottom']) {
                            $bottom_item = $value;
                            continue;
                        }

                        if (isset($value['is_title']) && $value['is_title']) {
                            echo '<li class="topbar-menu-title">' . $value["title"] . '</li>';
                            continue;
                        }

                        $select_this = false;
                        if ($value['url'] != null) {
                            $select_this = ($active_menu == $value["url"]) ? true : false;
                        }
                        if (!empty($value['sub_menus'])) {
                            $submenu_is_selected = array_search($active_menu, array_column($value['sub_menus'], 'url'));
                            if (!is_bool($submenu_is_selected)) {
                                $select_this = true;
                            }
                        }
                    ?>
                        <li class="topbar-nav-item <?php echo (!empty($value["sub_menus"])) ? 'topbar-has-submenu' : ''; ?> <?php echo $select_this ? 'active' : ''; ?>">

                            <a href="<?php echo $value["url"] ? base_url() . $value["url"] : 'javascript:void(0);' ?>"
                                class="topbar-nav-link <?php echo $select_this ? 'active' : ''; ?>"
                                title="<?php echo $value["title"] ?>">
                                <i class="<?php echo $value["icon"] ?>"></i>
                                <span><?php echo $value["title"] ?></span>
                                <?php if (!empty($value["sub_menus"])): ?>
                                    <span class="topbar-arrow">▼</span>
                                <?php endif; ?>
                            </a>

                            <?php if (!empty($value['sub_menus'])): ?>
                                <ul class="topbar-submenu">
                                    <?php foreach ($value["sub_menus"] as $value_submenu):
                                        if (isset($value_submenu['status']) && !$value_submenu['status']) continue;
                                        $select_this_submenu = ($value_submenu['url'] && $active_menu == $value_submenu["url"]) ? true : false;
                                    ?>
                                        <li>
                                            <a href="<?php echo base_url() . $value_submenu["url"] ?>"
                                                class="<?php echo $select_this_submenu ? 'active' : ''; ?>"
                                                title="<?php echo $value_submenu["title"] ?>">
                                                <i class="<?php echo $value_submenu["icon"] ?>"></i>
                                                <span><?php echo $value_submenu["title"] ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php } ?>

                    <!-- System Bottom Item -->
                    <?php if ($bottom_item): ?>
                        <li class="topbar-nav-item topbar-system-item">
                            <a href="<?php echo base_url() . $bottom_item["url"] ?>" class="topbar-nav-link">
                                <i class="<?php echo $bottom_item["icon"] ?>" style="color: #ef4444;"></i>
                                <span style="font-weight: 600;"><?php echo $bottom_item["title"] ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

        </div>
    </div>
</div>

<!-- ====================== END TOP BAR ====================== -->

<?php if (isset($is_month_lock) && $is_month_lock): ?>
    <script>
        $(document).ready(function() {
            $('#topbar-menu-search').on('keyup', function() {
                var value = $(this).val().toLowerCase();

                $('.topbar-nav-list > li.topbar-nav-item').each(function() {
                    var text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(value) > -1);
                });
            });
        });
    </script>
<?php endif; ?>