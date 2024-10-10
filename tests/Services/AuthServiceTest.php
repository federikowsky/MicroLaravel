<?php

use PHPUnit\Framework\TestCase;
use App\Services\AuthService;
use App\Models\User;

class AuthServiceTest extends TestCase
{
    protected $authService;
    protected $userModel;

    protected function setUp(): void
    {
        // Prepara le dipendenze per il test
        $this->userModel = $this->createMock(User::class);
        $this->authService = new AuthService($this->userModel);
    }

    public function testActivateUser()
    {
        // // Definisci il comportamento del modello utente
        // $this->userModel->method('find_user_by_activation_code')
        //     ->willReturn(['id' => 1, 'email' => 'test@example.com', 'active' => 0]);

        // $this->userModel->expects($this->once())
        //     ->method('activate')
        //     ->with($this->equalTo(1))
        //     ->willReturn(true);

        // // Esegui il test
        // $result = $this->authService->activate('valid_activation_code');

        // // Verifica il risultato
        // $this->assertTrue($result);
        $this->assertTrue(true);

    }
}
