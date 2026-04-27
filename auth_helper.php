<?php
require_once __DIR__ . '/config.php';

// ── RATE LIMITING ──

const LOGIN_MAX_ATTEMPTS    = 5;
const LOGIN_WINDOW_SECONDS  = 900; // 15 min

function login_attempts_init(): void {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    pdo()->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        ip VARCHAR(45) NOT NULL,
        identifier VARCHAR(255) NOT NULL,
        success TINYINT(1) NOT NULL DEFAULT 0,
        attempted_at DATETIME NOT NULL,
        INDEX idx_ip_id_time (ip, identifier, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function login_client_ip(): string {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Devuelve segundos hasta desbloqueo, o 0 si no está bloqueado.
 */
function login_throttle_check(string $identifier): int {
    login_attempts_init();
    $ip = login_client_ip();
    $stmt = pdo()->prepare(
        "SELECT COUNT(*) AS cnt, UNIX_TIMESTAMP(MAX(attempted_at)) AS last
         FROM login_attempts
         WHERE ip = ? AND identifier = ? AND success = 0
           AND attempted_at >= (NOW() - INTERVAL ? SECOND)"
    );
    $stmt->execute([$ip, $identifier, LOGIN_WINDOW_SECONDS]);
    $row = $stmt->fetch();
    $cnt = (int) ($row['cnt'] ?? 0);
    if ($cnt < LOGIN_MAX_ATTEMPTS) return 0;
    $last = (int) ($row['last'] ?? 0);
    $unlock = $last + LOGIN_WINDOW_SECONDS;
    $now = time();
    return max(0, $unlock - $now);
}

function login_record_attempt(string $identifier, bool $success): void {
    login_attempts_init();
    $ip = login_client_ip();
    pdo()->prepare(
        "INSERT INTO login_attempts (ip, identifier, success, attempted_at) VALUES (?, ?, ?, NOW())"
    )->execute([$ip, $identifier, $success ? 1 : 0]);

    // Si fue exitoso, limpiamos los intentos previos para esta combinación
    if ($success) {
        pdo()->prepare(
            "DELETE FROM login_attempts WHERE ip = ? AND identifier = ? AND success = 0"
        )->execute([$ip, $identifier]);
    }

    // Mantenemos la tabla limpia: borramos registros más viejos que 1 día
    if (random_int(1, 50) === 1) {
        pdo()->exec("DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)");
    }
}

function login_throttle_message(int $secondsRemaining): string {
    $minutes = (int) ceil($secondsRemaining / 60);
    return "Demasiados intentos fallidos. Volvé a intentar en {$minutes} minuto" . ($minutes !== 1 ? 's' : '') . '.';
}

// ── CLIENTE AUTH ──

function cliente_register(string $nombre, string $email, string $password, string $telefono = ''): array {
    $db = pdo();
    $stmt = $db->prepare("SELECT id FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['ok' => false, 'mensaje' => 'Ya existe una cuenta con ese email'];
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO clientes (nombre, email, password, telefono) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $email, $hash, $telefono]);
    $id = (int) $db->lastInsertId();
    session_regenerate_id(true);
    $_SESSION['cliente_id'] = $id;
    $_SESSION['cliente_nombre'] = $nombre;
    $_SESSION['cliente_email'] = $email;
    return ['ok' => true, 'mensaje' => 'Cuenta creada con éxito'];
}

function cliente_login(string $email, string $password): array {
    $identifier = 'cliente:' . strtolower($email);
    $blocked = login_throttle_check($identifier);
    if ($blocked > 0) {
        return ['ok' => false, 'mensaje' => login_throttle_message($blocked)];
    }

    $db = pdo();
    $stmt = $db->prepare("SELECT * FROM clientes WHERE email = ? AND activo = 1");
    $stmt->execute([$email]);
    $c = $stmt->fetch();
    if (!$c || !password_verify($password, $c['password'])) {
        login_record_attempt($identifier, false);
        return ['ok' => false, 'mensaje' => 'Email o contraseña incorrectos'];
    }

    login_record_attempt($identifier, true);
    session_regenerate_id(true);
    $_SESSION['cliente_id'] = (int) $c['id'];
    $_SESSION['cliente_nombre'] = $c['nombre'];
    $_SESSION['cliente_email'] = $c['email'];
    $db->prepare("UPDATE clientes SET ultimo_acceso = NOW() WHERE id = ?")->execute([$c['id']]);
    return ['ok' => true, 'mensaje' => 'Sesión iniciada'];
}

function cliente_logout(): void {
    unset($_SESSION['cliente_id'], $_SESSION['cliente_nombre'], $_SESSION['cliente_email']);
}

function cliente_data(): ?array {
    if (!is_cliente()) return null;
    $stmt = pdo()->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([cliente_id()]);
    return $stmt->fetch() ?: null;
}

function cliente_change_password(int $id, string $current, string $new): array {
    $db = pdo();
    $stmt = $db->prepare("SELECT password FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($current, $row['password'])) {
        return ['ok' => false, 'mensaje' => 'Contraseña actual incorrecta'];
    }
    $hash = password_hash($new, PASSWORD_DEFAULT);
    $db->prepare("UPDATE clientes SET password = ? WHERE id = ?")->execute([$hash, $id]);
    return ['ok' => true, 'mensaje' => 'Contraseña actualizada'];
}

// ── ADMIN AUTH ──

function admin_login(string $user, string $pass): array {
    $identifier = 'admin:' . strtolower($user ?: '_');
    $blocked = login_throttle_check($identifier);
    if ($blocked > 0) {
        return ['ok' => false, 'mensaje' => login_throttle_message($blocked)];
    }

    if ($user !== ADMIN_USER || ADMIN_PASS === '' || !password_verify($pass, ADMIN_PASS)) {
        login_record_attempt($identifier, false);
        return ['ok' => false, 'mensaje' => 'Usuario o contraseña incorrectos'];
    }

    login_record_attempt($identifier, true);
    session_regenerate_id(true);
    $_SESSION['admin_auth'] = true;
    return ['ok' => true, 'mensaje' => 'Sesión iniciada'];
}

function admin_logout(): void {
    unset($_SESSION['admin_auth']);
}

function require_admin(): void {
    if (!is_admin()) {
        redirect('admin.php');
    }
}

function require_cliente(): void {
    if (!is_cliente()) {
        redirect('login.php');
    }
}
