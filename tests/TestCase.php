<?php
/**
 * Base test case for all tests
 */

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clean up test database and set up fresh data
        $this->setUpDatabase();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->tearDownDatabase();

        parent::tearDown();
    }

    protected function setUpDatabase(): void
    {
        // Truncate all tables to start clean
        $this->truncateTables();

        // Seed test data
        $this->seedTestData();
    }

    protected function tearDownDatabase(): void
    {
        // Truncate tables after each test
        $this->truncateTables();
    }

    protected function truncateTables(): void
    {
        $tables = [
            'logs_actividad',
            'password_reset_tokens',
            'login_intentos',
            'configuracion_cron',
            'notificaciones',
            'solicitudes_cambios',
            'pago_mensualidad',
            'pagos',
            'mensualidades',
            'tasa_cambio_bcv',
            'configuracion_tarifas',
            'controles_estacionamiento',
            'apartamento_usuario',
            'apartamentos',
            'usuarios'
        ];

        foreach ($tables as $table) {
            try {
                Database::execute("TRUNCATE TABLE {$table}");
            } catch (Exception $e) {
                // Try DELETE if TRUNCATE fails
                try {
                    Database::execute("DELETE FROM {$table}");
                } catch (Exception $e2) {
                    // Ignore errors during cleanup
                }
            }
        }
    }

    protected function seedTestData(): void
    {
        // Create test user
        Database::execute("
            INSERT INTO usuarios (nombre_completo, email, password, rol, activo, primer_acceso, password_temporal)
            VALUES ('Test User', 'test@example.com', ?, 'cliente', true, false, false)
        ", [password_hash('password123', PASSWORD_BCRYPT)]);
    }

    /**
     * Helper to create authenticated session
     */
    protected function authenticateAs(string $role = 'cliente'): array
    {
        $user = [
            'id' => 1,
            'email' => 'test@example.com',
            'rol' => $role,
            'activo' => true
        ];

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_rol'] = $user['rol'];

        return $user;
    }

    /**
     * Helper to clear session
     */
    protected function clearAuthentication(): void
    {
        unset($_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_rol']);
    }
}