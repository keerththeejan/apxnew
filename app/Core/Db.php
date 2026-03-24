<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Db
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $cfg = self::readConfig();
        $dsn = self::buildDsn($cfg);

        try {
            $opts = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $timeout = (int) ($cfg['timeout'] ?? 0);
            if ($timeout > 0) {
                $opts[PDO::ATTR_TIMEOUT] = $timeout;
            }
            if (($cfg['ssl_ca'] ?? '') !== '') {
                $opts[PDO::MYSQL_ATTR_SSL_CA] = (string) $cfg['ssl_ca'];
                if (($cfg['ssl_verify'] ?? true) === false) {
                    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                }
            }

            self::$pdo = new PDO($dsn, (string) $cfg['username'], (string) $cfg['password'], $opts);
        } catch (PDOException $e) {
            error_log('[db] connection failed: ' . $e->getMessage());
            http_response_code(500);
            echo 'Database connection error';
            exit;
        }

        return self::$pdo;
    }

    /** @return array{host:string,port:string,database:string,username:string,password:string,socket:string,ssl_ca:string,ssl_verify:bool,timeout:int} */
    private static function readConfig(): array
    {
        $url = trim((string) (self::firstEnv(['DB_URL', 'DATABASE_URL']) ?? ''));
        if ($url !== '') {
            $p = parse_url($url);
            if (is_array($p)) {
                return [
                    'host' => (string) ($p['host'] ?? '127.0.0.1'),
                    'port' => (string) ($p['port'] ?? '3306'),
                    'database' => ltrim((string) ($p['path'] ?? ''), '/'),
                    'username' => (string) ($p['user'] ?? ''),
                    'password' => (string) ($p['pass'] ?? ''),
                    'socket' => '',
                    'ssl_ca' => (string) (env('DB_SSL_CA', '') ?? ''),
                    'ssl_verify' => strtolower((string) (env('DB_SSL_VERIFY', '1') ?? '1')) !== '0',
                    'timeout' => (int) (env('DB_CONNECT_TIMEOUT', '10') ?? '10'),
                ];
            }
        }

        return [
            'host' => (string) (self::firstEnv(['DB_HOST', 'MYSQLHOST']) ?? '127.0.0.1'),
            'port' => (string) (self::firstEnv(['DB_PORT', 'MYSQLPORT']) ?? '3306'),
            'database' => (string) (self::firstEnv(['DB_DATABASE', 'MYSQLDATABASE']) ?? ''),
            'username' => (string) (self::firstEnv(['DB_USERNAME', 'MYSQLUSER']) ?? ''),
            'password' => (string) (self::firstEnv(['DB_PASSWORD', 'MYSQLPASSWORD']) ?? ''),
            'socket' => (string) (self::firstEnv(['DB_SOCKET', 'MYSQLSOCKET']) ?? ''),
            'ssl_ca' => (string) (env('DB_SSL_CA', '') ?? ''),
            'ssl_verify' => strtolower((string) (env('DB_SSL_VERIFY', '1') ?? '1')) !== '0',
            'timeout' => (int) (env('DB_CONNECT_TIMEOUT', '10') ?? '10'),
        ];
    }

    /** @param array{host:string,port:string,database:string,socket:string} $cfg */
    private static function buildDsn(array $cfg): string
    {
        $database = (string) $cfg['database'];
        $socket = trim((string) ($cfg['socket'] ?? ''));
        if ($socket !== '') {
            return 'mysql:unix_socket=' . $socket . ';dbname=' . $database . ';charset=utf8mb4';
        }

        return 'mysql:host=' . (string) $cfg['host'] . ';port=' . (string) $cfg['port'] . ';dbname=' . $database . ';charset=utf8mb4';
    }

    /** @param list<string> $keys */
    private static function firstEnv(array $keys): ?string
    {
        foreach ($keys as $k) {
            $v = env($k, null);
            if ($v !== null && trim($v) !== '') {
                return trim($v);
            }
        }
        return null;
    }
}
