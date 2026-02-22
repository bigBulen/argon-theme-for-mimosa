<?php
/**
 * 时间轴日历示例数据
 * Timeline Calendar Sample Data
 */

function argon_timeline_create_sample_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'argon_timeline_events';
    
    // 检查是否已经有数据
    $existing_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    if ($existing_count > 0) {
        return false; // 已有数据，不创建示例数据
    }
    
    $sample_events = array(
        array(
            'title' => '《塞尔达传说：王国之泪》发售',
            'description' => '任天堂Switch平台独占大作，《塞尔达传说：旷野之息》的正统续作。',
            'event_date' => '2025-05-12',
            'event_time' => '00:00:00',
            'category' => 'game',
            'text_color' => '#2c3e50',
            'background_color' => '#e8f5e8',
            'icon' => 'fas fa-gamepad',
            'status' => 'published'
        ),
        array(
            'title' => 'E3 2025 游戏展',
            'description' => '全球最大的电子娱乐展览会，将展示最新的游戏作品和技术。',
            'event_date' => '2025-06-15',
            'event_time' => '09:00:00',
            'category' => 'exhibition',
            'text_color' => '#2c3e50',
            'background_color' => '#e3f2fd',
            'icon' => 'fas fa-calendar-alt',
            'status' => 'published'
        ),
        array(
            'title' => '《最终幻想XVI》PC版发售',
            'description' => 'Square Enix经典RPG系列最新作品登陆PC平台。',
            'event_date' => '2025-07-20',
            'event_time' => '12:00:00',
            'category' => 'game',
            'text_color' => '#2c3e50',
            'background_color' => '#fff3e0',
            'icon' => 'fas fa-desktop',
            'status' => 'published'
        ),
        array(
            'title' => 'Gamescom 2025',
            'description' => '欧洲最大的游戏展览会，在德国科隆举办。',
            'event_date' => '2025-08-25',
            'event_time' => '10:00:00',
            'category' => 'exhibition',
            'text_color' => '#2c3e50',
            'background_color' => '#f3e5f5',
            'icon' => 'fas fa-globe-europe',
            'status' => 'published'
        ),
        array(
            'title' => '《赛博朋克2077》资料片发售',
            'description' => 'CD Projekt RED开发的大型资料片"夜之城传说"。',
            'event_date' => '2025-09-10',
            'event_time' => '16:00:00',
            'category' => 'game',
            'text_color' => '#2c3e50',
            'background_color' => '#fce4ec',
            'icon' => 'fas fa-robot',
            'status' => 'published'
        ),
        array(
            'title' => 'Tokyo Game Show 2025',
            'description' => '日本最大的游戏展览会，展示亚洲地区最新游戏作品。',
            'event_date' => '2025-09-28',
            'event_time' => '09:30:00',
            'category' => 'exhibition',
            'text_color' => '#2c3e50',
            'background_color' => '#ffebee',
            'icon' => 'fas fa-torii-gate',
            'status' => 'published'
        ),
        array(
            'title' => '《巫师4》正式公布',
            'description' => 'CD Projekt RED在开发者大会上正式公布《巫师》系列新作。',
            'event_date' => '2025-10-15',
            'event_time' => '14:00:00',
            'category' => 'conference',
            'text_color' => '#2c3e50',
            'background_color' => '#e8f5e8',
            'icon' => 'fas fa-magic',
            'status' => 'published'
        ),
        array(
            'title' => '《使命召唤：现代战争4》发售',
            'description' => 'Activision年度大作，支持跨平台联机游戏。',
            'event_date' => '2025-11-08',
            'event_time' => '00:00:00',
            'category' => 'game',
            'text_color' => '#2c3e50',
            'background_color' => '#f1f8e9',
            'icon' => 'fas fa-crosshairs',
            'status' => 'published'
        ),
        array(
            'title' => 'The Game Awards 2025',
            'description' => '年度游戏颁奖典礼，表彰本年度最优秀的游戏作品。',
            'event_date' => '2025-12-12',
            'event_time' => '20:00:00',
            'category' => 'conference',
            'text_color' => '#2c3e50',
            'background_color' => '#fff8e1',
            'icon' => 'fas fa-trophy',
            'status' => 'published'
        )
    );
    
    foreach ($sample_events as $event) {
        $wpdb->insert($table_name, $event);
    }
    
    return true;
}

// 添加管理员菜单项来创建示例数据
add_action('admin_menu', function() {
    add_submenu_page(
        'timeline-calendar',
        '示例数据',
        '示例数据',
        'manage_options',
        'timeline-sample-data',
        'argon_timeline_sample_data_page'
    );
});

function argon_timeline_sample_data_page() {
    if (isset($_POST['create_sample_data'])) {
        if (argon_timeline_create_sample_data()) {
            echo '<div class="notice notice-success"><p>示例数据创建成功！</p></div>';
        } else {
            echo '<div class="notice notice-warning"><p>数据库中已存在事件数据，未创建示例数据。</p></div>';
        }
    }
    
    if (isset($_POST['clear_all_data'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'argon_timeline_events';
        $wpdb->query("TRUNCATE TABLE {$table_name}");
        echo '<div class="notice notice-success"><p>所有数据已清空！</p></div>';
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'argon_timeline_events';
    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    
    ?>
    <div class="wrap">
        <h1>时间轴日历 - 示例数据</h1>
        
        <div class="card" style="max-width: 600px; margin-top: 20px;">
            <div class="card-body" style="padding: 20px;">
                <h3>数据库状态</h3>
                <p>当前数据库中有 <strong><?php echo $count; ?></strong> 个事件。</p>
                
                <hr>
                
                <h3>操作选项</h3>
                
                <form method="post" style="margin-bottom: 20px;">
                    <p>创建9个示例事件数据，包括游戏发售、展会活动等不同类型的事件。</p>
                    <button type="submit" name="create_sample_data" class="button button-primary" 
                            onclick="return confirm('确定要创建示例数据吗？')">
                        创建示例数据
                    </button>
                </form>
                
                <form method="post">
                    <p style="color: #d63384;">⚠️ 危险操作：清空所有时间轴事件数据。</p>
                    <button type="submit" name="clear_all_data" class="button button-secondary" 
                            onclick="return confirm('确定要清空所有数据吗？此操作不可恢复！')">
                        清空所有数据
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card" style="max-width: 600px; margin-top: 20px;">
            <div class="card-body" style="padding: 20px;">
                <h3>使用说明</h3>
                <ol>
                    <li>创建一个新页面，选择"时间轴日历"模板</li>
                    <li>或者在任意页面/文章中使用短代码：<code>[argon_timeline]</code></li>
                    <li>支持参数：
                        <ul>
                            <li><code>[argon_timeline limit="5"]</code> - 限制显示数量</li>
                            <li><code>[argon_timeline category="game"]</code> - 只显示特定分类</li>
                            <li><code>[argon_timeline status="published"]</code> - 只显示已发布的事件</li>
                        </ul>
                    </li>
                </ol>
            </div>
        </div>
    </div>
    <?php
}