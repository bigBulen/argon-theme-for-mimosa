<?php
/**
 * 
 * Apex Media List: 番剧/galgame 清单 + 短代码 + 后台管理
 * - CPT: apex_anime / apex_galgame（CPT已废弃）
 * - Tax: apex_status (想看 want / 在看 watching / 看过 watched)（CPT已废弃）
 * - Meta: _apex_cover_url, _apex_bgm_url, _apex_score, _apex_season_text, _apex_review, _apex_year, _apex_quarter（CPT已废弃）
 * - Shortcodes: [apex_anime_list] [apex_galgame_list]
 * 
 * 在主列表中显示「首页展示」标签
 * 主列表有标签的点击可进入
 * 为每个条目建立详情页（待定）
 * id
 *  type(anime/galgame)
 *  title
 * status(watched/watching/want)
 *  bgm_url(bangumi/vndb作品详情)
 * cover_source_url
 * cover_attachment_id 
 * score_100 score_base（百分制分数，千万别用，用下面的十分制）
  * season_text（发售日期，如2026年冬季）
  * year （发售日期年）
 * quarter（发售日期季）
  * review （个人评论）
 * finished_at （结束观看）
 * show_on_home （在侧边栏展示，现在不用）
 * home_rank （侧边栏优先级，不用）
 * legacy_post_id 
 * created_at（条目创建时间）
 * updated_at（条目最后更新时间）
 * score_10（十分制，用这个分数）
 * slug
 * show_on_home_feed（1代表在首页展示卡片）
 */

if (!defined('ABSPATH')) exit;

class Apex_Media_List {
    const CPT_ANIME   = 'apex_anime';
    const CPT_GALGAME = 'apex_galgame';
    const TAX_STATUS  = 'apex_status';

    const DB_VERSION        = '4';
    const OPTION_DB_VERSION = 'apex_media_db_version';
    const TABLE_ITEMS       = 'apex_media_items';
    const TABLE_TAGS        = 'apex_media_tags';
    const TABLE_ITEM_TAGS   = 'apex_media_item_tags';

    private $status_terms = [
        'want'     => '想看',
        'watching' => '在看',
        'watched'  => '看过',
    ];
    private $quarters = ['spring' => '春季', 'summer' => '夏季', 'autumn' => '秋季', 'winter' => '冬季'];

    public function get_status_label($slug) {
        return isset($this->status_terms[$slug]) ? $this->status_terms[$slug] : '';
    }

    // 古腾堡文章区块 预览卡片
    public function get_media_row_by_id($id) {
        global $wpdb;

        $id = intval($id);
        if ($id <= 0) return null;

        $cache_key = 'apex_media_item_' . $id;
        $cached = wp_cache_get($cache_key, 'apex_media');
        if ($cached !== false) {
            return $cached;
        }

        $table = $wpdb->prefix . self::TABLE_ITEMS;

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE id = %d LIMIT 1",
                $id
            ),
            ARRAY_A
        );

        wp_cache_set($cache_key, $row, 'apex_media', 300); // 缓存 5 分钟

        return $row;
    }

    public function __construct() {
        add_action('init', [$this, 'maybe_upgrade_db'], 5);
        add_action('init', [$this, 'register_post_types']);
        add_action('init', [$this, 'register_taxonomies']);
        add_action('add_meta_boxes', [$this, 'register_meta_boxes']);
        add_action('save_post', [$this, 'save_meta']);
        add_shortcode('apex_anime_list', [$this, 'shortcode_anime']);
        add_shortcode('apex_galgame_list', [$this, 'shortcode_galgame']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        add_action('init', [$this, 'register_acgn_rewrite']);
        add_filter('query_vars', [$this, 'register_acgn_query_var']);
        add_filter('template_include', [$this, 'maybe_load_media_detail_template']);

        // 后台迁移工具入口 + 新表管理页
        add_action('admin_menu', [$this, 'register_tools_page']);
        add_action('admin_post_apex_migrate_covers', [$this, 'handle_migrate_covers']);
        add_action('admin_post_apex_migrate_media_data', [$this, 'handle_migrate_media_data']);
        add_action('admin_post_apex_media_save_item', [$this, 'handle_save_media_item']);
        add_action('admin_post_apex_media_home_feed_save', [$this, 'handle_save_home_feed']);
        add_action('admin_post_apex_media_home_feed_mode', [$this, 'handle_save_home_feed_mode']);
        add_action('admin_post_apex_media_delete_item', [$this, 'handle_delete_media_item']);
    }

    public function maybe_upgrade_db() {
        global $wpdb;

        $installed = get_option(self::OPTION_DB_VERSION);
        $needs_schema = !$this->is_items_table_schema_valid();
        if ($installed === self::DB_VERSION && !$needs_schema) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $table_items     = $wpdb->prefix . self::TABLE_ITEMS;
        $table_tags      = $wpdb->prefix . self::TABLE_TAGS;
        $table_item_tags = $wpdb->prefix . self::TABLE_ITEM_TAGS;

        $sql_items = "CREATE TABLE {$table_items} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            type varchar(16) NOT NULL DEFAULT '',
            title varchar(255) NOT NULL DEFAULT '',
            slug varchar(160) NULL,
            status varchar(16) NOT NULL DEFAULT '',
            bgm_url text NULL,
            cover_source_url text NULL,
            cover_attachment_id bigint(20) unsigned NULL,
            score_100 smallint(5) unsigned NULL,
            score_10 decimal(4,2) NULL,
            score_base decimal(4,2) NULL,
            season_text varchar(64) NOT NULL DEFAULT '',
            year smallint(5) unsigned NULL,
            quarter varchar(8) NOT NULL DEFAULT '',
            review longtext NULL,
            finished_at datetime NULL,
            show_on_home tinyint(1) unsigned NOT NULL DEFAULT 0,
            show_on_home_feed tinyint(1) unsigned NOT NULL DEFAULT 0,
            home_rank int(11) NULL,
            legacy_post_id bigint(20) unsigned NULL,
            created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY  (id),
            UNIQUE KEY legacy_post_id (legacy_post_id),
            UNIQUE KEY slug (slug),
            KEY type_status (type, status),
            KEY home_show (show_on_home, home_rank, updated_at),
            KEY home_feed (show_on_home_feed, finished_at, updated_at),
            KEY year_quarter (year, quarter)
        ) {$charset_collate};";

        $sql_tags = "CREATE TABLE {$table_tags} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            slug varchar(64) NOT NULL DEFAULT '',
            name varchar(128) NOT NULL DEFAULT '',
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug)
        ) {$charset_collate};";

        $sql_item_tags = "CREATE TABLE {$table_item_tags} (
            item_id bigint(20) unsigned NOT NULL,
            tag_id bigint(20) unsigned NOT NULL,
            PRIMARY KEY  (item_id, tag_id),
            KEY tag_id (tag_id)
        ) {$charset_collate};";

        dbDelta($sql_items);
        dbDelta($sql_tags);
        dbDelta($sql_item_tags);

        // 补写缺失的 score_10（不改 score_100）
        $this->backfill_score_10_if_missing();
        $this->backfill_slug_if_missing();

        update_option(self::OPTION_DB_VERSION, self::DB_VERSION);
    }

    private function get_items_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_ITEMS;
    }

    private function get_tags_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_TAGS;
    }

    private function get_item_tags_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_ITEM_TAGS;
    }

    private function get_table_columns($table_name) {
        global $wpdb;
        if (empty($table_name)) return [];
        $prev = $wpdb->suppress_errors(true);
        $columns = $wpdb->get_col("SHOW COLUMNS FROM {$table_name}", 0);
        $wpdb->suppress_errors($prev);
        if (empty($columns) || !is_array($columns)) return [];
        return array_map('strtolower', $columns);
    }

    private function is_items_table_schema_valid() {
        $table   = $this->get_items_table_name();
        $columns = $this->get_table_columns($table);
        if (empty($columns)) return false;
        $required = ['slug', 'show_on_home_feed', 'finished_at'];
        foreach ($required as $col) {
            if (!in_array($col, $columns, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 允许字段 + 对应 format 映射
     */
    private function normalize_media_item_data(array $data) {
        $allowed = [
            'type'               => '%s',
            'title'              => '%s',
            'slug'               => '%s',
            'status'             => '%s',
            'bgm_url'            => '%s',
            'cover_source_url'   => '%s',
            'cover_attachment_id'=> '%d',
            'score_100'          => '%d',
            'score_10'           => '%f',
            'score_base'         => '%f',
            'season_text'        => '%s',
            'year'               => '%d',
            'quarter'            => '%s',
            'review'             => '%s',
            'finished_at'        => '%s',
            'show_on_home'       => '%d',
            'show_on_home_feed'  => '%d',
            'home_rank'          => '%d',
            'legacy_post_id'     => '%d',
            'created_at'         => '%s',
            'updated_at'         => '%s',
        ];

        $out = [];
        $formats = [];
        foreach ($allowed as $key => $fmt) {
            if (array_key_exists($key, $data)) {
                $out[$key] = $data[$key];
                $formats[] = $fmt;
            }
        }

        return [$out, $formats];
    }

    /**
     * 通过 legacy_post_id 获取一条媒体记录
     */
    protected function media_item_get_by_legacy_post_id($legacy_post_id) {
        global $wpdb;
        $legacy_post_id = intval($legacy_post_id);
        if ($legacy_post_id <= 0) {
            return null;
        }

        $table = $this->get_items_table_name();
        $sql   = $wpdb->prepare("SELECT * FROM {$table} WHERE legacy_post_id = %d LIMIT 1", $legacy_post_id);
        $row   = $wpdb->get_row($sql, ARRAY_A);

        return $row ?: null;
    }

    /**
     * 通过 id 获取媒体记录
     */
    protected function media_item_get_by_id($id) {
        global $wpdb;
        $id = intval($id);
        if ($id <= 0) return null;
        $table = $this->get_items_table_name();
        $sql = $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id);
        $row = $wpdb->get_row($sql, ARRAY_A);
        return $row ?: null;
    }

    /**
     * 通过 slug 获取媒体记录
     */
    public function media_item_get_by_slug($slug) {
        global $wpdb;
        $slug = trim(sanitize_title($slug));
        if ($slug === '') return null;
        $table = $this->get_items_table_name();
        $sql = $wpdb->prepare("SELECT * FROM {$table} WHERE slug = %s LIMIT 1", $slug);
        $row = $wpdb->get_row($sql, ARRAY_A);
        return $row ?: null;
    }

    private function ensure_unique_slug($slug, $exclude_id = 0) {
        global $wpdb;
        $slug = sanitize_title($slug);
        if ($slug === '') return '';
        $table = $this->get_items_table_name();
        $base = $slug;
        $i = 2;
        while (true) {
            $sql = $wpdb->prepare("SELECT id FROM {$table} WHERE slug = %s AND id != %d LIMIT 1", $slug, intval($exclude_id));
            $exists = $wpdb->get_var($sql);
            if (!$exists) {
                return $slug;
            }
            $slug = $base . '-' . $i;
            $i++;
        }
    }

    private function generate_slug($title, $exclude_id = 0) {
        $slug = sanitize_title($title);
        if ($slug === '') {
            $slug = $exclude_id > 0 ? 'media-' . $exclude_id : 'media-' . strtolower(wp_generate_password(6, false, false));
        }
        return $this->ensure_unique_slug($slug, $exclude_id);
    }

    /**
     * 删除媒体记录
     */
    protected function media_item_delete($id) {
        global $wpdb;
        $id = intval($id);
        if ($id <= 0) return false;
        $table_items = $this->get_items_table_name();
        $table_item_tags = $this->get_item_tags_table_name();
        $wpdb->delete($table_item_tags, ['item_id' => $id], ['%d']);
        $result = $wpdb->delete($table_items, ['id' => $id], ['%d']);
        return $result !== false;
    }

    /**
     * 插入一条媒体记录，返回新 ID 或 false
     */
    protected function media_item_insert(array $data) {
        global $wpdb;
        $table = $this->get_items_table_name();

        $now = current_time('mysql');
        if (empty($data['created_at'])) {
            $data['created_at'] = $now;
        }
        $data['updated_at'] = $now;

        if (!empty($data['slug'])) {
            $data['slug'] = $this->ensure_unique_slug($data['slug']);
        }
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = $this->generate_slug($data['title']);
        }

        list($data, $formats) = $this->normalize_media_item_data($data);
        if (empty($data)) {
            return false;
        }

        $result = $wpdb->insert($table, $data, $formats);
        if ($result === false) {
            return false;
        }
        return (int) $wpdb->insert_id;
    }

    /**
     * 更新一条媒体记录，返回是否成功
     */
    protected function media_item_update($id, array $data) {
        global $wpdb;
        $id = intval($id);
        if ($id <= 0) {
            return false;
        }

        $table = $this->get_items_table_name();

        if (!empty($data['slug'])) {
            $data['slug'] = $this->ensure_unique_slug($data['slug'], $id);
        } elseif (!empty($data['title'])) {
            $data['slug'] = $this->generate_slug($data['title'], $id);
        }

        $data['updated_at'] = current_time('mysql');
        list($data, $formats) = $this->normalize_media_item_data($data);
        if (empty($data)) {
            return false;
        }

        $where = ['id' => $id];
        $where_formats = ['%d'];

        $result = $wpdb->update($table, $data, $where, $formats, $where_formats);
        return $result !== false;
    }

    /**
     * 通过 legacy_post_id 进行 upsert，返回最终 ID 或 false
     */
    protected function media_item_upsert_by_legacy_post_id($legacy_post_id, array $data) {
        $legacy_post_id = intval($legacy_post_id);
        if ($legacy_post_id <= 0) {
            return false;
        }

        $existing = $this->media_item_get_by_legacy_post_id($legacy_post_id);
        $data['legacy_post_id'] = $legacy_post_id;

        if ($existing) {
            $ok = $this->media_item_update((int) $existing['id'], $data);
            return $ok ? (int) $existing['id'] : false;
        }

        return $this->media_item_insert($data);
    }

    /**
     * 标签相关：通过 slug 获取标签
     */
    protected function media_tag_get_by_slug($slug) {
        global $wpdb;
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }
        $table = $this->get_tags_table_name();
        $sql   = $wpdb->prepare("SELECT * FROM {$table} WHERE slug = %s LIMIT 1", $slug);
        $row   = $wpdb->get_row($sql, ARRAY_A);
        return $row ?: null;
    }

    /**
     * 创建标签，返回行数据或 null
     */
    protected function media_tag_create($slug, $name) {
        global $wpdb;
        $slug = trim($slug);
        $name = trim($name);
        if ($name === '') {
            return null;
        }
        if ($slug === '') {
            $slug = sanitize_title($name);
        }

        $table = $this->get_tags_table_name();
        $data  = [
            'slug' => $slug,
            'name' => $name,
        ];
        $formats = ['%s', '%s'];

        $result = $wpdb->insert($table, $data, $formats);
        if ($result === false) {
            // 可能是唯一键冲突，尝试再查一次
            return $this->media_tag_get_by_slug($slug);
        }

        $id = (int) $wpdb->insert_id;
        return [
            'id'   => $id,
            'slug' => $slug,
            'name' => $name,
        ];
    }

    /**
     * 获取或创建标签
     */
    protected function media_tag_get_or_create($slug, $name) {
        $row = $this->media_tag_get_by_slug($slug);
        if ($row) {
            return $row;
        }
        return $this->media_tag_create($slug, $name);
    }

    /**
     * 为某个媒体项设置标签（以 slug/name 数组列表形式）
     * 传入格式：[['slug' => 'xxx', 'name' => '展示名'], ...]
     */
    protected function media_item_set_tags($item_id, array $tags) {
        global $wpdb;
        $item_id = intval($item_id);
        if ($item_id <= 0) {
            return false;
        }

        $table_item_tags = $this->get_item_tags_table_name();

        // 先删除旧关联
        $wpdb->delete($table_item_tags, ['item_id' => $item_id], ['%d']);

        if (empty($tags)) {
            return true;
        }

        foreach ($tags as $tag) {
            if (!is_array($tag)) continue;
            $slug = isset($tag['slug']) ? trim($tag['slug']) : '';
            $name = isset($tag['name']) ? trim($tag['name']) : '';
            if ($slug === '' && $name === '') continue;

            $row = $this->media_tag_get_or_create($slug, $name);
            if (!$row || empty($row['id'])) continue;

            $wpdb->insert($table_item_tags, [
                'item_id' => $item_id,
                'tag_id'  => (int) $row['id'],
            ], ['%d', '%d']);
        }

        return true;
    }

    /**
     * 获取某个媒体项的所有标签
     */
    public function media_item_get_tags($item_id) {
        global $wpdb;
        $item_id = intval($item_id);
        if ($item_id <= 0) {
            return [];
        }

        $table_tags      = $this->get_tags_table_name();
        $table_item_tags = $this->get_item_tags_table_name();

        $sql = $wpdb->prepare(
            "SELECT t.* FROM {$table_tags} t
             INNER JOIN {$table_item_tags} it ON it.tag_id = t.id
             WHERE it.item_id = %d
             ORDER BY t.name ASC",
            $item_id
        );

        $rows = $wpdb->get_results($sql, ARRAY_A);
        return $rows ?: [];
    }

    /**
     * 批量获取多个媒体项的标签映射
     * 返回形如 [item_id => [tagRow...], ...]
     */
    protected function media_items_get_tags_map(array $item_ids) {
        global $wpdb;
        if (empty($item_ids)) {
            return [];
        }
        $item_ids = array_values(array_filter(array_map('intval', $item_ids)));
        if (empty($item_ids)) {
            return [];
        }

        $table_tags      = $this->get_tags_table_name();
        $table_item_tags = $this->get_item_tags_table_name();
        list($ph, $vals) = $this->build_in_placeholders($item_ids, '%d');
        if (empty($ph)) {
            return [];
        }

        $sql = $wpdb->prepare(
            "SELECT it.item_id, t.* FROM {$table_item_tags} it
             INNER JOIN {$table_tags} t ON t.id = it.tag_id
             WHERE it.item_id IN ({$ph})
             ORDER BY t.name ASC",
            $vals
        );
        $rows = $wpdb->get_results($sql, ARRAY_A);
        if (!$rows) return [];

        $map = [];
        foreach ($rows as $row) {
            $iid = intval($row['item_id']);
            if (!isset($map[$iid])) {
                $map[$iid] = [];
            }
            $map[$iid][] = $row;
        }
        return $map;
    }

    /**
     * 获取常用标签（按关联次数降序）
     */
    protected function media_tags_popular($limit = 30) {
        global $wpdb;
        $limit = intval($limit);
        if ($limit <= 0) {
            $limit = 30;
        }
        $table_tags      = $this->get_tags_table_name();
        $table_item_tags = $this->get_item_tags_table_name();

        $sql = $wpdb->prepare(
            "SELECT t.*, COUNT(it.tag_id) AS usage_count
             FROM {$table_tags} t
             LEFT JOIN {$table_item_tags} it ON it.tag_id = t.id
             GROUP BY t.id
             ORDER BY usage_count DESC, t.name ASC
             LIMIT %d",
            $limit
        );

        $rows = $wpdb->get_results($sql, ARRAY_A);
        return $rows ?: [];
    }

    /**
     * 将逗号/空格分隔的标签字符串转换为去重后的 slug/name 数组
     */
    private function normalize_tags_input($input) {
        if (empty($input)) return [];
        if (is_array($input)) {
            $input = implode(',', $input);
        }
        $parts = preg_split('/[,，\s]+/', $input);
        $tags = [];
        foreach ($parts as $part) {
            $name = trim($part);
            if ($name === '') continue;
            $slug = sanitize_title($name);
            if ($slug === '') continue;
            $tags[$slug] = [
                'slug' => $slug,
                'name' => $name,
            ];
        }
        return array_values($tags);
    }

    private function build_in_placeholders($values, $type = '%s') {
        if (empty($values)) return ['', []];
        $placeholders = implode(',', array_fill(0, count($values), $type));
        return [$placeholders, $values];
    }

    private function map_cpt_to_media_type($post_type) {
        if ($post_type === self::CPT_ANIME) return 'anime';
        if ($post_type === self::CPT_GALGAME) return 'galgame';
        return '';
    }

    public function map_media_type_to_label($type) {
        if ($type === 'anime') return '番剧';
        if ($type === 'galgame') return 'Galgame';
        return '';
    }

    private function map_quarter_order($quarter) {
        $map = ['spring' => 1, 'summer' => 2, 'autumn' => 3, 'winter' => 4];
        return isset($map[$quarter]) ? $map[$quarter] : 0;
    }

    /**
     * 评分档位映射（10 分制）
     */
    private function map_score_10_to_grade($score_10) {
        $s = floatval($score_10);
        if ($s > 9.8) {
            return ['label' => '殿堂级', 'desc' => '少数能长期停留在记忆中的作品。不只是完成度，而是与个人经验产生了不可替代的共鸣。'];
        }
        if ($s >= 9.2) {
            return ['label' => '神作', 'desc' => '在类型、叙事或情绪层面达到了极高水准，即便存在缺陷，也无损其整体力量。'];
        }
        if ($s >= 8.3) {
            return ['label' => '优秀', 'desc' => '明显高于平均水平，有比较鲜明亮点。'];
        }
        if ($s >= 7.4) {
            return ['label' => '佳作', 'desc' => '完成度扎实，表达清晰，在特定受众中具有一定的吸引力。'];
        }
        if ($s >= 6.9) {
            return ['label' => '还行', 'desc' => '有可取之处，但仍有明显局限，更适合有明确兴趣取向的读者/观众。'];
        }
        if ($s >= 6.0) {
            return ['label' => '一般', 'desc' => '可以理解其优点，但整体印象有限，是否投入时间取决于个人偏好。'];
        }
        if ($s >= 5.0) {
            return ['label' => '不太推荐', 'desc' => '存在结构性问题或表达失衡，除非题材强相关，否则不建议优先选择。'];
        }
        if ($s > 0) {
            return ['label' => '不推荐', 'desc' => '无法有效支撑其核心表达，或已明显偏离个人审美底线。'];
        }
        return ['label' => '', 'desc' => ''];
    }
    public function get_grade_from_score_10($score_10) {
        return $this->map_score_10_to_grade($score_10);
    }

    /**
     * 100 分制 → 10 分制（强非线性压缩，高分段稀疏）
     */
    private function map_score_100_to_10($score_100) {
        $score = intval($score_100);
        if ($score > 100) $score = 100;
        if ($score < 0) $score = 0;

        $map = [
            100 => 9.9,
            99  => 9.8,
            98  => 9.5,
            97  => 9.2,
            96  => 8.9,
            95  => 8.7,

            94  => 8.5,
            93  => 8.4,
            92  => 8.3,
            91  => 8.2,
            90  => 8.1,

            89  => 7.9,
            88  => 7.8,
            87  => 7.7,
            86  => 7.6,
            85  => 7.5,

            84  => 7.4,
            83  => 7.3,
            82  => 7.2,
            81  => 7.1,
            80  => 7.0,

            79  => 6.9,
            78  => 6.8,
            77  => 6.7,
            76  => 6.6,
            75  => 6.5,

            74  => 6.3,
            73  => 6.2,
            72  => 6.1,
            71  => 6.0,
            70  => 5.9,

            69  => 5.8,
            68  => 5.7,
            67  => 5.6,
            66  => 5.5,
            65  => 5.4,
            64  => 5.3,
            63  => 5.2,
            62  => 5.1,
            61  => 5.0,
            60  => 4.8,

            59  => 4.6,
            58  => 4.4,
            57  => 4.2,
            56  => 4.0,
            55  => 3.8,
            54  => 3.6,
            53  => 3.4,
            52  => 3.2,
            51  => 3.0,
            50  => 2.8,
        ];

        if ($score >= 50 && isset($map[$score])) {
            return (float) $map[$score];
        }

        $low = max(0, min(49, $score));
        $score_10 = round(($low / 50) * 2.5, 1);
        return (float) $score_10;
    }

    /**
     * 读取行时优先返回已有的 score_10，否则按 100 分制映射
     */
    public function resolve_score_10_from_row(array $row) {
        if (isset($row['score_10']) && $row['score_10'] !== '' && $row['score_10'] !== null) {
            return floatval($row['score_10']);
        }
        $score_100 = isset($row['score_100']) ? intval($row['score_100']) : 0;
        return $this->map_score_100_to_10($score_100);
    }

    public function resolve_finished_timestamp(array $row) {
        // 优先使用 finished_at，其次 updated_at，再次 created_at，避免“取最大值”导致排序混乱
        if (!empty($row['finished_at']) && $row['finished_at'] !== '0000-00-00 00:00:00') {
            $ts = strtotime($row['finished_at']);
            if ($ts && $ts > 0) return $ts;
        }
        if (!empty($row['updated_at'])) {
            $ts = strtotime($row['updated_at']);
            if ($ts && $ts > 0) return $ts;
        }
        if (!empty($row['created_at'])) {
            $ts = strtotime($row['created_at']);
            if ($ts && $ts > 0) return $ts;
        }
        return 0;
    }

    public function get_media_detail_url(array $row) {
        $slug = isset($row['slug']) ? sanitize_title($row['slug']) : '';
        if ($slug === '' && !empty($row['title'])) {
            $slug = sanitize_title($row['title']);
        }
        if ($slug === '' && !empty($row['id'])) {
            $slug = 'media-' . intval($row['id']);
        }
        return trailingslashit(home_url('/acgn/' . $slug));
    }

    /**
     * 为已有记录补写 score_10（不触碰 score_100）
     */
    private function backfill_score_10_if_missing() {
        global $wpdb;
        $table = $this->get_items_table_name();
        if (empty($table)) return;
        $rows = $wpdb->get_results("SELECT id, score_100, score_10 FROM {$table} WHERE score_10 IS NULL", ARRAY_A);
        if (empty($rows)) return;
        foreach ($rows as $row) {
            $score_10 = $this->resolve_score_10_from_row($row);
            $wpdb->update($table, ['score_10' => $score_10], ['id' => intval($row['id'])], ['%f'], ['%d']);
        }
    }

    private function backfill_slug_if_missing() {
        global $wpdb;
        $table = $this->get_items_table_name();
        if (empty($table)) return;
        $rows = $wpdb->get_results("SELECT id, title, slug FROM {$table} WHERE slug IS NULL OR slug = ''", ARRAY_A);
        if (empty($rows)) return;
        foreach ($rows as $row) {
            $slug = $this->generate_slug($row['title'] ?: ('media-' . intval($row['id'])), intval($row['id']));
            if ($slug === '') continue;
            $wpdb->update($table, ['slug' => $slug], ['id' => intval($row['id'])], ['%s'], ['%d']);
        }
    }

    /**
     * 通用媒体查询：读取独立表，支持状态/类型/是否首页展示/搜索/分页
     */
    public function media_query($args = []) {
        global $wpdb;
        $table = $this->get_items_table_name();

        $defaults = [
            'types'       => [], // ['anime', 'galgame']
            'status'      => [], // ['want','watching','watched']
            'show_on_home'=> null, // true/false/null
            'show_on_home_feed' => null, // true/false/null
            'order_by'    => 'time', // updated_at|created_at|score|time|finished_at
            'order'       => 'DESC',
            'limit'       => 0,
            'offset'      => 0,
            'search'      => '',
        ];
        $args = wp_parse_args($args, $defaults);

        $where  = [];
        $params = [];

        if (!empty($args['types'])) {
            list($ph, $vals) = $this->build_in_placeholders($args['types'], '%s');
            $where[]  = "type IN ({$ph})";
            $params   = array_merge($params, $vals);
        }

        if (!empty($args['status'])) {
            list($ph, $vals) = $this->build_in_placeholders($args['status'], '%s');
            $where[]  = "status IN ({$ph})";
            $params   = array_merge($params, $vals);
        }

        if ($args['show_on_home'] !== null) {
            $where[] = 'show_on_home = %d';
            $params[] = $args['show_on_home'] ? 1 : 0;
        }

        if ($args['show_on_home_feed'] !== null) {
            $where[] = 'show_on_home_feed = %d';
            $params[] = $args['show_on_home_feed'] ? 1 : 0;
        }

        if (!empty($args['search'])) {
            $kw = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = "(title LIKE %s OR review LIKE %s)";
            $params[] = $kw;
            $params[] = $kw;
        }

        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $order_sql_parts = [];
        if ($args['order_by'] === 'score') {
            $order_sql_parts[] = "score_10 {$order}";
            $order_sql_parts[] = "score_100 {$order}";
        } elseif ($args['order_by'] === 'created_at') {
            $order_sql_parts[] = "created_at {$order}";
        } elseif ($args['order_by'] === 'finished_at') {
            $order_sql_parts[] = "COALESCE(finished_at, created_at, updated_at) {$order}";
        } elseif ($args['order_by'] === 'time') {
            // 按年份 + 季度顺序排序（最新在前）
            $order_sql_parts[] = "year DESC";
            $order_sql_parts[] = "FIELD(quarter,'spring','summer','autumn','winter') DESC";
        }
        $order_sql_parts[] = "updated_at {$order}";
        $order_by_sql = implode(', ', $order_sql_parts);

        $sql = "SELECT * FROM {$table}";
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= " ORDER BY {$order_by_sql}";

        if (!empty($args['limit']) && intval($args['limit']) > 0) {
            $sql .= ' LIMIT %d OFFSET %d';
            $params[] = intval($args['limit']);
            $params[] = intval($args['offset']);
        }

        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }

        $rows = $wpdb->get_results($sql, ARRAY_A);
        if (!$rows) return [];

        foreach ($rows as &$row) {
            // 优先用数据库中的 score_10；若缺失，则一次性回填并写回数据库，避免每次映射
            if (!isset($row['score_10']) || $row['score_10'] === '' || $row['score_10'] === null) {
                $row['score_10'] = $this->map_score_100_to_10(isset($row['score_100']) ? $row['score_100'] : 0);
                if (!empty($row['id'])) {
                    $this->media_item_update((int)$row['id'], ['score_10' => $row['score_10']]);
                }
            } else {
                $row['score_10'] = floatval($row['score_10']);
            }
            $row['qorder'] = $this->map_quarter_order(isset($row['quarter']) ? $row['quarter'] : '');
        }
        unset($row);

        return $rows;
    }

    /**
     * 统计数量（用于分页）
     */
    public function media_query_count($args = []) {
        global $wpdb;
        $table = $this->get_items_table_name();
        $defaults = [
            'types'       => [],
            'status'      => [],
            'show_on_home'=> null,
            'show_on_home_feed' => null,
            'search'      => '',
        ];
        $args = wp_parse_args($args, $defaults);

        $where  = [];
        $params = [];

        if (!empty($args['types'])) {
            list($ph, $vals) = $this->build_in_placeholders($args['types'], '%s');
            $where[]  = "type IN ({$ph})";
            $params   = array_merge($params, $vals);
        }

        if (!empty($args['status'])) {
            list($ph, $vals) = $this->build_in_placeholders($args['status'], '%s');
            $where[]  = "status IN ({$ph})";
            $params   = array_merge($params, $vals);
        }

        if ($args['show_on_home'] !== null) {
            $where[] = 'show_on_home = %d';
            $params[] = $args['show_on_home'] ? 1 : 0;
        }

        if ($args['show_on_home_feed'] !== null) {
            $where[] = 'show_on_home_feed = %d';
            $params[] = $args['show_on_home_feed'] ? 1 : 0;
        }

        if (!empty($args['search'])) {
            $kw = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = "(title LIKE %s OR review LIKE %s)";
            $params[] = $kw;
            $params[] = $kw;
        }

        $sql = "SELECT COUNT(*) FROM {$table}";
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }

        return intval($wpdb->get_var($sql));
    }

    // 判断是否外部链接（非本站）
    private function is_external_url($url) {
        if (empty($url)) return false;
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) return false;
        $site_host = parse_url(home_url(), PHP_URL_HOST);
        return $host !== $site_host;
    }

    // 侧载外链图片到媒体库并返回附件ID（失败返回0）
    private function sideload_cover_attachment($post_id, $url) {
        if (empty($url) || !$this->is_external_url($url)) return 0;
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $att_id = @media_sideload_image($url, $post_id, null, 'id');
        if (is_wp_error($att_id)) {
            return 0;
        }
        return intval($att_id);
    }

    // 侧载外链图片（无绑定 post），用于新表条目
    private function sideload_cover_attachment_from_url($url) {
        if (empty($url) || !$this->is_external_url($url)) return 0;
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $att_id = @media_sideload_image($url, 0, null, 'id');
        if (is_wp_error($att_id)) {
            return 0;
        }
        return intval($att_id);
    }

    // 根据年份与季度生成「{year}年{季}」
    private function build_season_text($year, $quarter) {
        if (empty($year) || empty($quarter) || !isset($this->quarters[$quarter])) return '';
        return sprintf('%d年%s', intval($year), $this->quarters[$quarter]);
    }

    // 后台工具页注册
    public function register_tools_page() {

    add_menu_page(
        'Galgame/番剧',
        'Galgame/番剧',
        'manage_options',
        'apex-media-dashboard',
        [$this, 'render_media_dashboard'],
        'dashicons-playlist-audio',
        57
    );

    add_submenu_page(
        'apex-media-dashboard',
        '番剧管理（新表）',
        '番剧管理（新表）',
        'manage_options',
        'apex-media-anime',
        function () {
            $this->render_media_admin_page('anime');
        }
    );

    add_submenu_page(
        'apex-media-dashboard',
        'Galgame 管理（新表）',
        'Galgame 管理（新表）',
        'manage_options',
        'apex-media-gal',
        function () {
            $this->render_media_admin_page('galgame');
        }
    );

    add_submenu_page(
        'apex-media-dashboard',
        '作品详情页管理',
        '作品详情页管理',
        'manage_options',
        'apex-media-home-feed',
        [$this, 'render_media_home_feed_page']
    );

    add_submenu_page(
        'apex-media-dashboard',
        '数据/封面工具',
        '数据/封面工具',
        'manage_options',
        'apex-cover-migrate',
        [$this, 'render_tools_page']
    );

    // 👇 干掉 WP 自动生成的同名子菜单
    remove_submenu_page(
        'apex-media-dashboard',
        'apex-media-dashboard'
    );
}

    public function render_tools_page() {
        if (!current_user_can('manage_options')) {
            wp_die('无权限');
        }
        $migrated = isset($_GET['migrated']) ? intval($_GET['migrated']) : 0;
        $skipped  = isset($_GET['skipped']) ? intval($_GET['skipped']) : 0;
        $failed   = isset($_GET['failed']) ? intval($_GET['failed']) : 0;
        $total    = isset($_GET['total']) ? intval($_GET['total']) : 0;
        $dry      = isset($_GET['dry']) ? intval($_GET['dry']) === 1 : false;

        $media_total    = isset($_GET['media_total']) ? intval($_GET['media_total']) : 0;
        $media_inserted = isset($_GET['media_inserted']) ? intval($_GET['media_inserted']) : 0;
        $media_updated  = isset($_GET['media_updated']) ? intval($_GET['media_updated']) : 0;
        $media_failed   = isset($_GET['media_failed']) ? intval($_GET['media_failed']) : 0;
        $media_dry      = isset($_GET['media_dry']) ? intval($_GET['media_dry']) === 1 : false;
        ?>
        <div class="wrap">
            <h1>番剧 / gal 工具</h1>

            <h2>封面迁移工具（外链 → 媒体库附件 → 由插件上云）</h2>
            <p>说明：扫描番剧与 galgame 所有条目，将外部封面图侧载为媒体库附件。你的 OSS 插件会自动把附件转存到 OSS 并生成外链。</p>
            <?php if ($total > 0): ?>
                <div class="notice notice-success"><p>
                    <?php echo esc_html(sprintf('封面迁移结果：总计 %d，已处理 %d，跳过 %d，失败 %d%s', $total, $migrated, $skipped, $failed, $dry ? '（Dry-run）' : '')); ?>
                </p></div>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('apex_migrate_covers_nonce', 'apex_migrate_covers_nonce'); ?>
                <input type="hidden" name="action" value="apex_migrate_covers">
                <p>
                    <label><input type="checkbox" name="dry_run" value="1"> 仅统计（Dry-run），不实际迁移</label>
                </p>
                <p><button class="button button-primary" type="submit">开始封面迁移</button></p>
            </form>

            <hr>

            <h2>数据迁移到独立数据库</h2>
            <p>说明：从现有番剧 / gal CPT 中读取数据，写入独立的 apex_media_* 表，仅复制，不删除旧数据。</p>
            <?php if ($media_total > 0): ?>
                <div class="notice notice-success"><p>
                    <?php echo esc_html(sprintf('数据迁移结果：总计 %d，新增 %d，更新 %d，失败 %d%s', $media_total, $media_inserted, $media_updated, $media_failed, $media_dry ? '（Dry-run）' : '')); ?>
                </p></div>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('apex_migrate_media_data_nonce', 'apex_migrate_media_data_nonce'); ?>
                <input type="hidden" name="action" value="apex_migrate_media_data">
                <p>
                    <label><input type="checkbox" name="dry_run_media" value="1"> 仅统计（Dry-run），不实际写入新表</label>
                </p>
                <p><button class="button button-primary" type="submit">开始数据迁移</button></p>
            </form>
        </div>
        <?php
    }

    private function render_media_admin_page($media_type) {
        if (!current_user_can('manage_options')) {
            wp_die('无权限');
        }
        $type_label = $this->map_media_type_to_label($media_type);
        if (!$type_label) {
            wp_die('非法类型');
        }

        $editing_id = isset($_GET['media_id']) ? intval($_GET['media_id']) : 0;
        $editing_row = $editing_id > 0 ? $this->media_item_get_by_id($editing_id) : null;
        if ($editing_row && $editing_row['type'] !== $media_type) {
            $editing_row = null;
            $editing_id = 0;
        }
        $editing_tags = $editing_id > 0 ? $this->media_item_get_tags($editing_id) : [];
        $editing_tags_input = '';
        if (!empty($editing_tags)) {
            $editing_tags_input = implode(', ', array_map(function($t){ return $t['name']; }, $editing_tags));
        }
        $editing_finished_date = '';
        if (!empty($editing_row['finished_at']) && $editing_row['finished_at'] !== '0000-00-00 00:00:00') {
            $editing_finished_date = substr($editing_row['finished_at'], 0, 10);
        }
        $popular_tags = $this->media_tags_popular(30);

        $per_page = 20;
        $paged = max(1, isset($_GET['paged']) ? intval($_GET['paged']) : 1);
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $offset = ($paged - 1) * $per_page;

        $items = $this->media_query([
            'types' => [$media_type],
            'order_by' => 'created_at',
            'order' => 'DESC',
            'limit' => $per_page,
            'offset' => $offset,
            'search' => $search,
        ]);
        $item_tags_map = [];
        if (!empty($items)) {
            $item_ids = array_map(function($r){ return intval($r['id']); }, $items);
            $item_tags_map = $this->media_items_get_tags_map($item_ids);
        }
        $total_items = $this->media_query_count([
            'types' => [$media_type],
            'search' => $search,
        ]);
        $total_pages = $per_page > 0 ? ceil($total_items / $per_page) : 1;

        $saved = isset($_GET['saved']) ? intval($_GET['saved']) : 0;

        ?>
        <div class="wrap">
            <h1><?php echo esc_html($type_label); ?>管理（独立数据库）</h1>
            <?php if ($saved === 1): ?>
                <div class="notice notice-success"><p>已保存。</p></div>
            <?php elseif ($saved === 0 && isset($_GET['saved'])): ?>
                <div class="notice notice-error"><p>保存失败，请重试。</p></div>
            <?php endif; ?>

            <div class="apex-admin-grid">
                <div class="apex-admin-form card">
                    <div class="apex-admin-form-header">
                        <h2><?php echo $editing_row ? '编辑条目' : '新增条目'; ?></h2>
                        <?php if ($editing_row): ?>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="apex-inline-form" onsubmit="return confirm('确定删除此条目吗？此操作不可恢复');">
                                <?php wp_nonce_field('apex_media_item_nonce', 'apex_media_item_nonce'); ?>
                                <input type="hidden" name="action" value="apex_media_delete_item">
                                <input type="hidden" name="media_type" value="<?php echo esc_attr($media_type); ?>">
                                <input type="hidden" name="media_id" value="<?php echo intval($editing_row['id']); ?>">
                                <button type="submit" class="button button-secondary" style="color:#d63638;border-color:#d63638;">删除</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="apex-admin-edit-form">
                        <?php wp_nonce_field('apex_media_item_nonce', 'apex_media_item_nonce'); ?>
                        <input type="hidden" name="action" value="apex_media_save_item">
                        <input type="hidden" name="media_type" value="<?php echo esc_attr($media_type); ?>">
                        <input type="hidden" name="media_id" value="<?php echo $editing_row ? intval($editing_row['id']) : 0; ?>">

                        <table class="form-table">
                            <tr>
                                <th><label>标题</label></th>
                                <td><input type="text" name="media_title" class="regular-text" required value="<?php echo esc_attr($editing_row['title'] ?? ''); ?>"></td>
                            </tr>
                            <tr>
                                <th><label>详情页 slug</label></th>
                                <td><input type="text" name="media_slug" class="regular-text" value="<?php echo esc_attr($editing_row['slug'] ?? ''); ?>" placeholder="留空将自动生成"></td>
                            </tr>
                            <tr>
                                <th><label>状态</label></th>
                                <td>
                                    <select name="media_status">
                                        <option value="">未设置</option>
                                        <?php foreach ($this->status_terms as $slug => $name): ?>
                                            <option value="<?php echo esc_attr($slug); ?>" <?php selected($editing_row['status'] ?? '', $slug); ?>><?php echo esc_html($name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>评分（0-10，保留1位小数）</label></th>
                                <td><input type="number" name="media_score_10" min="0" max="10" step="0.1" value="<?php echo isset($editing_row) ? esc_attr(number_format($this->resolve_score_10_from_row($editing_row), 1)) : ''; ?>" placeholder="如 9.1"></td>
                            </tr>
                            <tr>
                                <th><label>BGM 链接</label></th>
                                <td><input type="url" name="media_bgm" class="regular-text" value="<?php echo esc_attr($editing_row['bgm_url'] ?? ''); ?>" placeholder="https://bgm.tv/subject/... 或其他跳转"></td>
                            </tr>
                            <tr>
                                <th><label>封面 URL</label></th>
                                <td><input type="url" name="media_cover" class="regular-text" value="<?php echo esc_attr($editing_row['cover_source_url'] ?? ''); ?>" placeholder="https://example.com/cover.jpg"></td>
                            </tr>
                            <tr>
                                <th><label>年份</label></th>
                                <td><input type="number" name="media_year" min="1970" max="2100" step="1" value="<?php echo isset($editing_row['year']) ? intval($editing_row['year']) : ''; ?>"></td>
                            </tr>
                            <tr>
                                <th><label>季度 / 发售季</label></th>
                                <td>
                                    <select name="media_quarter">
                                        <option value="">未设置</option>
                                        <?php foreach ($this->quarters as $qk => $qv): ?>
                                            <option value="<?php echo esc_attr($qk); ?>" <?php selected($editing_row['quarter'] ?? '', $qk); ?>><?php echo esc_html($qv); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description">只需填年份 + 季节，可留空具体日期。</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>完成时间</label></th>
                                <td>
                                    <input type="date" name="media_finished_at" value="<?php echo esc_attr($editing_finished_date); ?>">
                                    <p class="description">观看 / 游玩完成日期，可选填。</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>季节文本</label></th>
                                <td><input type="text" name="media_season_text" class="regular-text" value="<?php echo esc_attr($editing_row['season_text'] ?? ''); ?>" placeholder="若留空将按 年+季 自动生成"></td>
                            </tr>
                            <tr>
                                <th><label>标签</label></th>
                                <td>
                                    <input type="text" name="media_tags" class="regular-text" value="<?php echo esc_attr($editing_tags_input); ?>" placeholder="用逗号分隔，如：治愈,校园">
                                    <p class="description">可输入多个标签，保存后用于筛选/展示。</p>
                                    <?php if (!empty($popular_tags)): ?>
                                        <div class="apex-tag-suggestions">
                                            <span>常用标签：</span>
                                            <?php foreach ($popular_tags as $tag): ?>
                                                <button type="button" class="apex-tag-chip" data-tag="<?php echo esc_attr($tag['name']); ?>"><?php echo esc_html($tag['name']); ?></button>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><label>侧边栏展示</label></th>
                                <td>
                                    <label><input type="checkbox" name="media_show_on_home" value="1" <?php checked(intval($editing_row['show_on_home'] ?? 0), 1); ?>> 在侧边栏精选显示</label>
                                    <div style="margin-top:6px;">
                                        <label>侧边栏排序（可选，数值越大越靠前）：</label>
                                        <input type="number" name="media_home_rank" step="1" value="<?php echo isset($editing_row['home_rank']) ? intval($editing_row['home_rank']) : ''; ?>" style="width:120px;">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th><label>首页混排</label></th>
                                <td>
                                    <label><input type="checkbox" name="media_show_on_home_feed" value="1" <?php checked(intval($editing_row['show_on_home_feed'] ?? 0), 1); ?>> 参与首页文章流</label>
                                    <p class="description">按完成时间排序，与文章/说说混排。</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>评价</label></th>
                                <td><textarea name="media_review" rows="6" class="large-text"><?php echo esc_textarea($editing_row['review'] ?? ''); ?></textarea></td>
                            </tr>
                        </table>

                        <p><button type="submit" class="button button-primary">保存</button></p>
                    </form>
                </div>

                <div class="apex-admin-list card">
                    <div class="apex-admin-list-header">
                        <h2><?php echo esc_html($type_label); ?>列表</h2>
                        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="apex-inline-form">
                            <input type="hidden" name="page" value="<?php echo $media_type === 'anime' ? 'apex-media-anime' : 'apex-media-gal'; ?>">
                            <input type="search" name="s" placeholder="搜索标题/评价" value="<?php echo esc_attr($search); ?>">
                            <button class="button">搜索</button>
                        </form>
                    </div>
                    <table class="widefat striped apex-admin-table">
                        <thead>
                            <tr>
                                <th width="8%">ID</th>
                                <th>标题</th>
                                <th width="16%">标签</th>
                                <th width="10%">状态</th>
                                <th width="10%">评分</th>
                                <th width="12%">年份/季</th>
                                <th width="12%">完成时间</th>
                                <th width="12%">更新时间</th>
                                <th width="10%">主页/混排</th>
                                <th width="18%">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($items)): foreach ($items as $row): ?>
                            <tr>
                                <td><?php echo intval($row['id']); ?></td>
                                <td><?php echo esc_html($row['title']); ?></td>
                                <td>
                                    <?php
                                    $tags = isset($item_tags_map[$row['id']]) ? $item_tags_map[$row['id']] : [];
                                    if (!empty($tags)) {
                                        foreach ($tags as $tg) {
                                            echo '<span class="apex-tag-badge">' . esc_html($tg['name']) . '</span> ';
                                        }
                                    } else {
                                        echo '<span class="apex-tag-empty">-</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc_html($this->get_status_label($row['status'] ?? '')); ?></td>
                                <td>
                                    <?php
                                    $s10  = $this->resolve_score_10_from_row($row);
                                    $s100 = isset($row['score_100']) ? intval($row['score_100']) : null;
                                    echo $s10 > 0 ? esc_html(number_format($s10, 1)) : '-';
                                    if (!is_null($s100)) {
                                        echo '<br><small>' . esc_html($s100 . '/100') . '</small>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc_html(trim(($row['year'] ?? '') . ' ' . ($this->quarters[$row['quarter']] ?? ''))); ?></td>
                                <td><?php echo !empty($row['finished_at']) && $row['finished_at'] !== '0000-00-00 00:00:00' ? esc_html(substr($row['finished_at'],0,10)) : '-'; ?></td>
                                <td><?php echo esc_html($row['updated_at']); ?></td>
                                <td>
                                    <div><?php echo intval($row['show_on_home']) === 1 ? '精选：是' : '精选：否'; ?></div>
                                    <div><?php echo intval($row['show_on_home_feed'] ?? 0) === 1 ? '混排：是' : '混排：否'; ?></div>
                                </td>
                                <td class="apex-actions">
                                    <a class="button" href="<?php echo esc_url(add_query_arg(['page' => $media_type === 'anime' ? 'apex-media-anime' : 'apex-media-gal', 'media_id' => intval($row['id'])], admin_url('admin.php'))); ?>">编辑</a>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('确定删除此条目吗？此操作不可恢复');">
                                        <?php wp_nonce_field('apex_media_item_nonce', 'apex_media_item_nonce'); ?>
                                        <input type="hidden" name="action" value="apex_media_delete_item">
                                        <input type="hidden" name="media_type" value="<?php echo esc_attr($media_type); ?>">
                                        <input type="hidden" name="media_id" value="<?php echo intval($row['id']); ?>">
                                        <button type="submit" class="button button-link-delete">删除</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="10">暂无数据</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <?php if ($total_pages > 1): ?>
                        <div class="tablenav"><div class="tablenav-pages">
                            <?php
                            $base_url = add_query_arg([
                                'page' => $media_type === 'anime' ? 'apex-media-anime' : 'apex-media-gal',
                                's' => $search,
                            ], admin_url('admin.php'));
                            for ($p = 1; $p <= $total_pages; $p++) {
                                $class = $p === $paged ? 'page-numbers current' : 'page-numbers';
                                $url = add_query_arg('paged', $p, $base_url);
                                echo '<a class="' . esc_attr($class) . '" href="' . esc_url($url) . '">' . intval($p) . '</a> ';
                            }
                            ?>
                        </div></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <style>
            .apex-admin-grid { display: grid; grid-template-columns: 1fr 1.6fr; gap: 16px; align-items: flex-start; }
            @media (max-width: 1200px) { .apex-admin-grid { grid-template-columns: 1fr; } }
            .apex-admin-form.card, .apex-admin-list.card { padding: 16px 20px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border:1px solid #e5e5e5; }
            .apex-admin-list.card { width: 100%; max-width: none; }
            .apex-admin-form-header { display:flex; justify-content: space-between; align-items:center; gap:12px; margin-bottom:12px; }
            .apex-inline-form { display:flex; gap:8px; align-items:center; margin:0; }
            .apex-inline-form input[type="search"] { min-width: 200px; }
            .apex-admin-edit-form table th { width: 140px; }
            .apex-admin-list-header { display:flex; justify-content: space-between; align-items:center; margin-bottom:12px; }
            .apex-actions { display:flex; gap:8px; align-items:center; flex-wrap: wrap; }
            .button-link-delete { color:#d63638; border-color:#d63638; background:#fff; }
            .button-link-delete:hover { color:#a60000; }
            .apex-admin-form h2, .apex-admin-list h2 { margin:0; }
            .apex-admin-table { table-layout: auto; width:100%; min-width: 1180px; }
            .apex-admin-table th, .apex-admin-table td { white-space: normal; word-break: break-word; }
            .apex-admin-table th:nth-child(1), .apex-admin-table td:nth-child(1) { width: 70px; }
            .apex-admin-table th:nth-child(2), .apex-admin-table td:nth-child(2) { min-width: 220px; }
            .apex-admin-table th:nth-child(3), .apex-admin-table td:nth-child(3) { min-width: 200px; }
            .apex-admin-table th:nth-child(4), .apex-admin-table td:nth-child(4) { width: 90px; }
            .apex-admin-table th:nth-child(5), .apex-admin-table td:nth-child(5) { width: 80px; }
            .apex-admin-table th:nth-child(6), .apex-admin-table td:nth-child(6) { width: 120px; }
            .apex-admin-table th:nth-child(7), .apex-admin-table td:nth-child(7) { width: 120px; }
            .apex-admin-table th:nth-child(8), .apex-admin-table td:nth-child(8) { width: 160px; }
            .apex-admin-table th:nth-child(9), .apex-admin-table td:nth-child(9) { width: 90px; }
            .apex-admin-table th:nth-child(10), .apex-admin-table td:nth-child(10) { width: 180px; }
            .apex-admin-list { overflow-x: auto; }
            .apex-tag-suggestions { display:flex; flex-wrap: wrap; gap:8px; align-items:center; margin-top:6px; }
            .apex-tag-chip { border:1px solid #ccd0d4; background:#f6f7f7; padding:3px 8px; border-radius:12px; cursor:pointer; transition:all .15s; }
            .apex-tag-chip:hover { border-color:#2271b1; color:#2271b1; background:#fff; }
            .apex-tag-badge { display:inline-block; background:#eef6ff; color:#1d4ed8; border:1px solid #d0e4ff; padding:2px 8px; border-radius:12px; font-size:12px; margin-right:4px; }
            .apex-tag-empty { color:#888; }
        </style>
        <script>
            (function(){
                const form = document.querySelector('.apex-admin-edit-form');
                if (!form) return;
                const input = form.querySelector('input[name="media_tags"]');
                if (!input) return;
                const chips = form.querySelectorAll('.apex-tag-chip');
                chips.forEach(function(chip){
                    chip.addEventListener('click', function(){
                        const tag = this.dataset.tag || '';
                        if (!tag) return;
                        const current = input.value ? input.value.split(/[,，]/).map(function(t){ return t.trim(); }).filter(Boolean) : [];
                        if (!current.includes(tag)) {
                            current.push(tag);
                        }
                        input.value = current.join(', ');
                    });
                });
            })();
        </script>
        <?php
    }

    public function render_media_home_feed_page() {
        if (!current_user_can('manage_options')) {
            wp_die('无权限');
        }
        $per_page = 20;
        $paged = max(1, isset($_GET['paged']) ? intval($_GET['paged']) : 1);
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $offset = ($paged - 1) * $per_page;

        if (!$this->is_items_table_schema_valid()) {
            echo '<div class="notice notice-error"><p>媒体表结构缺失，请访问前台页面以触发升级，或在“固定链接”页保存以刷新规则后重试。</p></div>';
            return;
        }

        $items = $this->media_query([
            'show_on_home_feed' => true,
            'order_by' => 'finished_at',
            'order' => 'DESC',
            'limit' => $per_page,
            'offset' => $offset,
            'search' => $search,
        ]);
        $total = $this->media_query_count([
            'show_on_home_feed' => true,
            'search' => $search,
        ]);
        $total_pages = $per_page > 0 ? ceil($total / $per_page) : 1;
        $saved = isset($_GET['saved']) ? intval($_GET['saved']) : null;
        $mode_saved = isset($_GET['mode_saved']) ? intval($_GET['mode_saved']) : null;
        $collapse_mode = get_option('apex_media_collapse_mode', 'adjacent');
        ?>
        <div class="wrap">
            <h1>作品详情页管理</h1>
            <?php if ($saved === 1): ?>
                <div class="notice notice-success"><p>已保存。</p></div>
            <?php elseif ($saved === 0): ?>
                <div class="notice notice-error"><p>保存失败，请重试。</p></div>
            <?php endif; ?>
            <?php if ($mode_saved === 1): ?>
                <div class="notice notice-success"><p>折叠模式已更新。</p></div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="apex-inline-form" style="margin:12px 0 12px 0; gap:12px; align-items:center;">
                <?php wp_nonce_field('apex_media_collapse_mode_nonce', 'apex_media_collapse_mode_nonce'); ?>
                <input type="hidden" name="action" value="apex_media_home_feed_mode">
                <label><input type="radio" name="apex_media_collapse_mode" value="adjacent" <?php checked($collapse_mode, 'adjacent'); ?>> 保留现有逻辑（仅折叠文章间连续媒体）</label>
                <label><input type="radio" name="apex_media_collapse_mode" value="all" <?php checked($collapse_mode, 'all'); ?>> 全部折叠：默认只显首条，其余折叠</label>
                <button class="button button-primary" type="submit">保存折叠模式</button>
            </form>

            <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="apex-inline-form" style="margin:0 0 16px 0;">
                <input type="hidden" name="page" value="apex-media-home-feed">
                <input type="search" name="s" placeholder="搜索标题/评价" value="<?php echo esc_attr($search); ?>">
                <button class="button">搜索</button>
            </form>

            <table class="widefat striped apex-admin-table">
                <thead>
                    <tr>
                        <th width="6%">ID</th>
                        <th width="22%">标题 / 类型</th>
                        <th width="12%">slug</th>
                        <th width="12%">完成时间</th>
                        <th width="10%">评分</th>
                        <th>评价（与主表共享）</th>
                        <th width="10%">首页混排</th>
                        <th width="14%">操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($items)): foreach ($items as $row):
                    $score_10 = $this->resolve_score_10_from_row($row);
                    $detail_url = $this->get_media_detail_url($row);
                    $finished_date = (!empty($row['finished_at']) && $row['finished_at'] !== '0000-00-00 00:00:00') ? substr($row['finished_at'], 0, 10) : '';
                    $type_label = $this->map_media_type_to_label($row['type']);
                    ?>
                    <?php $form_id = 'apex-home-feed-' . intval($row['id']); ?>
                    <tr>
                        <td><?php echo intval($row['id']); ?></td>
                        <td>
                            <strong><?php echo esc_html($row['title']); ?></strong><br>
                            <span class="description"><?php echo esc_html($type_label ?: $row['type']); ?></span>
                        </td>
                        <td>
                            <input type="text" name="media_slug" form="<?php echo esc_attr($form_id); ?>" value="<?php echo esc_attr($row['slug'] ?? ''); ?>" placeholder="自动生成" class="regular-text" style="width:100%; max-width:180px;">
                        </td>
                        <td>
                            <input type="date" name="media_finished_at" form="<?php echo esc_attr($form_id); ?>" value="<?php echo esc_attr($finished_date); ?>">
                        </td>
                        <td>
                            <?php echo $score_10 > 0 ? esc_html(number_format($score_10, 1)) : '-'; ?>
                        </td>
                        <td>
                            <textarea name="media_review" form="<?php echo esc_attr($form_id); ?>" rows="3" style="width:100%;min-width:240px;"><?php echo esc_textarea($row['review'] ?? ''); ?></textarea>
                        </td>
                        <td>
                            <label><input type="checkbox" name="media_show_on_home_feed" form="<?php echo esc_attr($form_id); ?>" value="1" <?php checked(intval($row['show_on_home_feed'] ?? 0), 1); ?>> 显示</label>
                        </td>
                        <td class="apex-actions">
                            <form id="<?php echo esc_attr($form_id); ?>" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <?php wp_nonce_field('apex_media_home_feed_nonce', 'apex_media_home_feed_nonce'); ?>
                                <input type="hidden" name="action" value="apex_media_home_feed_save">
                                <input type="hidden" name="media_id" value="<?php echo intval($row['id']); ?>">
                                <input type="hidden" name="paged" value="<?php echo intval($paged); ?>">
                                <input type="hidden" name="s" value="<?php echo esc_attr($search); ?>">
                            </form>
                            <button type="submit" form="<?php echo esc_attr($form_id); ?>" class="button button-primary">保存</button>
                            <a class="button" href="<?php echo esc_url($detail_url); ?>" target="_blank">查看详情</a>
                            <a class="button" href="<?php echo esc_url(add_query_arg(['page' => $row['type'] === 'galgame' ? 'apex-media-gal' : 'apex-media-anime', 'media_id' => intval($row['id'])], admin_url('admin.php'))); ?>">完整编辑</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="8">暂无已选择主页展示的作品。</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <?php if ($total_pages > 1): ?>
                <div class="tablenav"><div class="tablenav-pages">
                    <?php
                    $base_url = add_query_arg([
                        'page' => 'apex-media-home-feed',
                        's' => $search,
                    ], admin_url('admin.php'));
                    for ($p = 1; $p <= $total_pages; $p++) {
                        $class = $p === $paged ? 'page-numbers current' : 'page-numbers';
                        $url = add_query_arg('paged', $p, $base_url);
                        echo '<a class="' . esc_attr($class) . '" href="' . esc_url($url) . '">' . intval($p) . '</a> ';
                    }
                    ?>
                </div></div>
            <?php endif; ?>
        </div>
        <style>
            .apex-admin-table textarea { font-family: inherit; }
        </style>
        <?php
    }

    public function handle_save_home_feed_mode() {
        if (!current_user_can('manage_options')) {
            wp_die('无权限');
        }
        if (!isset($_POST['apex_media_collapse_mode_nonce']) || !wp_verify_nonce($_POST['apex_media_collapse_mode_nonce'], 'apex_media_collapse_mode_nonce')) {
            wp_die('非法请求');
        }
        $mode = isset($_POST['apex_media_collapse_mode']) ? sanitize_text_field($_POST['apex_media_collapse_mode']) : 'adjacent';
        $allowed = ['adjacent','all'];
        if (!in_array($mode, $allowed, true)) {
            $mode = 'adjacent';
        }
        update_option('apex_media_collapse_mode', $mode);
        $redirect = add_query_arg([
            'page' => 'apex-media-home-feed',
            'mode_saved' => 1,
        ], admin_url('admin.php'));
        wp_redirect($redirect);
        exit;
    }

    public function handle_save_home_feed() {
        if (!current_user_can('manage_options')) {
            wp_die('无权限');
        }
        if (!isset($_POST['apex_media_home_feed_nonce']) || !wp_verify_nonce($_POST['apex_media_home_feed_nonce'], 'apex_media_home_feed_nonce')) {
            wp_die('非法请求');
        }
        $id = isset($_POST['media_id']) ? intval($_POST['media_id']) : 0;
        $row = $this->media_item_get_by_id($id);
        if (!$row) {
            wp_redirect(add_query_arg(['page' => 'apex-media-home-feed', 'saved' => 0], admin_url('admin.php')));
            exit;
        }

        $slug = isset($_POST['media_slug']) ? sanitize_title(wp_unslash($_POST['media_slug'])) : '';
        $review = isset($_POST['media_review']) ? wp_kses_post(wp_unslash($_POST['media_review'])) : '';
        $finished_raw = isset($_POST['media_finished_at']) ? sanitize_text_field(wp_unslash($_POST['media_finished_at'])) : '';
        $finished_at = '';
        if (!empty($finished_raw)) {
            $dt = date_create($finished_raw);
            if ($dt) {
                $finished_at = $dt->format('Y-m-d 00:00:00');
            }
        }
        $show_on_home_feed = isset($_POST['media_show_on_home_feed']) ? 1 : 0;

        if ($slug === '' && !empty($row['title'])) {
            $slug = $this->generate_slug($row['title'], $id);
        }

        $data = [
            'review' => $review,
            'show_on_home_feed' => $show_on_home_feed,
        ];
        if (!empty($slug)) {
            $data['slug'] = $slug;
        }
        if (!empty($finished_at)) {
            $data['finished_at'] = $finished_at;
        }

        $ok = $this->media_item_update($id, $data);

        $redirect = add_query_arg([
            'page'  => 'apex-media-home-feed',
            'saved' => $ok ? 1 : 0,
            'paged' => isset($_POST['paged']) ? intval($_POST['paged']) : 1,
            's'     => isset($_POST['s']) ? sanitize_text_field($_POST['s']) : '',
        ], admin_url('admin.php'));
        wp_redirect($redirect);
        exit;
    }

    public function handle_migrate_covers() {
        if (!current_user_can('manage_options')) {
            wp_die('无权限');
        }
        if (!isset($_POST['apex_migrate_covers_nonce']) || !wp_verify_nonce($_POST['apex_migrate_covers_nonce'], 'apex_migrate_covers_nonce')) {
            wp_die('非法请求');
        }
        $dry = isset($_POST['dry_run']) && $_POST['dry_run'] == '1';

        $post_types = [self::CPT_ANIME, self::CPT_GALGAME];
        $migrated = 0;
        $skipped  = 0;
        $failed   = 0;
        $total    = 0;

        foreach ($post_types as $pt) {
            $q = new WP_Query([
                'post_type'      => $pt,
                'posts_per_page' => -1,
                'post_status'    => 'any',
                'fields'         => 'ids'
            ]);
            foreach ($q->posts as $pid) {
                $total++;
                $att_id = intval(get_post_meta($pid, '_apex_cover_attachment_id', true));
                $src    = get_post_meta($pid, '_apex_cover_url', true);
                if ($att_id > 0) { $skipped++; continue; }
                if (!$this->is_external_url($src)) { $skipped++; continue; }
                if ($dry) { $migrated++; continue; }
                $new_id = $this->sideload_cover_attachment($pid, $src);
                if ($new_id > 0) {
                    update_post_meta($pid, '_apex_cover_attachment_id', $new_id);
                    $migrated++;
                } else {
                    $failed++;
                }
            }
            wp_reset_postdata();
        }

        $args = [
            'page'     => 'apex-cover-migrate',
            'migrated' => $migrated,
            'skipped'  => $skipped,
            'failed'   => $failed,
            'total'    => $total,
            'dry'      => $dry ? 1 : 0,
        ];
        wp_redirect(add_query_arg($args, admin_url('edit.php?post_type=' . self::CPT_ANIME . '&page=apex-cover-migrate')));
        exit;
    }

    public function handle_save_media_item() {
        if (!current_user_can('manage_options')) {
            wp_die('无权限');
        }
        if (!isset($_POST['apex_media_item_nonce']) || !wp_verify_nonce($_POST['apex_media_item_nonce'], 'apex_media_item_nonce')) {
            wp_die('非法请求');
        }

        $media_type = isset($_POST['media_type']) ? sanitize_text_field($_POST['media_type']) : '';
        if (!in_array($media_type, ['anime', 'galgame'], true)) {
            wp_die('非法类型');
        }

        $id = isset($_POST['media_id']) ? intval($_POST['media_id']) : 0;
        $title = isset($_POST['media_title']) ? sanitize_text_field(wp_unslash($_POST['media_title'])) : '';
        $status = isset($_POST['media_status']) ? sanitize_text_field(wp_unslash($_POST['media_status'])) : '';
        $score_10_raw = isset($_POST['media_score_10']) ? trim(wp_unslash($_POST['media_score_10'])) : '';
        $score_10 = $score_10_raw === '' ? null : floatval($score_10_raw);
        if (!is_null($score_10)) {
            $score_10 = max(0, min(10, round($score_10, 1)));
        }
        $bgm = isset($_POST['media_bgm']) ? esc_url_raw(wp_unslash($_POST['media_bgm'])) : '';
        $cover = isset($_POST['media_cover']) ? esc_url_raw(wp_unslash($_POST['media_cover'])) : '';
        $review = isset($_POST['media_review']) ? wp_kses_post(wp_unslash($_POST['media_review'])) : '';
        $year = isset($_POST['media_year']) ? intval(wp_unslash($_POST['media_year'])) : 0;
        $quarter = isset($_POST['media_quarter']) ? sanitize_text_field(wp_unslash($_POST['media_quarter'])) : '';
        $show_on_home = isset($_POST['media_show_on_home']) ? 1 : 0;
        $show_on_home_feed = isset($_POST['media_show_on_home_feed']) ? 1 : 0;
        $home_rank = isset($_POST['media_home_rank']) ? intval($_POST['media_home_rank']) : null;
        $tags_raw = isset($_POST['media_tags']) ? sanitize_text_field($_POST['media_tags']) : '';
        $tags_array = $this->normalize_tags_input($tags_raw);
        $finished_raw = isset($_POST['media_finished_at']) ? sanitize_text_field($_POST['media_finished_at']) : '';
        $slug_input = isset($_POST['media_slug']) ? sanitize_title($_POST['media_slug']) : '';
        $finished_at = '';
        if (!empty($finished_raw)) {
            $dt = date_create($finished_raw);
            if ($dt) {
                $finished_at = $dt->format('Y-m-d 00:00:00');
            }
        }

        $season_text = isset($_POST['media_season_text']) ? sanitize_text_field(wp_unslash($_POST['media_season_text'])) : '';
        if (empty($season_text) && !empty($year) && !empty($quarter)) {
            $season_text = $this->build_season_text($year, $quarter);
        }

        $existing_row = $id > 0 ? $this->media_item_get_by_id($id) : null;

        $slug = $slug_input;
        if ($slug === '' && $existing_row && !empty($existing_row['slug'])) {
            $slug = $existing_row['slug'];
        }
        if ($slug === '' && !empty($title)) {
            $slug = $this->generate_slug($title, $id);
        }

        // 若没填新分数，沿用旧值；若填了 10 分制，则同步算出 100 分制
        if (is_null($score_10)) {
            if ($existing_row) {
                $score_10 = $this->resolve_score_10_from_row($existing_row);
            } else {
                $score_10 = null;
            }
        }
        $score_100 = is_null($score_10) ? 0 : intval(round($score_10 * 10));

        $existing_att = intval($existing_row['cover_attachment_id'] ?? 0);
        $attach_id = $existing_att;

        if (!empty($cover) && $this->is_external_url($cover)) {
            $new_att = $this->sideload_cover_attachment_from_url($cover);
            if ($new_att > 0) {
                $attach_id = $new_att;
            }
        } elseif (!empty($cover) && !$this->is_external_url($cover)) {
            // 用户改为站内或相对地址，不侧载
            $attach_id = $existing_att;
        }

        $data = [
            'type'             => $media_type,
            'title'            => $title,
            'slug'             => $slug,
            'status'           => $status,
            'score_100'        => $score_100,
            'score_10'         => $score_10,
            'bgm_url'          => $bgm,
            'cover_source_url' => $cover,
            'season_text'      => $season_text,
            'year'             => $year,
            'quarter'          => $quarter,
            'review'           => $review,
            'show_on_home'     => $show_on_home,
            'show_on_home_feed'=> $show_on_home_feed,
        ];
        if (!empty($finished_at)) {
            $data['finished_at'] = $finished_at;
        }
        if ($attach_id > 0) {
            $data['cover_attachment_id'] = $attach_id;
        }
        if (!is_null($home_rank)) {
            $data['home_rank'] = $home_rank;
        }

        $success = false;
        if ($id > 0) {
            $success = $this->media_item_update($id, $data);
            $saved_id = $id;
        } else {
            $saved_id = $this->media_item_insert($data);
            $success = $saved_id !== false;
        }

        if ($success && $saved_id > 0) {
            $this->media_item_set_tags($saved_id, $tags_array);
        }

        $redirect = add_query_arg([
            'page' => $media_type === 'anime' ? 'apex-media-anime' : 'apex-media-gal',
            'saved' => $success ? 1 : 0,
            'media_id' => $success ? $saved_id : 0,
        ], admin_url('admin.php'));
        wp_redirect($redirect);
        exit;
    }

    public function handle_delete_media_item() {
        if (!current_user_can('manage_options')) {
            wp_die('无权限');
        }
        if (!isset($_POST['apex_media_item_nonce']) || !wp_verify_nonce($_POST['apex_media_item_nonce'], 'apex_media_item_nonce')) {
            wp_die('非法请求');
        }
        $media_type = isset($_POST['media_type']) ? sanitize_text_field($_POST['media_type']) : '';
        if (!in_array($media_type, ['anime', 'galgame'], true)) {
            wp_die('非法类型');
        }
        $id = isset($_POST['media_id']) ? intval($_POST['media_id']) : 0;
        $row = $this->media_item_get_by_id($id);
        $success = false;
        if ($row && $row['type'] === $media_type) {
            $success = $this->media_item_delete($id);
        }

        $redirect = add_query_arg([
            'page' => $media_type === 'anime' ? 'apex-media-anime' : 'apex-media-gal',
            'saved' => $success ? 1 : 0,
        ], admin_url('admin.php'));
        wp_redirect($redirect);
        exit;
    }

    public function handle_migrate_media_data() {
        if (!current_user_can('manage_options')) {
            wp_die('无权限');
        }
        if (!isset($_POST['apex_migrate_media_data_nonce']) || !wp_verify_nonce($_POST['apex_migrate_media_data_nonce'], 'apex_migrate_media_data_nonce')) {
            wp_die('非法请求');
        }

        $dry = isset($_POST['dry_run_media']) && $_POST['dry_run_media'] == '1';

        $post_types = [self::CPT_ANIME, self::CPT_GALGAME];
        $type_map = [
            self::CPT_ANIME   => 'anime',
            self::CPT_GALGAME => 'galgame',
        ];

        $total    = 0;
        $inserted = 0;
        $updated  = 0;
        $failed   = 0;

        foreach ($post_types as $pt) {
            $q = new WP_Query([
                'post_type'      => $pt,
                'posts_per_page' => -1,
                'post_status'    => 'any',
                'fields'         => 'ids',
            ]);

            foreach ($q->posts as $pid) {
                $total++;
                $legacy_post_id = (int) $pid;

                $type   = isset($type_map[$pt]) ? $type_map[$pt] : '';
                $title  = get_the_title($pid);
                $status = $this->get_status_slug($pid);
                $bgm    = get_post_meta($pid, '_apex_bgm_url', true);
                $cover  = get_post_meta($pid, '_apex_cover_url', true);
                $att_id = intval(get_post_meta($pid, '_apex_cover_attachment_id', true));
                $score  = intval(get_post_meta($pid, '_apex_score', true));
                $score_10 = $this->map_score_100_to_10($score);
                $season = get_post_meta($pid, '_apex_season_text', true);
                $review = get_post_meta($pid, '_apex_review', true);
                $year   = intval(get_post_meta($pid, '_apex_year', true));
                $quarter= get_post_meta($pid, '_apex_quarter', true);

                $created_at = get_post_field('post_date_gmt', $pid);
                if (empty($created_at) || $created_at === '0000-00-00 00:00:00') {
                    $created_at = get_post_field('post_date', $pid);
                }
                if (empty($created_at)) {
                    $created_at = current_time('mysql');
                }
                $updated_at = get_post_field('post_modified_gmt', $pid);
                if (empty($updated_at) || $updated_at === '0000-00-00 00:00:00') {
                    $updated_at = get_post_field('post_modified', $pid);
                }
                if (empty($updated_at)) {
                    $updated_at = $created_at;
                }

                $data = [
                    'type'               => $type,
                    'title'              => $title,
                    'status'             => $status,
                    'bgm_url'            => $bgm,
                    'cover_source_url'   => $cover,
                    'cover_attachment_id'=> $att_id > 0 ? $att_id : null,
                    'score_100'          => $score,
                    'score_10'           => $score_10,
                    'season_text'        => $season,
                    'year'               => $year,
                    'quarter'            => $quarter,
                    'review'             => $review,
                    'created_at'         => $created_at,
                    'updated_at'         => $updated_at,
                ];

                $existing = $this->media_item_get_by_legacy_post_id($legacy_post_id);

                if ($dry) {
                    if ($existing) {
                        $updated++;
                    } else {
                        $inserted++;
                    }
                    continue;
                }

                $result_id = $this->media_item_upsert_by_legacy_post_id($legacy_post_id, $data);
                if ($result_id === false) {
                    $failed++;
                } else {
                    if ($existing) {
                        $updated++;
                    } else {
                        $inserted++;
                    }
                }
            }
            wp_reset_postdata();
        }

        $args = [
            'page'           => 'apex-cover-migrate',
            'media_total'    => $total,
            'media_inserted' => $inserted,
            'media_updated'  => $updated,
            'media_failed'   => $failed,
            'media_dry'      => $dry ? 1 : 0,
        ];
        wp_redirect(add_query_arg($args, admin_url('edit.php?post_type=' . self::CPT_ANIME . '&page=apex-cover-migrate')));
        exit;
    }

    public function register_acgn_rewrite() {
        add_rewrite_rule('^acgn/([^/]+)/?$', 'index.php?apex_media_slug=$matches[1]', 'top');
    }

    public function register_acgn_query_var($vars) {
        $vars[] = 'apex_media_slug';
        return $vars;
    }

    public function maybe_load_media_detail_template($template) {
        $slug = get_query_var('apex_media_slug');
        if (!empty($slug)) {
            $tpl = locate_template('media-detail.php');
            if (!empty($tpl)) {
                return $tpl;
            }
        }
        return $template;
    }

    public function register_post_types() {
        // 保留 CPT 以兼容旧数据/重写，但隐藏原生管理界面
        register_post_type(self::CPT_ANIME, [
            'labels' => [
                'name' => '番剧表','singular_name' => '番剧条目','add_new_item'=>'添加番剧条目','edit_item'=>'编辑番剧条目','menu_name'=>'番剧表',
            ],
            'public' => true,
            'has_archive' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'menu_icon' => 'dashicons-video-alt3',
            'supports' => ['title','editor','thumbnail'],
        ]);
        register_post_type(self::CPT_GALGAME, [
            'labels' => [
                'name'=>'galgame表','singular_name'=>'galgame条目','add_new_item'=>'添加galgame条目','edit_item'=>'编辑galgame条目','menu_name'=>'galgame表',
            ],
            'public' => true,
            'has_archive' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'menu_icon'=>'dashicons-games',
            'supports'=>['title','editor','thumbnail'],
        ]);
    }

    public function register_taxonomies() {
        register_taxonomy(self::TAX_STATUS, [self::CPT_ANIME, self::CPT_GALGAME], [
            'label'=>'状态','public'=>true,'hierarchical'=>false,'show_admin_column'=>true,'rewrite'=>['slug'=>'apex-status'],
        ]);
        foreach ($this->status_terms as $slug=>$name) {
            if (!term_exists($name, self::TAX_STATUS)) {
                wp_insert_term($name, self::TAX_STATUS, ['slug'=>$slug]);
            }
        }
    }

    public function register_meta_boxes() {
        add_meta_box('apex_anime_meta','番剧条目信息',[$this,'render_meta_box'],self::CPT_ANIME,'normal','high');
        add_meta_box('apex_galgame_meta','galgame条目信息',[$this,'render_meta_box'],self::CPT_GALGAME,'normal','high');
    }

    private function esc_meta($post_id, $key, $default='') {
        $v = get_post_meta($post_id, $key, true);
        return $v === '' ? $default : $v;
    }

    public function render_meta_box($post) {
        wp_nonce_field('apex_media_meta_nonce','apex_media_meta_nonce');
        $cover  = $this->esc_meta($post->ID,'_apex_cover_url');
        $bgm    = $this->esc_meta($post->ID,'_apex_bgm_url');
        $score  = $this->esc_meta($post->ID,'_apex_score','0');
        $season = $this->esc_meta($post->ID,'_apex_season_text');
        $review = $this->esc_meta($post->ID,'_apex_review');
        $year   = $this->esc_meta($post->ID,'_apex_year');
        $quarter= $this->esc_meta($post->ID,'_apex_quarter');
        ?>
        <style>
            .apex-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
            .apex-meta-grid .field{display:flex;flex-direction:column}
            .apex-meta-grid .field label{font-weight:600;margin-bottom:6px}
            .apex-meta-grid textarea{min-height:120px}
            @media (max-width: 782px){.apex-meta-grid{grid-template-columns:1fr}}
        </style>
        <div class="apex-meta-grid">
            <div class="field">
                <label>封面图 URL（卡片左侧主视图）</label>
                <input type="url" name="apex_cover_url" value="<?php echo esc_attr($cover); ?>" placeholder="https://example.com/cover.jpg">
            </div>
            <div class="field">
                <label>BGM 链接（标题跳转）</label>
                <input type="url" name="apex_bgm_url" value="<?php echo esc_attr($bgm); ?>" placeholder="https://bgm.tv/subject/xxxx">
            </div>
            <div class="field">
                <label>评分（0-100）</label>
                <input type="number" name="apex_score" value="<?php echo esc_attr($score); ?>" min="0" max="100" step="1">
            </div>
            <div class="field">
                <label>时间/发行信息（如 2025年夏季新番 或 发售时间）</label>
                <input type="text" name="apex_season_text" value="<?php echo esc_attr($season); ?>" placeholder="2025年夏季新番">
            </div>
            <div class="field">
                <label>年份（用于筛选与排序）</label>
                <input type="number" name="apex_year" value="<?php echo esc_attr($year); ?>" min="1970" max="2100" step="1" placeholder="2025">
            </div>
            <div class="field">
                <label>季度（用于筛选与排序）</label>
                <select name="apex_quarter">
                    <option value="">未设置</option>
                    <?php foreach ($this->quarters as $qk=>$qv): ?>
                        <option value="<?php echo esc_attr($qk); ?>" <?php selected($quarter,$qk); ?>><?php echo esc_html($qv); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" style="grid-column:1/-1">
                <label>个人评价（支持弹窗展示全文）</label>
                <textarea name="apex_review" placeholder="写下你的评价..."><?php echo esc_textarea($review); ?></textarea>
            </div>
        </div>
        <p>状态请在右侧“状态”分类中选择：想看 / 在看 / 看过。</p>
        <?php
    }

    public function save_meta($post_id) {
        if (!isset($_POST['apex_media_meta_nonce']) || !wp_verify_nonce($_POST['apex_media_meta_nonce'],'apex_media_meta_nonce')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post',$post_id)) return;

        // 保存原有字段并收集值
        $fields = [
            'apex_cover_url' , 'apex_bgm_url','apex_score','apex_season_text','apex_review','apex_year','apex_quarter'
        ];
        $vals = [];
        foreach ($fields as $f) {
            if (!isset($_POST[$f])) continue;
            $val = $_POST[$f];
            switch ($f) {
                case 'apex_cover_url':
                case 'apex_bgm_url':
                    $val = esc_url_raw($val); break;
                case 'apex_score':
                case 'apex_year':
                    $val = intval($val); break;
                case 'apex_quarter':
                case 'apex_season_text':
                    $val = sanitize_text_field($val); break;
                case 'apex_review':
                    $val = wp_kses_post($val); break;
            }
            $vals[$f] = $val;
            update_post_meta($post_id, '_'.$f, $val);
        }

        // 自动生成「{year}年{季}」：当 season_text 为空且 year+quarter 都存在时
        $year    = isset($vals['apex_year']) ? intval($vals['apex_year']) : intval(get_post_meta($post_id, '_apex_year', true));
        $quarter = isset($vals['apex_quarter']) ? $vals['apex_quarter'] : get_post_meta($post_id, '_apex_quarter', true);
        $season_text = isset($vals['apex_season_text']) ? $vals['apex_season_text'] : get_post_meta($post_id, '_apex_season_text', true);
        if (empty($season_text) && !empty($year) && !empty($quarter)) {
            $auto = $this->build_season_text($year, $quarter);
            if (!empty($auto)) {
                update_post_meta($post_id, '_apex_season_text', $auto);
            }
        }

        // 封面侧载：当填写的是外链且尚无附件ID时，将图片侧载到媒体库并保存附件ID
        $src = isset($vals['apex_cover_url']) ? $vals['apex_cover_url'] : get_post_meta($post_id, '_apex_cover_url', true);
        $existing_att = intval(get_post_meta($post_id, '_apex_cover_attachment_id', true));
        if ($this->is_external_url($src) && $existing_att <= 0) {
            $new_id = $this->sideload_cover_attachment($post_id, $src);
            if ($new_id > 0) {
                update_post_meta($post_id, '_apex_cover_attachment_id', $new_id);
            }
        }
    }

    public function enqueue_assets() {
        $ver = wp_get_theme()->get('Version');
        wp_enqueue_style('apex-media-list', get_template_directory_uri().'/assets/css/apex-media-list.css', [], $ver);
        wp_enqueue_script('apex-media-list', get_template_directory_uri().'/assets/js/apex-media-list.js', [], $ver, true);
    }

    public function shortcode_anime($atts) {
        return $this->render_shortcode(self::CPT_ANIME, '番剧', shortcode_atts([
            'status'=>'all', 'order'=>'default', 'per_page'=>-1, 'show_tabs'=>'true'
        ], $atts));
    }
    public function shortcode_galgame($atts) {
        return $this->render_shortcode(self::CPT_GALGAME, 'galgame', shortcode_atts([
            'status'=>'all', 'order'=>'default', 'per_page'=>-1, 'show_tabs'=>'true'
        ], $atts));
    }

    private function get_status_slug($post_id) {
        $terms = wp_get_post_terms($post_id, self::TAX_STATUS);
        if (!empty($terms) && !is_wp_error($terms)) {
            $slug = $terms[0]->slug;
            if (isset($this->status_terms[$slug])) return $slug;
            // fallback by name
            foreach ($this->status_terms as $s=>$n) {
                if ($terms[0]->name === $n) return $s;
            }
        }
        return '';
    }

    private function quarter_order($q) {
        $map = ['spring'=>1,'summer'=>2,'autumn'=>3,'winter'=>4];
        return isset($map[$q]) ? $map[$q] : 0;
    }

    private function render_shortcode($post_type, $type_label, $atts) {
        $media_type = $this->map_cpt_to_media_type($post_type);
        $status_filter = [];
        if (!empty($atts['status']) && $atts['status'] !== 'all') {
            $status_filter = [$atts['status']];
        }

        $order_by = 'created_at';
        if ($atts['order'] === 'score') {
            $order_by = 'score';
        } elseif ($atts['order'] === 'time') {
            $order_by = 'time';
        }

        $limit = intval($atts['per_page']);
        if ($limit <= 0) {
            $limit = 0; // 不限
        }

        $items = $this->media_query([
            'types'  => $media_type ? [$media_type] : [],
            'status' => $status_filter,
            'order_by' => $order_by,
            'order' => 'DESC',
            'limit' => $limit,
        ]);

        $item_tags_map = [];
        $avg_score = '';
        $median_score = '';
        $total_items = count($items);
        $finished_count = 0;

        if (!empty($items)) {
            $item_ids = array_map(function($r){ return intval($r['id']); }, $items);
            $item_tags_map = $this->media_items_get_tags_map($item_ids);

            $sum = 0; 
            $cnt = 0;
            $scores = [];

            foreach ($items as $r) {
                $s = $this->resolve_score_10_from_row($r);
                if ($s > 0) {
                    $sum += $s; 
                    $cnt++; 
                    $scores[] = $s;
                }
                if (isset($r['status']) && $r['status'] === 'watched') {
                    $finished_count++;
                }
            }

            if ($cnt > 0) {
                // 平均分
                $avg_score = number_format($sum / $cnt, 1);

                // 中位数
                sort($scores, SORT_NUMERIC);
                $mid = intdiv(count($scores), 2);
                if (count($scores) % 2 === 0) {
                    $median_score = number_format(($scores[$mid - 1] + $scores[$mid]) / 2, 1);
                } else {
                    $median_score = number_format($scores[$mid], 1);
                }
            }
        }

        $score_distribution = array_fill(0, 11, 0); // 0~10

        foreach ($scores as $s) {
            $idx = round($s); // 四舍五入到整数
            if ($idx >= 0 && $idx <= 10) {
                $score_distribution[$idx]++;
            }
        }
        ob_start();
        $uid = 'apxml-'.wp_generate_uuid4();
        ?>
        <style>
            .apex-baseline-line {display: flex;align-items: center;gap: 8px;}
            .apex-separator {width: 1px;height: 16px;background-color: #ccc;margin: 0 8px;}
        </style>
        <div class="apex-media-list" id="<?php echo esc_attr($uid); ?>" data-type="<?php echo esc_attr($post_type); ?>">
            <?php if ($avg_score !== ''): ?>
        <div class="apex-baseline">
            <div class="apex-baseline-line">
                <span class="apex-baseline-title">
                    <?php echo $media_type === 'galgame' ? 'Galgame 列表' : '番剧列表'; ?>
                </span>
            </div>
            <div class="apex-baseline-line">
                <span class="apex-baseline-title">基准分（均值）：</span>
                <span class="apex-baseline-value"><?php echo esc_html($avg_score); ?></span>
                <?php if ($median_score !== ''): ?>
                <span class="apex-separator"></span>
                <span class="apex-baseline-title">中位数：</span>
                <span class="apex-baseline-value"><?php echo esc_html($median_score); ?></span>
                <?php endif; ?>
            </div>
            <!--图表显示，包括下方的script逻辑、chart.js引用-->
            <div class="apex-baseline-line">
                <canvas id="<?php echo esc_attr($uid . '-chart'); ?>" height="150"></canvas>
            </div>
            <div class="apex-baseline-line apex-baseline-sub">
                <span>
                    共收集 <?php echo intval($total_items); ?> 个作品条目，其中 <?php echo intval($finished_count); ?> 部作品<?php echo $media_type === 'galgame' ? '已通关' : '已观看'; ?>
                </span>
            </div>
        </div>
        <script>
        (function(){
            const ctx = document.getElementById('<?php echo esc_js($uid . "-chart"); ?>').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(range(0, 10)); ?>,
                    datasets: [{
                        label: '数量：',
                        data: <?php echo json_encode($score_distribution); ?>,
                        fill: false,
                        borderColor: '#4f46e5',
                        tension: 0.2
                    }]
                },
                options: {
                    scales: {
                        x: {
                            title: { display: true, text: '分数（四舍五入）' },
                            ticks: { stepSize: 1 }
                        },
                        y: {
                            title: { display: true, text: '数量' },
                            beginAtZero: true,
                            precision: 0
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        })();
        </script>
        <?php endif; ?>
            <div class="apex-media-toolbar">
                <?php if ($atts['show_tabs'] !== 'false'): ?>
                <div class="apex-media-tabs" role="tablist">
                    <button class="tab active" data-status="all">全部</button>
                    <button class="tab" data-status="want">想看</button>
                    <button class="tab" data-status="watching">在看</button>
                    <button class="tab" data-status="watched">看过</button>
                    <button class="btn-filter" type="button">筛选</button>
                </div>
                <?php endif; ?>
                
                <div class="apex-media-actions">
                    <div class="apex-media-sort">
                        <label>排序</label>
                        <select class="apex-sort-select">
                            <option value="default" <?php selected($atts['order'], 'default'); ?>>默认</option>
                            <option value="score" <?php selected($atts['order'], 'score'); ?>>评分</option>
                            <option value="time" <?php selected($atts['order'], 'time'); ?>>发售/放映时间</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="apex-media-grid">
                <?php if (!empty($items)): foreach ($items as $item):
                    $cover = '';
                    $att_id = isset($item['cover_attachment_id']) ? intval($item['cover_attachment_id']) : 0;
                    if ($att_id > 0) {
                        $cover = wp_get_attachment_image_url($att_id, 'full');
                    }
                    if (empty($cover)) {
                        $cover = isset($item['cover_source_url']) ? $item['cover_source_url'] : '';
                    }
                    $bgm    = isset($item['bgm_url']) ? $item['bgm_url'] : '';
                    $detail_url = $this->get_media_detail_url($item);
                    $score_10 = $this->resolve_score_10_from_row($item);
                    $score_percent = max(0, min(100, $score_10 * 10));
                    $grade = $this->map_score_10_to_grade($score_10);
                    $season = isset($item['season_text']) ? $item['season_text'] : '';
                    $finished = (!empty($item['finished_at']) && $item['finished_at'] !== '0000-00-00 00:00:00') ? substr($item['finished_at'], 0, 10) : '';
                    $review = isset($item['review']) ? $item['review'] : '';
                    $year   = isset($item['year']) ? intval($item['year']) : 0;
                    $quarter= isset($item['quarter']) ? $item['quarter'] : '';
                    $status = isset($item['status']) ? $item['status'] : '';
                    $qord   = isset($item['qorder']) ? intval($item['qorder']) : 0;
                    $title  = isset($item['title']) ? $item['title'] : '';
                    $updated= isset($item['updated_at']) ? $item['updated_at'] : '';
                    $created= isset($item['created_at']) ? $item['created_at'] : '';
                    $updated_ts = !empty($updated) ? strtotime($updated) : 0;
                    $created_ts = !empty($created) ? strtotime($created) : 0;
                    $tags = isset($item_tags_map[$item['id']]) ? $item_tags_map[$item['id']] : [];
                    $start_label = $media_type === 'galgame' ? '发售' : '放送';
                    $finish_label = $media_type === 'galgame' ? '通关' : '完成';
                    $home_flag = !empty($item['show_on_home_feed']);
                    ?>
                    <div class="apex-card card shadow-sm"
                         data-status="<?php echo esc_attr($status ?: ''); ?>"
                         data-score="<?php echo esc_attr($score_10); ?>"
                         data-updated="<?php echo esc_attr($updated_ts); ?>"
                         data-created="<?php echo esc_attr($created_ts); ?>"
                         data-year="<?php echo esc_attr($year); ?>"
                         data-quarter="<?php echo esc_attr($quarter); ?>"
                         data-qorder="<?php echo esc_attr($qord); ?>"
                         data-title="<?php echo esc_attr($title); ?>"
                         data-review="<?php echo esc_attr(wp_strip_all_tags($review)); ?>">
                        <div class="apex-card-inner">
                            <div class="apex-cover-wrapper">
                                <div class="apex-cover">
                                    <?php if (!empty($cover)): ?>
                                        <img loading="lazy" src="<?php echo esc_url($cover); ?>" alt="<?php echo esc_attr($title); ?>">
                                    <?php else: ?>
                                        <div class="apex-cover-placeholder">No Cover</div>
                                    <?php endif; ?>
                                </div>
                                <div class="apex-scorebar" aria-label="评分">
                                    <div class="apex-scorebar-fill" style="width: <?php echo $score_percent; ?>%;"></div>
                                    <div class="apex-score-text">
                                        <?php if ($score_10 > 0): ?>
                                            <span class="apex-score-number"><?php echo esc_html(number_format($score_10, 1)); ?></span>
                                            <?php if (!empty($grade['label'])): ?>
                                                <span class="apex-grade-sep">|</span>
                                                <span class="apex-grade-label" data-grade="<?php echo esc_attr($grade['label']); ?>" data-grade-desc="<?php echo esc_attr($grade['desc']); ?>"><?php echo esc_html($grade['label']); ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            暂无评分
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="apex-info">
                                <div class="apex-badges">
                                    <span class="badge badge-type"><?php echo esc_html($type_label); ?></span>
                                    <?php if ($status && isset($this->status_terms[$status])): ?>
                                        <span class="badge badge-status"><?php echo esc_html($this->status_terms[$status]); ?></span>
                                    <?php endif; ?>
                                    <?php if ($home_flag): ?>
                                        <span class="badge badge-home">*首页展示</span>
                                    <?php endif; ?>
                                </div>
                                <div class="apex-title-row">
                                    <h3 class="apex-title">
                                        <a href="<?php echo esc_url($detail_url); ?>">
                                            <?php echo esc_html($title); ?>
                                        </a>
                                    </h3>
                                </div>
                                <div class="apex-meta-row">
                                    <?php if (!empty($season)): ?>
                                        <span class="apex-meta-text"><?php echo esc_html($season); ?></span>
                                        <span class="apex-meta-label"><?php echo esc_html($start_label); ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($season) && !empty($finished)): ?>
                                        <span> | </span>
                                    <?php endif; ?>

                                    <?php if (!empty($finished)): ?>
                                        <span class="apex-meta-text"><?php echo esc_html($finished); ?></span>
                                        <span class="apex-meta-label"><?php echo esc_html($finish_label); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="apex-review line-clamp">
                                    <?php echo esc_html(wp_strip_all_tags($review)); ?>
                                </div>

                                <?php 
                                $review_text = wp_strip_all_tags($review);
                                $review_length = mb_strlen($review_text);
                                $is_long = $review_length > 250;
                                ?>

                                <?php if ($review_length > 0): ?>
                                    <div class="apex-review-actions">
                                        <a href="<?php echo esc_url($detail_url); ?>">
                                            <button class="btn-readmore" type="button">阅读全文</button>
                                        </a>

                                        <div class="apex-review-meta">
                                            <span class="review-type <?php echo $is_long ? 'long' : 'short'; ?>">
                                                <?php echo $is_long ? '长评' : '短评'; ?>
                                            </span>
                                            <span class="review-divider"></span>
                                            <span class="review-length">
                                                +<?php echo $review_length; ?> 字
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($tags)): ?>
                                <div class="apex-tag-list">
                                    <span class="apex-tag-label">标签：</span>
                                    <?php foreach ($tags as $tg): ?>
                                        <span class="apex-tag-chip"><?php echo esc_html($tg['name']); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <div class="apex-empty">暂无条目</div>
                <?php endif; ?>
            </div>

            <!-- 阅读全文弹窗 如有需要把阅读全文按钮的a标签删了，现在是直接指向详情页
            <div class="apex-modal" aria-hidden="true" role="dialog">
                <div class="apex-modal-mask"></div>
                <div class="apex-modal-dialog card">
                    <div class="apex-modal-header">
                        <span class="apex-modal-title">评价</span>
                        <button class="apex-modal-close" aria-label="close">×</button>
                    </div>
                    <div class="apex-modal-body"></div>
                </div>
            </div>
            -->
            <!-- 筛选弹窗 -->
            <div class="apex-filter-modal" aria-hidden="true" role="dialog">
                <div class="apex-modal-mask"></div>
                <div class="apex-modal-dialog card">
                    <div class="apex-modal-header">
                        <span class="apex-modal-title">精确查找</span>
                        <button class="apex-modal-close" aria-label="close">×</button>
                    </div>
                    <div class="apex-modal-body">
                        <div class="filter-field">
                            <label>关键词</label>
                            <input type="text" class="filter-keyword" placeholder="按标题或评价关键词筛选">
                        </div>
                        <div class="filter-field">
                            <label>年份</label>
                            <select class="filter-year">
                                <option value="">全部年份</option>
                            </select>
                        </div>
                        <div class="filter-field">
                            <label>季度</label>
                            <select class="filter-quarter">
                                <option value="">全部季度</option>
                                <option value="spring">春季</option>
                                <option value="summer">夏季</option>
                                <option value="autumn">秋季</option>
                                <option value="winter">冬季</option>
                            </select>
                        </div>
                    </div>
                    <div class="apex-modal-footer">
                        <button class="btn-filter-reset" type="button">重置</button>
                        <button class="btn-filter-apply" type="button">应用</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// 全局实例，便于模板直接调用
global $apex_media_list;
$apex_media_list = new Apex_Media_List();