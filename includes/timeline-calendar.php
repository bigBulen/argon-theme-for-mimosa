<?php
/**
 * Timeline Calendar System for Argon Theme
 * 垂直时间轴日历系统
 */

class ArgonTimelineCalendar {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'argon_timeline_events';
        
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_timeline_save_event', array($this, 'ajax_save_event'));
        add_action('wp_ajax_timeline_delete_event', array($this, 'ajax_delete_event'));
        add_action('wp_ajax_timeline_get_event', array($this, 'ajax_get_event'));
        
        // 注册短代码
        add_shortcode('argon_timeline', array($this, 'timeline_shortcode'));
        
        // 激活时创建数据库表
        register_activation_hook(__FILE__, array($this, 'create_table'));
    }
    
    public function init() {
        $this->create_table();
    }
    
    /**
     * 创建数据库表
     */
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text,
            event_date date NOT NULL,
            event_time time DEFAULT NULL,
            category varchar(100) DEFAULT 'general',
            text_color varchar(7) DEFAULT '#333333',
            background_color varchar(7) DEFAULT '#f8f9fa',
            icon varchar(50) DEFAULT 'fas fa-calendar',
            status varchar(20) DEFAULT 'published',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_date (event_date),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * 加载前端样式和脚本
     */
    public function enqueue_scripts() {
        wp_enqueue_style('argon-timeline-calendar', get_template_directory_uri() . '/assets/css/timeline-calendar.css', array(), '1.0.0');
        wp_enqueue_script('argon-timeline-calendar', get_template_directory_uri() . '/assets/js/timeline-calendar.js', array('jquery'), '1.0.0', true);
    }
    
    /**
     * 加载后台样式和脚本
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'timeline-calendar') === false) {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('argon-timeline-admin', get_template_directory_uri() . '/assets/css/timeline-admin.css', array(), '1.0.0');
        wp_enqueue_script('argon-timeline-admin', get_template_directory_uri() . '/assets/js/timeline-admin.js', array('jquery', 'wp-color-picker'), '1.0.0', true);
        
        wp_localize_script('argon-timeline-admin', 'timeline_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('timeline_nonce')
        ));
    }
    
    /**
     * 添加后台菜单
     */
    public function add_admin_menu() {
        add_menu_page(
            '时间轴日历',
            '时间轴日历',
            'manage_options',
            'timeline-calendar',
            array($this, 'admin_page'),
            'dashicons-calendar-alt',
            30
        );
    }
    
    /**
     * 后台管理页面
     */
    public function admin_page() {
        $categories = $this->get_categories();
        ?>
        <div class="wrap">
            <h1>时间轴日历管理</h1>
            
            <div class="timeline-admin-container">
                <div class="timeline-admin-header">
                    <button type="button" class="button button-primary" id="add-new-event">添加新事件</button>
                </div>
                
                <div class="timeline-events-list">
                    <?php $this->display_events_table(); ?>
                </div>
            </div>
            
            <!-- 事件编辑模态框 -->
            <div id="event-modal" class="timeline-modal" style="display: none;">
                <div class="timeline-modal-content">
                    <div class="timeline-modal-header">
                        <h2 id="modal-title">添加事件</h2>
                        <span class="timeline-modal-close">&times;</span>
                    </div>
                    <div class="timeline-modal-body">
                        <form id="event-form">
                            <input type="hidden" id="event-id" name="event_id" value="">
                            
                            <div class="form-group">
                                <label for="event-title">事件标题 *</label>
                                <input type="text" id="event-title" name="title" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="event-description">事件描述</label>
                                <textarea id="event-description" name="description" rows="4"></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="event-date">事件日期 *</label>
                                    <input type="date" id="event-date" name="event_date" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="event-time">事件时间</label>
                                    <div class="event-time-row">
                                        <input type="time" id="event-time" name="event_time">
                                        <label class="all-day-label" style="margin-left:10px;">
                                            <input type="checkbox" id="event-all-day"> 全天
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="event-category">分类</label>
                                    <input type="text" id="event-category" name="category" list="timeline-category-list" placeholder="输入或选择分类">
                                    <datalist id="timeline-category-list">
                                        <?php if (!empty($categories)) { foreach ($categories as $c) { echo '<option value="' . esc_attr($c) . '"></option>'; } } ?>
                                    </datalist>
                                </div>
                                
                                <div class="form-group">
                                    <label for="event-icon">图标</label>
                                    <input type="text" id="event-icon" name="icon" placeholder="fas fa-calendar">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="text-color">文字颜色</label>
                                    <input type="text" id="text-color" name="text_color" class="color-picker" value="#333333">
                                </div>
                                
                                <div class="form-group">
                                    <label for="background-color">背景颜色</label>
                                    <input type="text" id="background-color" name="background_color" class="color-picker" value="#f8f9fa">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="event-status">状态</label>
                                <select id="event-status" name="status">
                                    <option value="published">已发布</option>
                                    <option value="draft">草稿</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="timeline-modal-footer">
                        <button type="button" class="button" id="cancel-event">取消</button>
                        <button type="button" class="button button-primary" id="save-event">保存</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * 显示事件列表表格
     */
    private function display_events_table() {
        global $wpdb;
        
        $events = $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY event_date ASC");
        
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>标题</th>
                    <th>日期</th>
                    <th>时间</th>
                    <th>分类</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="6">暂无事件</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><strong><?php echo esc_html($event->title); ?></strong></td>
                            <td><?php echo esc_html($event->event_date); ?></td>
                            <td><?php echo esc_html($event->event_time ?: '全天'); ?></td>
                            <td><?php echo esc_html($this->get_category_label($event->category)); ?></td>
                            <td>
                                <span class="status-<?php echo esc_attr($event->status); ?>">
                                    <?php echo esc_html($event->status === 'published' ? '已发布' : '草稿'); ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="button button-small edit-event" data-id="<?php echo esc_attr($event->id); ?>">编辑</button>
                                <button type="button" class="button button-small delete-event" data-id="<?php echo esc_attr($event->id); ?>">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }
    
    /**
     * 获取分类标签
     */
    private function get_category_label($category) {
        $labels = array(
            'game' => '游戏发售',
            'exhibition' => '展会活动',
            'conference' => '会议',
            'general' => '其他'
        );
        
        return isset($labels[$category]) ? $labels[$category] : $category;
    }
    
    private function get_categories() {
        global $wpdb;
        $rows = $wpdb->get_col("SELECT DISTINCT category FROM {$this->table_name} WHERE category IS NOT NULL AND category <> '' ORDER BY category ASC");
        return is_array($rows) ? $rows : array();
    }
    
    /**
     * AJAX保存事件
     */
    public function ajax_save_event() {
        check_ajax_referer('timeline_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('权限不足');
        }
        
        global $wpdb;
        
        $event_id = intval($_POST['event_id']);
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'description' => sanitize_textarea_field($_POST['description']),
            'event_date' => sanitize_text_field($_POST['event_date']),
            'event_time' => sanitize_text_field($_POST['event_time']),
            'category' => sanitize_text_field($_POST['category']),
            'text_color' => sanitize_hex_color($_POST['text_color']),
            'background_color' => sanitize_hex_color($_POST['background_color']),
            'icon' => sanitize_text_field($_POST['icon']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        if (empty($data['event_time'])) {
            $data['event_time'] = null;
        }
        
        if ($event_id > 0) {
            // 更新事件
            $result = $wpdb->update($this->table_name, $data, array('id' => $event_id));
        } else {
            // 新增事件
            $result = $wpdb->insert($this->table_name, $data);
        }
        
        if ($result !== false) {
            wp_send_json_success('保存成功');
        } else {
            wp_send_json_error('保存失败');
        }
    }
    
    /**
     * AJAX删除事件
     */
    public function ajax_delete_event() {
        check_ajax_referer('timeline_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('权限不足');
        }
        
        global $wpdb;
        
        $event_id = intval($_POST['event_id']);
        $result = $wpdb->delete($this->table_name, array('id' => $event_id));
        
        if ($result !== false) {
            wp_send_json_success('删除成功');
        } else {
            wp_send_json_error('删除失败');
        }
    }
    
    /**
     * AJAX获取事件详情
     */
    public function ajax_get_event() {
        check_ajax_referer('timeline_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('权限不足');
        }
        
        global $wpdb;
        
        $event_id = intval($_POST['event_id']);
        $event = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $event_id));
        
        if ($event) {
            wp_send_json_success($event);
        } else {
            wp_send_json_error('事件不存在');
        }
    }
    
    /**
     * 时间轴短代码
     */
    public function timeline_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => -1,
            'category' => '',
            'status' => 'published'
        ), $atts);
        
        global $wpdb;
        
        $where_conditions = array("status = %s");
        $where_values = array($atts['status']);
        
        if (!empty($atts['category'])) {
            $where_conditions[] = "category = %s";
            $where_values[] = $atts['category'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        $limit_clause = $atts['limit'] > 0 ? "LIMIT " . intval($atts['limit']) : "";
        
        $query = "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY event_date ASC {$limit_clause}";
        $events = $wpdb->get_results($wpdb->prepare($query, $where_values));
        
        if (empty($events)) {
            return '<div class="timeline-calendar-empty">暂无事件</div>';
        }
        
        return $this->render_timeline($events);
    }
    
    /**
     * 渲染时间轴
     */
    private function render_timeline($events) {
        ob_start();

        $now = new DateTime();
        $current_year_num = (int)$now->format('Y');
        $current_month_num = (int)$now->format('n');

        $grouped_events = [];
        foreach ($events as $event) {
            $event_date = new DateTime($event->event_date);
            $year = (int)$event_date->format('Y');
            $month = (int)$event_date->format('n');
            $grouped_events[$year][$month][] = $event;
        }
        
        // 按年份升序排序
        ksort($grouped_events, SORT_NUMERIC);

        ?>
        <div class="argon-timeline-calendar" id="argon-timeline-calendar">
            <div class="timeline-container">
                <?php foreach ($grouped_events as $year => $months):
                    $is_past_year = $year < $current_year_num;
                    $year_collapse_class = $is_past_year ? 'timeline-year-collapsed' : '';
                ?>
                    <div class="timeline-year-group <?php echo esc_attr($year_collapse_class); ?>" data-year="<?php echo esc_attr($year); ?>">
                        <div class="timeline-year-header">
                            <?php echo esc_html($year . '年'); ?>
                            <span class="timeline-toggle timeline-year-toggle" aria-hidden="true"></span>
                        </div>
                        <div class="timeline-year-events">
                            <?php 
                            // 按月份升序排序
                            ksort($months, SORT_NUMERIC);
                            foreach ($months as $month => $events_in_month):
                                $is_current_month = ($year == $current_year_num && $month == $current_month_num);
                                $is_past_month = ($year < $current_year_num) || ($year == $current_year_num && $month < $current_month_num);
                                $month_collapse_class = $is_past_month ? 'timeline-month-collapsed' : '';
                                $month_id = "timeline-month-{$year}-{$month}";
                            ?>
                                <div class="timeline-month-wrapper <?php echo esc_attr($month_collapse_class); ?>" id="<?php echo esc_attr($month_id); ?>" data-month="<?php echo esc_attr($month); ?>">
                                    <div class="timeline-month-header">
                                    <?php echo esc_html($month . '月'); ?>
                                    <span class="timeline-toggle timeline-month-toggle" aria-hidden="true"></span>
                                </div>
                                    <div class="timeline-month-events">
                                        <?php foreach ($events_in_month as $event): ?>
                                            <div class="timeline-item" data-category="<?php echo esc_attr($event->category); ?>">
                                                <div class="timeline-marker">
                                                    <i class="<?php echo esc_attr($event->icon ?: 'fas fa-calendar'); ?>"></i>
                                                </div>
                                                <div class="timeline-content" style="color: <?php echo esc_attr($event->text_color); ?> !important; background-color: <?php echo esc_attr($event->background_color); ?> !important;">
                                                    <div class="timeline-date">
                                                        <?php echo esc_html(date('m月d日', strtotime($event->event_date))); ?>
                                                        <?php if ($event->event_time): ?>
                                                            <span class="timeline-time"><?php echo esc_html(date('H:i', strtotime($event->event_time))); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <h3 class="timeline-title" style="color: <?php echo esc_attr($event->text_color); ?> !important;"><?php echo esc_html($event->title); ?></h3>
                                                    <?php if ($event->description): ?>
                                                        <div class="timeline-description"><?php echo wp_kses_post(wpautop($event->description)); ?></div>
                                                    <?php endif; ?>
                                                    <div class="timeline-category category-<?php echo esc_attr($event->category); ?>">
                                                        <?php echo esc_html($this->get_category_label($event->category)); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// 初始化时间轴日历系统
new ArgonTimelineCalendar();

// 引入示例数据功能
require_once get_template_directory() . '/includes/timeline-sample-data.php';
