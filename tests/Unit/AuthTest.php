<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testPasswordHashingAndVerification(): void
    {
        $password = 'senhaSegura123';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->assertNotEmpty($hash);
        $this->assertNotEquals($password, $hash);
        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('senhaIncorreta', $hash));
    }

    public function testInputSanitization(): void
    {
        $dirtyInput = "  <script>alert('xss')</script> Hello World  ";
        $cleanInput = sanitize($dirtyInput);

        $this->assertEquals("alert(&#039;xss&#039;) Hello World", $cleanInput);
    }
}
