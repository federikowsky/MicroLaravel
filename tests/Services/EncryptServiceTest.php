<?php 

use PHPUnit\Framework\TestCase;

use App\Services\Security\EncryptionService;

class EncryptServiceTest extends TestCase
{
    protected $encryptService;
    protected $KEY = '123456';

    protected function setUp(): void
    {
        $this->encryptService = new EncryptionService($this->KEY);
    }

    public function testEncrypt()
    {
        $data = '1';
        $encrypted = $this->encryptService->encrypt($data);
        $this->assertEquals('c4ca4238a0b923820dcc509a6f75849b', $this->encryptService->encrypt('1'));
    }

    public function testDecrypt()
    {
        $this->assertEquals('1', $this->encryptService->decrypt('c4ca4238a0b923820dcc509a6f75849b'));
    }
}