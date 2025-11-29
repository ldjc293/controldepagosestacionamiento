<?php
/**
 * Unit tests for Usuario model
 */

use PHPUnit\Framework\Attributes\Test;

class UsuarioTest extends TestCase
{
    #[Test]
    public function testUsuarioCreation(): void
    {
        $userData = [
            'nombre_completo' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'rol' => 'cliente',
            'activo' => true
        ];

        $userId = Usuario::create($userData);

        $this->assertIsInt($userId);
        $this->assertGreaterThan(0, $userId);
    }

    #[Test]
    public function testUsuarioFindById(): void
    {
        $userData = [
            'nombre_completo' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'rol' => 'cliente',
            'activo' => true
        ];

        $userId = Usuario::create($userData);
        $user = Usuario::findById($userId);

        $this->assertInstanceOf(Usuario::class, $user);
        $this->assertEquals('Test User', $user->nombre_completo);
        $this->assertEquals('test@example.com', $user->email);
    }

    #[Test]
    public function testUsuarioFindByEmail(): void
    {
        $userData = [
            'nombre_completo' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'rol' => 'cliente',
            'activo' => true
        ];

        Usuario::create($userData);
        $user = Usuario::findByEmail('test@example.com');

        $this->assertInstanceOf(Usuario::class, $user);
        $this->assertEquals('Test User', $user->nombre_completo);
    }

    #[Test]
    public function testUsuarioLoginValidation(): void
    {
        $userData = [
            'nombre_completo' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'rol' => 'cliente',
            'activo' => true,
            'primer_acceso' => false,
            'password_temporal' => false
        ];

        Usuario::create($userData);

        $result = Usuario::verifyLogin('test@example.com', 'password123');

        $this->assertTrue($result['success']);
        $this->assertInstanceOf(Usuario::class, $result['user']);
    }

    #[Test]
    public function testUsuarioLoginInvalidPassword(): void
    {
        $userData = [
            'nombre_completo' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'rol' => 'cliente',
            'activo' => true
        ];

        Usuario::create($userData);

        $result = Usuario::verifyLogin('test@example.com', 'wrongpassword');

        $this->assertFalse($result['success']);
        $this->assertNull($result['user']);
        $this->assertEquals('Email o contraseña incorrectos', $result['message']);
    }

    #[Test]
    public function testUsuarioBuscarCliente(): void
    {
        $userData = [
            'nombre_completo' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'rol' => 'cliente',
            'activo' => true
        ];

        Usuario::create($userData);

        $user = Usuario::buscarCliente('Juan');

        $this->assertInstanceOf(Usuario::class, $user);
        $this->assertEquals('Juan Pérez', $user->nombre_completo);
    }

    #[Test]
    public function testUsuarioHasPermission(): void
    {
        // Test cliente permissions
        $clienteData = [
            'nombre_completo' => 'Cliente User',
            'email' => 'cliente@example.com',
            'password' => 'password123',
            'rol' => 'cliente',
            'activo' => true
        ];

        $clienteId = Usuario::create($clienteData);
        $cliente = Usuario::findById($clienteId);

        // Cliente should have view_own_estado_cuenta but not admin_access
        $this->assertTrue($cliente->hasPermission('view_own_estado_cuenta'));
        $this->assertFalse($cliente->hasPermission('admin_access'));
        $this->assertFalse($cliente->hasPermission('nonexistent_permission'));

        // Test admin permissions
        $adminData = [
            'nombre_completo' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'rol' => 'administrador',
            'activo' => true
        ];

        $adminId = Usuario::create($adminData);
        $admin = Usuario::findById($adminId);

        // Admin should have all permissions
        $this->assertTrue($admin->hasPermission('admin_access'));
        $this->assertTrue($admin->hasPermission('view_own_estado_cuenta'));
        $this->assertTrue($admin->hasPermission('nonexistent_permission')); // Admin has 'all'
    }
}