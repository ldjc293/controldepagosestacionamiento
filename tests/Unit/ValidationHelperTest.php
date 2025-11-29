<?php
/**
 * Unit tests for ValidationHelper
 */

use PHPUnit\Framework\Attributes\Test;

class ValidationHelperTest extends TestCase
{
    #[Test]
    public function testValidateEmail(): void
    {
        $this->assertTrue(ValidationHelper::validateEmail('test@example.com'));
        $this->assertTrue(ValidationHelper::validateEmail('user.name+tag@example.co.uk'));
        $this->assertFalse(ValidationHelper::validateEmail('invalid-email'));
        $this->assertFalse(ValidationHelper::validateEmail('test@'));
        $this->assertFalse(ValidationHelper::validateEmail('@example.com'));
    }

    #[Test]
    public function testValidatePassword(): void
    {
        $result = ValidationHelper::validatePassword('StrongPass123!');
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);

        $result = ValidationHelper::validatePassword('weak');
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    #[Test]
    public function testSanitize(): void
    {
        $input = '<script>alert("xss")</script>Hello World';
        $sanitized = ValidationHelper::sanitize($input);

        // strip_tags removes tags but keeps content, htmlspecialchars escapes quotes
        $this->assertEquals('alert("xss")Hello World', $sanitized);
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('</script>', $sanitized);
    }

    #[Test]
    public function testValidateCSRFToken(): void
    {
        // Generate a token
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;

        $this->assertTrue(ValidationHelper::validateCSRFToken($token));
        $this->assertFalse(ValidationHelper::validateCSRFToken('invalid-token'));
        $this->assertFalse(ValidationHelper::validateCSRFToken(''));
    }


    #[Test]
    public function testValidateDate(): void
    {
        $this->assertTrue(ValidationHelper::validateDate('2024-01-15'));
        $this->assertTrue(ValidationHelper::validateDate(date('Y-m-d')));
        $this->assertFalse(ValidationHelper::validateDate('2024-13-45'));
        $this->assertFalse(ValidationHelper::validateDate('invalid-date'));
    }

}