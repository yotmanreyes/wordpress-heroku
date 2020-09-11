<?php

/** @var string Directory containing all of the site's files */
$root_dir = dirname(__DIR__);

/** @var string Document Root */
$webroot_dir = $root_dir . '/web';

/**
 * Expose global env() function from oscarotero/env
 */
Env::init();

/**
 * Use Dotenv to set required environment variables and load .env file in root
 */
$dotenv = new Dotenv\Dotenv($root_dir);
if (file_exists($root_dir . '/.env')) {
    $dotenv->load();
}

/**
 * Env - Default Values
 */
if (!getenv('DB_HOST')) {
    putenv('DB_HOST=localhost');
}
if (!getenv('DB_USER')) {
    putenv('DB_USER=root');
}

/**
 * Load Plugin Configurations
 */
function includeDirectory($dir)
{
    foreach (scandir($dir) as $filename) {
        $path = $dir . '/' . $filename;
        if (is_file($path)) {
            require_once($path);
        }
    }
}
includeDirectory($root_dir."/config/plugins/heroku");
includeDirectory($root_dir."/config/plugins/wordpress");

/**
 * Configuration - Database: Custom
 */
if (!empty(getenv('CUSTOM_DB_URL'))) {
    $env = parse_url(getenv('CUSTOM_DB_URL'));

    putenv(sprintf('DB_HOST=%s', $env['host']));
    if (array_key_exists('port', $env)) {
        putenv(sprintf('DB_PORT=%s', $env['port']));
    }
    putenv(sprintf('DB_USER=%s', $env['user']));
    putenv(sprintf('DB_PASSWORD=%s', $env['pass']));
    putenv(sprintf('DB_NAME=%s', ltrim($env['path'], '/')));

    unset($env);
}

/**
 * Configuration - Worker: IronWorker for WP CronJobs
 *  Disable WP Cronjobs, because they will be run using the iron worker.
 */
if (getenv('IRON_WORKER_PROJECT_ID') && getenv('IRON_WORKER_TOKEN')) {
    putenv(sprintf('DISABLE_WP_CRON=true'));
}

/**
 * Set up our global environment constant and load its config first
 * Default: production
 */
define('WP_ENV', env('WP_ENV') ?: 'production');

$env_config = __DIR__.'/environments/'.WP_ENV.'.php';
if (file_exists($env_config)) {
    require_once $env_config;
}

/**
 * URLs
 */
if (array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == 'https') {
    $_SERVER['HTTPS'] = 'on';
}
$_http_host_schema = array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
$_http_host_name = array_key_exists('HTTP_HOST', $_SERVER) ? $_SERVER['HTTP_HOST'] : 'localhost';
$_server_http_url = $_http_host_schema."://".$_http_host_name;

define('WP_HOME', env('WP_HOME') ?: $_server_http_url);
define('WP_SITEURL', env('WP_SITEURL') ?: $_server_http_url."/wp");

/**
 * Custom Content Directory
 */
define('CONTENT_DIR', '/app');
define('WP_CONTENT_DIR', $webroot_dir . CONTENT_DIR);
define('WP_CONTENT_URL', WP_HOME . CONTENT_DIR);

/**
 * DB settings
 */
define('DB_NAME','byvv7szgfnnfjrkr2oay');
define('DB_USER', 'ucxdoxrpmaumld6i');
define('DB_PASSWORD', 'DKsqkUPklUDgOOTk1ix6');
define('DB_HOST', 'byvv7szgfnnfjrkr2oay-mysql.services.clever-cloud.com');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
$table_prefix = env('DB_PREFIX') ?: 'wp_';

/**
 * Authentication Unique Keys and Salts
 */
define('AUTH_KEY',         '9zryjX#mM P|;i !x|G<-?rh?wJ[0@t1EUhHyo%I)>=.P{-B1{X4[9#(TbFAX]po');
define('SECURE_AUTH_KEY',  '^)I ZT[*_QaknW:&dy026CRO5O2,$ZDExx#%4%-.aHOF!vOpHOWg0.RZn<z=FOY(');
define('LOGGED_IN_KEY',    '3eI:||jIM|,>e|B)I~_^#*N05y,qHvw|#>2a/3{R>-dmmdU{raGD7wT-S_K6G$uj');
define('NONCE_KEY',        '|`8F*g.v@Aeks-kr%H25!wRT.<f+i*T/S|kjpn~XsK+y<+W,4:]ZD1FYJ:#J-$gy');
define('AUTH_SALT',        '|P9?0|fP`+oNN-RrL`Vn#jpfa[U)9O41%n23c18zPS g;=6U*FHmlD|5b0Za!zHr');
define('SECURE_AUTH_SALT', 'UZ=OTTAh9,5pnD;` pPhbgb-nOlc5`B-PcTmn!,(GxNC2{l|[9qHP+>>}[T@D~pJ');
define('LOGGED_IN_SALT',   '63c~r=:v>Mdn7m!5|IlSxxqj}+u)7l3G[a,.Crn.*>F{d_wo`H^k5]q94>W:vK0$');
define('NONCE_SALT',       'm&I4W4#:r%^?:*T%M33m]3McN ngV+y*jR4m?i!A=X-^pw_#GW_7H;1$yd0fbP6)');

/**
 * Custom Settings
 */
define('AUTOMATIC_UPDATER_DISABLED', true);
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);
define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?: false);

// Enforce SSL for Login/Admin in production
if (env('WP_ENV') != "development") {
    define('FORCE_SSL_LOGIN', true);
    define('FORCE_SSL_ADMIN', true);
}

/**
 * Multi Site
 *
 * If your Multisite is running on multiple domains
 * f.ex.: www.example.com main domain and www.subexample.com (instead of sub.example.com) as sub domain
 * use $_SERVER[ 'HTTP_HOST' ] instead of WP_MULTISITE_MAIN_DOMAIN in DOMAIN_CURRENT_SITE:
 * define( 'DOMAIN_CURRENT_SITE', $_SERVER[ 'HTTP_HOST' ]  );
 *
 * Without this, logins will only work in the DOMAIN_CURRENT_SITE.
 * Reauth is required on all sites in the network after this.
 */
define('WP_ALLOW_MULTISITE', env('WP_ALLOW_MULTISITE'));
if (env('WP_MULTISITE_MAIN_DOMAIN')) {
    define('MULTISITE', true);
    define('SUBDOMAIN_INSTALL', true);
    define('DOMAIN_CURRENT_SITE', env('WP_MULTISITE_MAIN_DOMAIN'));
    define('PATH_CURRENT_SITE', '/');
    define('SITE_ID_CURRENT_SITE', 1);
    define('BLOG_ID_CURRENT_SITE', 1);
    define('SUNRISE', true);
}

/**
 * Bootstrap WordPress
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', $webroot_dir . '/wp/');
}
