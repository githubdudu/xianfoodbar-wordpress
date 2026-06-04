<?php
if (!class_exists('wpdb')) {
    class wpdb {
        public string $prefix = 'wp_';
        public string $charset = 'utf8mb4';
        public string $collate = 'utf8mb4_unicode_ci';
        public string $last_error = '';
        public int $insert_id = 0;
        public mixed $nextRow = null;
        public array $nextResults = [];

        public function db_version(): string { return '8.0.0'; }
        public function get_row(string $query): mixed { return $this->nextRow; }
        public function get_results(string $query): array { return $this->nextResults; }
        public function query(string $query): bool { return true; }
        public function suppress_errors(bool $suppress = true): bool { return false; }
        public function db_connect(): bool { return true; }
    }
    $GLOBALS['wpdb'] = new wpdb();
}

if (!function_exists('get_option')) {
    function get_option(string $option, mixed $default = false): mixed { return $default; }
}

if (!function_exists('esc_sql')) {
    function esc_sql(mixed $data): mixed { return $data; }
}
