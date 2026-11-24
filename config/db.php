<?php

class Database
{
    private static ?PDO $conn = null;

    public static function getConnection(): PDO
    {
        if (self::$conn === null) {
            // No XAMPP (localhost) usa o MySQL do próprio computador;
            // no InfinityFree usa o banco da hospedagem.
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $local = in_array(explode(':', $host)[0], ['localhost', '127.0.0.1'], true);

            $cfg = $local
                ? [
                    'host' => 'localhost',
                    'name' => 'lojamilkshake2',
                    'user' => 'root',
                    'pass' => '',
                ]
                : [
                    'host' => 'sql112.infinityfree.com',
                    'name' => 'if0_43312247_lojamilkshake2',
                    'user' => 'if0_43312247',
                    'pass' => 'Dudateca12',
                ];

            try {
                self::$conn = new PDO(
                    "mysql:host={$cfg['host']};dbname={$cfg['name']};charset=utf8mb4",
                    $cfg['user'],
                    $cfg['pass'],
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                error_log('[db] ' . $e->getMessage());
                throw new RuntimeException('Falha na conexão com o banco de dados: ' . $e->getMessage());
            }
        }

        return self::$conn;
    }
}
