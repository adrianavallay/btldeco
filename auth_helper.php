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

// ── PASSWORD VALIDATION ──

const PASSWORD_MIN_LENGTH = 10;

function is_common_password(string $password): bool {
    static $set = null;
    if ($set === null) {
        // Top contraseñas más usadas en filtraciones públicas (rockyou, haveibeenpwned)
        $list = [
            '123456','password','12345678','qwerty','123456789','12345','1234','111111','1234567','dragon',
            '123123','baseball','abc123','football','monkey','letmein','696969','shadow','master','666666',
            'qwertyuiop','123321','mustang','1234567890','michael','654321','superman','1qaz2wsx','7777777','121212',
            '000000','qazwsx','123qwe','killer','trustno1','jordan','jennifer','zxcvbnm','asdfgh','hunter',
            'buster','soccer','harley','batman','andrew','tigger','sunshine','iloveyou','2000','charlie',
            'robert','thomas','hockey','ranger','daniel','starwars','klaster','112233','george','computer',
            'michelle','jessica','pepper','1111','zxcvbn','555555','11111111','131313','freedom','777777',
            'pass','maggie','159753','aaaaaa','ginger','princess','joshua','cheese','amanda','summer',
            'love','ashley','6969','nicole','chelsea','biteme','matthew','access','yankees','987654321',
            'dallas','austin','thunder','taylor','matrix','minecraft','jordan23','eagle1','shelby','555666',
            'abcd1234','qwerty123','1q2w3e4r','admin','admin123','administrator','root','toor','welcome','welcome1',
            'changeme','letmein1','passw0rd','p@ssword','p@ssw0rd','password1','password123','qwerty1','qwerty12','iloveu',
            'login','solo','starwars1','google','google123','contraseña','contrasena','clave','clave123','hola',
            '12345678910','01234567','01234567890','11223344','asdf1234','asdfasdf','aaaaaaaa','passw0rd1','password!','admin1234',
            'abcdef','abcdefg','abcdefgh','abcdefghi','qwertyui','qwertyuio','asdfghjkl','zxcvbnm1','poiuytrewq','lkjhgfdsa',
            'argentina','boca','river','messi','maradona','pepito','juan123','admin2024','admin2025','admin2026',
            'btldeco','btldeco123','tienda','tienda123','dyp','dyp123','consultora','adrian','adrian123','adriana',
        ];
        $set = array_flip(array_map('strtolower', $list));
    }
    return isset($set[strtolower($password)]);
}

/**
 * Valida una contraseña nueva.
 * Reglas: min PASSWORD_MIN_LENGTH chars, no en lista de comunes, al menos 1 letra y 1 número.
 */
function validate_password(string $password): array {
    $len = mb_strlen($password);
    if ($len < PASSWORD_MIN_LENGTH) {
        return ['ok' => false, 'mensaje' => 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres.'];
    }
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        return ['ok' => false, 'mensaje' => 'La contraseña debe incluir al menos una letra y un número.'];
    }
    if (is_common_password($password)) {
        return ['ok' => false, 'mensaje' => 'Esa contraseña es demasiado común. Elegí otra.'];
    }
    return ['ok' => true, 'mensaje' => 'OK'];
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
