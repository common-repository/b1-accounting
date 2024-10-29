<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-b1-accounting-base.php';

class B1_Accounting_Logger extends B1_Accounting_Base
{

    const TTL = 3600;
    const TABLE_NAME = 'b1_logs';
    const PER_PAGE = 20;

    /**
     * @var $wpdb
     */
    private $db;

    /**
     * @var string $table_name
     */
    private $table_name;
    /**
     * @var B1
     */
    private $b1;

    public function __construct($plugin_name, $version)
    {
        parent::__construct($plugin_name, $version);

        set_time_limit(self::TTL);
        ini_set('max_execution_time', self::TTL);

        global $wpdb;
        $this->db = $wpdb;
        $this->table_name = $wpdb->prefix . self::TABLE_NAME;
    }

    public function print_error_and_die($errHandle)
    {
        echo "<pre style='color:#ff0000'>";
        $content = $errHandle->getResponse()->getContent();
        print_r($errHandle->getResponse());
        B1_Accounting_Helper::debug($content['message']);
        echo "</pre>";
        wp_die('Error');
    }

    public function print_error_content($errHandle)
    {
        echo "<pre style='color:#ff0000'>";
        $content = $errHandle->getResponse()->getContent();
        print_r($content);
        echo "</pre>";
    }

    public function print_error_message($errHandle)
    {
        echo "<pre style='color:#ff0000'>";
        echo $errHandle->getMessage();
        echo "</pre>";
    }

    /**
     * Get initial SQL used in activator
     * @return string
     */
    public static function get_init_sql()
    {
        global $wpdb;
        $logs_table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "
CREATE TABLE IF NOT EXISTS {$logs_table_name}(
   id bigint(11) NOT NULL AUTO_INCREMENT,
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   is_success smallint,
   debug_info text,
   PRIMARY KEY  (id) 
) {$charset_collate}";
        return $sql;
    }

    public static function get_drop_sql()
    {
        global $wpdb;
        $logs_table_name = $wpdb->prefix . self::TABLE_NAME;

        return "DROP TABLE IF EXISTS {$logs_table_name}";
    }

    /**
     * Used by WP cron to clear log items older than 30 days
     */
    public function clear_old_logs()
    {
        try {
            $time = date('Y-m-d H:i:s', strtotime('-7 day'));

            $this->db->query("DELETE FROM {$this->table_name} WHERE `created_at` < '{$time}'");

        } catch (B1Exception $e) {
            $this->print_error_message($e);
        }
    }

    /**
     * Generate table & pagination to display
     * @param int $page
     * @return string
     */
    public function fetch_all_logs_as_html($page = 1)
    {
        $html = '<table class="table table-hover B1-logs">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Date</th>
                            <th scope="col">Status</th>
                            <th scope="col">Request detail</th>
                        </tr>
                        </thead>
                        <tbody >';

        try {
            $per_page = self::PER_PAGE;
            $offset = ($page - 1) * $per_page;

            $sql = "SELECT * FROM {$this->table_name} ORDER BY `created_at` DESC LIMIT {$per_page} OFFSET {$offset}";

            $total = $this->db->get_var("SELECT COUNT(*) FROM {$this->table_name}");
            $num_of_pages = ceil($total / $per_page);

            foreach ($this->db->get_results($sql) as $key => $row) {
                $short_text = substr($row->debug_info, 0, 150);
                $badge = $row->is_success == 1 ? '<span class="label label-success">OK</span>' : '<span class="label label-danger">Error</span>';

                $html .= "<tr data-id='{$row->id}'>
                                <td><input type=\"checkbox\" name=\"b1LogItemChkBox[]\" value=\"{$row->id}\"></td>
                                <td>{$row->created_at}</td>
                                <td>{$badge}</td>
                                <td>{$short_text}</td>
                            </tr>";
            }

            // pagination
            $page_links = paginate_links(array(
                'base' => add_query_arg('pagenum', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;', 'text-domain'),
                'next_text' => __('&raquo;', 'text-domain'),
                'total' => $num_of_pages,
                'current' => $page
            ));

        } catch (B1Exception $e) {
            $this->print_error_message($e);
        }
        $html .= '</tbody></table>
                <div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
        return $html;
    }

    /**
     * Return one log item to view
     * @param $id
     * @return string
     */
    public function fetch_log_by_id_as_html($id)
    {
        $html = '';
        try {
            $log_item = $this->db->get_row("SELECT * FROM {$this->table_name} WHERE `id` = {$id}");

            if ($log_item !== null) {

                $badge = $log_item->is_success == 1 ? '<span class="label label-success">OK</span>' : '<span class="label label-danger">Error</span>';
                $html .= "<div class=\"panel panel-default\">
                            <div class=\"panel-heading\">
                               #{$log_item->id} {$badge} at {$log_item->created_at}
                            </div>
                            <div class=\"panel-body\">
                                <div class=\"form-group\">
                                    <textarea style=\"font-family:monospace;\" rows=\"60\" cols=\"100\" readonly>{$log_item->debug_info}</textarea>
                                </div>
                            </div>
                      </div>";
            }

        } catch (B1Exception $e) {
            $this->print_error_message($e);
        }
        return $html;
    }

    /**
     * Save debug info to log
     * @param $is_success
     * @param $debug_info
     */
    public static function save($is_success, $debug_info)
    {
        try {
            global $wpdb;

            $logs_table_name = $wpdb->prefix . self::TABLE_NAME;

            $wpdb->insert($logs_table_name, ['is_success' => $is_success,
                'debug_info' => json_encode($debug_info, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            ]);

        } catch (B1Exception $e) {
        }
    }

    /**
     * Export log to file
     * @param $selected_ids
     */
    public function export_logs($selected_ids)
    {
        try {
            $result = [];
            $where_clause = "";
            if (is_array($selected_ids) && count($selected_ids) > 0) {
                $where_clause = "WHERE `id` in (" . implode(',', $selected_ids) . ")";
            }
            $sql = "SELECT `created_at`,`is_success`,`debug_info` FROM {$this->table_name} {$where_clause}";

            foreach ($this->db->get_results($sql) as $key => $row) {
                $debug_info = json_decode($row->debug_info, true);
                $is_success = $row->is_success == 1;
                $result[] = [
                    'created_at' => $row->created_at,
                    'is_success' => $is_success,
                    'debug_info' => $debug_info
                ];

            }

            $filename = 'b1-woocommerce-logs-' . date('Y_m_d_H_i_s');
            header("Content-type: text/plain");
            header('Content-Disposition: attachment; filename="' . $filename . '.json"');
            echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit();
        } catch (B1Exception $e) {
            $this->print_error_message($e);
            // wp_die('Final Error');
        }

    }

    /**
     * Used for Db migration
     */
    public function updateDB()
    {
        try {

            $this->db->query(self::get_init_sql());

        } catch (B1Exception $e) {
            $this->print_error_message($e);
        }

    }

}
