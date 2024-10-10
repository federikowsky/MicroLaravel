<?php

use App\Core\ServiceContainer;
use App\Core\Session;
use App\HTTP\Redirect;
use App\HTTP\Support\UrlGenerator;
use App\Facades\BaseFacade;
use App\Session\SessionManager;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



class RedirectTest extends TestCase
{
    protected $container;
    
    public function setUp(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        // Supponiamo che tu stia usando un service container nel tuo progetto
        $this->container = new ServiceContainer();

        BaseFacade::set_container($this->container);
        
        $session_config = [
            'driver' => 'array',
            'session_path' => __DIR__ . '/../storage/framework/sessions',
        ];

        $session_manager = new SessionManager($session_config, $this->container);

        // Simula una sessione per la gestione degli input flashati
        $session = new Session($session_manager->driver());
        $this->container->registerLazy(Session::class, function() use ($session) {
            return $session;
        });

        // $this->container->registerLazy(Response::class, function() {
        //     return new Response();
        // });

        // $this->container->registerLazy(UrlGenerator::class, function () {
        //     return new UrlGenerator();
        // });

        // $this->container->registerLazy(Redirect::class, function () {
        //     return new Redirect($this->container->getLazy(UrlGenerator::class));
        // });
    }

    public function testRedirectToLoginFromHome()
    {
        // Simula la richiesta alla homepage
        $request = Request::create('/home', 'GET');
        
        // Simula la risposta di reindirizzamento alla pagina di login
        $response = redirect('/login');
        $this->assertInstanceOf(Redirect::class, $response);
        $this->assertEquals('http://localhost/login', $response->get_url());
    }

    public function testRedirectBackWithInput()
    {
        // Simula una richiesta POST con dati errati e un redirect
        $request = Request::create('/login', 'POST', ['username' => 'wronguser', 'password' => 'wrongpass']);
        
        // Simula che la risposta sia un redirect
        $response = redirect()->back()->with_input(['username' => 'wronguser']);
        
        // Verifica che sia un RedirectResponse
        $this->assertInstanceOf(Redirect::class, $response);
        
        // Verifica che il redirect punti indietro
        $this->assertEquals('http://localhost/', $response->get_url());

        // Verifica che l'input sia stato flashato nella sessione
        $session = $this->container->getLazy(Session::class);
        $x = $session->get('inputs')['username'];
        $this->assertEquals('wronguser', $session->get('inputs')['username']);
    }
}
