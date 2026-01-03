<?php

class config
{
  private static $pdo = null;

  public static function getConnexion()
  {
    if (!isset(self::$pdo)) {
      // Support both Docker (env vars) and XAMPP (defaults)
      $host = getenv('DB_HOST') ?: 'localhost';
      $port = getenv('DB_PORT') ?: '3306';
      $dbname = getenv('DB_NAME') ?: 'nextgen_db';
      $user = getenv('DB_USER') ?: 'root';
      $pass = getenv('DB_PASS') ?: '';

      try {
        self::$pdo = new PDO(
          "mysql:host={$host};port={$port};dbname={$dbname}",  
          $user,
          $pass,
          [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
          ]
        );
      } catch (Exception $e) {
        die('Database Error: ' . $e->getMessage());
      }
    }
    return self::$pdo;
  }
}