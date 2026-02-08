<?php
declare(strict_types=1);

namespace App;

use App\Db;
use PDO;

class Settings
{
    /**
     * Load settings from DB and override $_ENV.
     * Call this AFTER ensuring DB connection is available or inside the logic flow.
     */
    public static function load(): void
    {
        try {
            // We assume App\Db::pdo() works. If not, it might throw, 
            // but we catch it to prevent crash if DB is totally dead (though other things will break).
            $pdo = Db::pdo();

            // Fetch all settings
            $stmt = $pdo->query("SELECT `key`, `val` FROM settings");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $k = $row['key'];
                $v = $row['val'];

                if ($k) {
                    // Update $_ENV and $_SERVER to "override" static .env values
                    $_ENV[$k]    = $v;
                    $_SERVER[$k] = $v;
                }
            }
        } catch (\Throwable $e) {
            // If table doesn't exist or DB connection fails, we silently fallback to .env
            // error_log("Settings load error: " . $e->getMessage());
        }
    }
}
