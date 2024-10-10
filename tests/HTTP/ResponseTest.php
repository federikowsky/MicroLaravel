
<?php

require_once __DIR__ . '/../../config/app.php';

use App\Core\ServiceContainer;
use PHPUnit\Framework\TestCase;
use App\HTTP\Response;

use App\Facades\BaseFacade;

use App\Services\EncryptionService;

class ResponseTest extends TestCase
{
    protected $container;

    protected function setUp(): void
    {
        $this->container = new ServiceContainer();
        BaseFacade::set_container($this->container);

        $this->container->registerLazy(EncryptionService::class, function () {
            return new EncryptionService(APP_KEY);
        });
    }

    public function testStatusCode()
    {
        $response = $this->container->getLazy(Response::class);
        $response->set_status(404);
        $this->assertEquals(404, $response->get_status());
    }

    public function testHeaders()
    {
        $response = $this->container->getLazy(Response::class);
        $response->header('Content-Type', 'application/json');
        $this->assertEquals('application/json', $response->get_headers('Content-Type'));
    }
    public function testMultipleHeaders()
    {
        $response = $this->container->getLazy(Response::class);
        $response->header('Content-Type', 'application/json');
        $response->header('X-Custom-Header', 'TestValue');
        $headers = $response->get_headers();

        $this->assertEquals('application/json', $headers['Content-Type']);
        $this->assertEquals('TestValue', $headers['X-Custom-Header']);
    }

    public function testDuplicateHeaders()
    {
        $response = $this->container->getLazy(Response::class);
        $response->header('Content-Type', 'application/json');
        $response->header('Content-Type', 'text/plain', false); // No replace, should not overwrite

        $this->assertEquals(['application/json', 'text/plain'], $response->get_headers('Content-Type'));
    }

    public function testInvalidStatusCode()
    {
        $this->expectException(\InvalidArgumentException::class);
        $response = $this->container->getLazy(Response::class);
        $response->set_status(999); // Codice di stato non valido
    }

    public function testBody()
    {
        $response = $this->container->getLazy(Response::class);
        $response->set_content('{"message": "Hello, World!"}');
        $this->assertEquals('{"message": "Hello, World!"}', $response->get_content());
    }

    public function testEmptyBody()
    {
        $response = $this->container->getLazy(Response::class);
        $response->set_content('');
        $this->assertEquals('', $response->get_content());
    }

    public function testJsonBody()
    {
        $response = $this->container->getLazy(Response::class);
        $response->json(['message' => 'Hello, World!']);
        $this->assertEquals(
            json_decode('{"message": "Hello, World!"}', true),
            json_decode($response->get_content(), true)
        );
    }

    public function testJsonWithHeaders()
    {
        $response = $this->container->getLazy(Response::class);
        $response->json(['message' => 'Hello, World!'], 200, ['X-Custom-Header' => 'Test']);
        $this->assertEquals('Test', $response->get_headers('X-Custom-Header'));
        $this->assertEquals('application/json', $response->get_headers('Content-Type'));
    }

    public function testStreamContent()
    {
        $response = $this->container->getLazy(Response::class);
        $response->stream(function () {
            echo 'Streaming content...';
        });

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertEquals('Streaming content...', $output);
    }

    public function testDownloadFile()
    {
        $file = tempnam(sys_get_temp_dir(), 'test_file');
        file_put_contents($file, 'File content');

        $response = $this->container->getLazy(Response::class);
        $response->download($file, 'test.txt');

        $headers = $response->get_headers();
        $this->assertEquals('attachment; filename=test.txt', $headers['Content-Disposition']);
        $this->assertEquals(filesize($file), $headers['Content-Length']);
    }
    
    public function testDownloadFileWithDispositionInline()
    {
        $file = tempnam(sys_get_temp_dir(), 'test_file');
        file_put_contents($file, 'File content');

        $response = $this->container->getLazy(Response::class);
        $response->download($file, 'test.txt', [], 'inline');

        $headers = $response->get_headers();
        $this->assertEquals('inline; filename=test.txt', $headers['Content-Disposition']);
        $this->assertEquals(filesize($file), $headers['Content-Length']);
    }

    public function testRedirectResponse()
    {
        $response = $this->container->getLazy(Response::class);
        $response->header('Location', '/login');
        $response->set_status(302);

        $this->assertEquals(302, $response->get_status());
        $this->assertEquals('/login', $response->get_headers('Location'));
    }

    public function testNoContentResponse()
    {
        $response = $this->container->getLazy(Response::class);
        $response->no_content();

        $this->assertEquals(204, $response->get_status());
        $this->assertEquals('', $response->get_content());
    }

    public function testEncryptCookie()
    {
        $response = $this->container->getLazy(Response::class);
        $response->cookie('session_cookie', 'session_value', 3600, true);
        $response->cookie('user_cookie', 'user_value', 3600, false);

        $cookies = $response->get_cookies();
        $this->assertArrayHasKey('session_cookie', $cookies);
        $this->assertArrayHasKey('user_cookie', $cookies);
        $this->assertEquals('session_value', $cookies['session_cookie']->value);
        $this->assertEquals('user_value', $cookies['user_cookie']->value);
    }

    public function testCookies()
    {
        $response = $this->container->getLazy(Response::class);
        $response->cookie('session_cookie', 'session_value', 3600);

        $cookies = $response->get_cookies();
        $this->assertArrayHasKey('session_cookie', $cookies);
        $this->assertEquals('session_value', $cookies['session_cookie']->value);
    }

    public function testRemoveCookie()
    {
        // Crea una nuova risposta
        $response = $this->container->getLazy(Response::class);

        // Aggiungi un cookie
        $response->cookie('user_cookie', 'some_value');

        // Verifica che il cookie sia presente
        $cookies = $response->get_cookies();
        $this->assertContains('user_cookie', array_keys($cookies));

        // Rimuovi il cookie
        $response->without_cookie('user_cookie');

        // Ottieni di nuovo i cookie
        $cookies = $response->get_cookies();

        // Verifica che il cookie time sia inferiore a quello attuale
        $this->assertLessThan(time(), $cookies['user_cookie']->expire);
    }

    public function testSendWithException()
    {
        $response = $this->container->getLazy(Response::class);
        $exception = new \Exception('Test Exception');
        $response->with_exception($exception);

        $this->assertEquals($exception, $response->get_exception());
    }

    public function testSetHeadersInSendMethod()
    {
        $response = $this->container->getLazy(Response::class);
        $response->set_content('Body content');
        $response->set_status(200);
        $response->header('X-Header-1', 'Value1');
        $response->header('X-Header-2', 'Value2');

        $response->send();

        $this->assertEquals('Value1', $response->get_headers('X-Header-1'));
        $this->assertEquals('Value2', $response->get_headers('X-Header-2'));
    }

    public function testSend()
    {
        // Crea un'istanza di Response con dei valori iniziali
        $response = $this->container->getLazy(Response::class);

        // Imposta lo status e il contenuto (puoi anche usare make)
        $response->set_content('Hello, Universe!')
            ->set_status(202)
            ->with_headers([
                'X-Custom-Header' => 'CustomValue',
                'Content-Length' => strlen('Hello, Universe!'),
                'Content-Type' => 'text/plain'
            ])
            ->cookie('test_cookie', 'cookie_value');

        // Aggiungi un cookie
        $response->cookie('test_cookie', 'cookie_value');

        // Simula l'invio della risposta
        $response->send();

        // Verifica che le intestazioni siano state impostate correttamente
        $headers = $response->get_headers();
        $this->assertArrayHasKey('X-Custom-Header', $headers);
        $this->assertEquals('CustomValue', $headers['X-Custom-Header']);
        $this->assertEquals('text/plain', $headers['Content-Type']);

        // Verifica che lo status sia 202
        $this->assertEquals(202, $response->get_status());

        // Verifica che il contenuto sia stato impostato correttamente
        $this->assertEquals('Hello, Universe!', $response->get_content());

        // Verifica che il cookie sia stato aggiunto correttamente
        $cookies = $response->get_cookies();
        $this->assertArrayHasKey('test_cookie', $cookies);
    }

}