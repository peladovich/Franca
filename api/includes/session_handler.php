<?php
/**
 * Database-backed PHP session storage.
 *
 * Serverless platforms (e.g. Vercel) don't guarantee the local filesystem
 * persists between function invocations, so PHP's default file-based
 * session save handler can silently lose logins, carts, CSRF tokens, and
 * the i18n locale between requests. Storing sessions in the same MySQL
 * database the app already uses avoids that entirely, and works
 * identically on a traditional host too.
 */
class DbSessionHandler implements SessionHandlerInterface
{
    private PDO $pdo;
    private int $maxLifetime;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->maxLifetime = (int) ini_get('session.gc_maxlifetime') ?: 1440;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        $stmt = $this->pdo->prepare('SELECT data FROM sessions WHERE id = ? AND last_activity > ?');
        $stmt->execute([$id, time() - $this->maxLifetime]);
        $row = $stmt->fetch();
        return $row ? $row['data'] : '';
    }

    public function write(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sessions (id, data, last_activity) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE data = VALUES(data), last_activity = VALUES(last_activity)'
        );
        return $stmt->execute([$id, $data, time()]);
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE last_activity <= ?');
        $stmt->execute([time() - $max_lifetime]);
        return $stmt->rowCount();
    }
}
