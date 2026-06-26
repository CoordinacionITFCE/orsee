<?php
// part of orsee. see orsee.org
error_reporting(E_ALL & ~E_NOTICE);

// SERVER SETTINGS
// Web server document root, e.g. /srv/www/htdocs
// no trailing slash!
$settings__root_to_server = getenv('ORSEE_ROOT_TO_SERVER') ?: "/var/www/html";

// Experiment system root relative to server root, e.g. /orsee
// begins always with "/" if in a subdirectory
// no trailing slash!
$settings__root_directory = getenv('ORSEE_ROOT_DIRECTORY') !== false ? getenv('ORSEE_ROOT_DIRECTORY') : "";

// url to web server document root (IP or domain name)
// without trailing slash and the http://!
$settings__server_url = getenv('ORSEE_SERVER_URL') ?: "localhost:8080";

// server protocol (either "http://" or "https://")
$settings__server_protocol = getenv('ORSEE_SERVER_PROTOCOL') ?: "http://";

// Double-check your entries above! The URL to your ORSEE installation will be:
// settings__server_protocol + settings__server_url + settings__root_directory


// DATABASE CONFIGURATION
// Don't forget to create the database
$site__database_host = getenv('DB_HOST') ?: "localhost";
$site__database_port = getenv('DB_PORT') ?: "3306";
$site__database_database = getenv('DB_NAME') ?: "orsee_db";
$site__database_admin_username = getenv('DB_USER') ?: "orsee_user";
$site__database_admin_password = getenv('DB_PASSWORD') ?: "orsee_pw";
$site__database_type = "mysql";
$site__database_table_prefix = getenv('DB_PREFIX') ?: "or_";

// SSL mysql connection. Works with PHP >=5.3.9.
// Use only if your database is located on a different server
// and you want to connect via SSL encrypted connection to it
$site__database_use_ssl = getenv('DB_USE_SSL') === 'true';
// path name of client private key file
$site__database_ssl_key = getenv('DB_SSL_KEY') ?: '/etc/mysql/ssl/client-key.pem';
// path name of client public key certificate file
$site__database_ssl_cert = getenv('DB_SSL_CERT') ?: '/etc/mysql/ssl/client-cert.pem';
// path name of Certificate Authority (CA) certificate file.
// if used, must be the same on client and server
$site__database_ssl_ca = getenv('DB_SSL_CA') ?: '/etc/mysql/ssl/ca-cert.pem';

// TIMEZONE SETTING
// PHP >= 5.1.0 requires the timezone to be explicitly set.
$timezone = getenv('TZ') ?: "Europe/Vienna";
date_default_timezone_set($timezone);

// INCOMING EMAIL MODULE
// These settings are only needed when you plan to enable the email module
// to retrieve emails from an external email account and process them in ORSEE
$settings__email_server_type = getenv('EMAIL_INCOMING_TYPE') ?: "pop3"; // either pop3 or imap
$settings__email_server_name = getenv('EMAIL_INCOMING_HOST') ?: "mail.foobar.edu";
$settings__email_server_port = getenv('EMAIL_INCOMING_PORT') ?: "";
$settings__email_username = getenv('EMAIL_INCOMING_USER') ?: "orsee@foobar.edu";
$settings__email_password = getenv('EMAIL_INCOMING_PASSWORD') ?: "orseefoorbar_pw";
$settings__email_ssl = getenv('EMAIL_INCOMING_SSL') === 'true';

// SECURITY SETTINGS
// on a http server, use
session_set_cookie_params(array('httponly'=>true,'samesite'=>'Strict'));
// on a https server, use
// session_set_cookie_params(array('secure' => true,'httponly' => true,'samesite' => 'Strict'));

// STOP SITE, TRACKING, DEBUGGING
// If below is set to "y", the admin part of ORSEE won't be reachable for anybody
// This is useful for example when running some procedures directly in the database
$settings__stop_admin_site = getenv('STOP_ADMIN_SITE') ?: "n";

// To stop tracking set to "y"
$settings__disable_orsee_tracking = getenv('DISABLE_ORSEE_TRACKING') ?: "n";

// Enable/disable debugging information output at the bottom of each page.
$settings__time_debugging_enabled = getenv('TIME_DEBUGGING') ?: "n";
$settings__query_debugging_enabled = getenv('QUERY_DEBUGGING') ?: "n";

// Include path for tagsets. Leave as is, only change when you know what you are doing.
ini_set("include_path",ini_get("include_path").":./tagsets:./../tagsets:./../../tagsets");


// OUTGOING MAIL TRANSPORT
// mail: use legacy transport (mail() / sendmail wrapper, configured in General Settings).
// phpmailer: use PHPMailer + SMTP settings below.
$settings__mail_transport = getenv('MAIL_TRANSPORT') ?: "mail";

// Sendmail path
$settings__sendmail_path = getenv('SENDMAIL_PATH') ?: "/usr/sbin/sendmail";


// If $settings__mail_transport="phpmailer", PHPMailer will use the following settings.
$settings__phpmailer_host = getenv('SMTP_HOST') ?: "your.smtp.mailserver.com";
$settings__phpmailer_port = getenv('SMTP_PORT') ?: 587;
$settings__phpmailer_smtp_secure = getenv('SMTP_SECURE') ?: "tls"; // "", "tls", or "ssl"
$settings__phpmailer_smtp_auth_type = getenv('SMTP_AUTH_TYPE') ?: "password"; // "none", "password", or "oauth2"
$settings__phpmailer_username = getenv('SMTP_USER') ?: "";
$settings__phpmailer_password = getenv('SMTP_PASSWORD') ?: "";
$settings__phpmailer_timeout = getenv('SMTP_TIMEOUT') ?: 15;
$settings__phpmailer_debug = getenv('SMTP_DEBUG') ?: "n"; // y/n

// OAuth2 SMTP settings
$settings__phpmailer_smtp_oauth_identities=array(
    "*"=>array(
        "provider"=>getenv('SMTP_OAUTH_PROVIDER') ?: "google",
        "identity"=>getenv('SMTP_OAUTH_IDENTITY') ?: "",
        "client_id"=>getenv('SMTP_OAUTH_CLIENT_ID') ?: "",
        "client_secret"=>getenv('SMTP_OAUTH_CLIENT_SECRET') ?: "",
        "refresh_token"=>getenv('SMTP_OAUTH_REFRESH_TOKEN') ?: "",
        "token_endpoint"=>getenv('SMTP_OAUTH_TOKEN_ENDPOINT') ?: "", 
        "scopes"=>getenv('SMTP_OAUTH_SCOPES') ?: "", 
        "tenant"=>getenv('SMTP_OAUTH_TENANT') ?: "common" 
    )
);

?>
