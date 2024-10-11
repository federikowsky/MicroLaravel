<?php

use App\Core\ServiceContainer;
use App\Core\Session;
use App\HTTP\Redirect;
use App\Facades\BaseFacade;
use App\HTTP\Router;
use App\Session\SessionManager;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;



class RedirectTest extends TestCase
{
    protected $container;
    protected $redirect;
    
    public function setUp(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $session_config = [
            'driver' => 'array',
            'session_path' => __DIR__ . '/../storage/framework/sessions',
        ];
        
        $this->container = ServiceContainer::get_instance();
        
        BaseFacade::set_container($this->container);
        
        $router = new Router($this->container);
        $router->load_routes([
            __DIR__ . '/../../routes/app.php',
            __DIR__ . '/../../routes/auth.php',
            __DIR__ . '/../../routes/admin.php',
            __DIR__ . '/../../routes/user.php',
            __DIR__ . '/../../routes/post.php'
        ]);

        $this->container->register(Router::class, $router);

        // Simula una sessione per la gestione degli input flashati
        $session_manager = new SessionManager($session_config, $this->container);
        $this->container->registerLazy(Session::class, function() use ($session_manager) {
            return new Session($session_manager->driver());;
        });


        $this->redirect = $this->container->getLazy(Redirect::class);
    }

    public function testMake()
    {
        $result = $this->redirect->make('/home', 302, ['X-Test' => 'HeaderValue']);

        $this->assertInstanceOf(Redirect::class, $result);
        $this->assertEquals('/home', $result->get_url());
        $this->assertEquals(302, $result->get_status());
        $this->assertEquals('HeaderValue', $result->get_headers()['X-Test']);
    }

    public function testTo()
    {
        $result = $this->redirect->to('/dashboard', 301, ['X-Redirect' => 'Yes']);
        
        $this->assertInstanceOf(Redirect::class, $result);
        $this->assertEquals('http://localhost/dashboard', $result->get_url());
        $this->assertEquals(301, $result->get_status());
        $this->assertEquals('Yes', $result->get_headers()['X-Redirect']);
    }

    public function testRoute()
    {        
        $result = $this->redirect->route('home', [], 302, ['X-Custom' => 'RouteHeader']);
        
        $this->assertInstanceOf(Redirect::class, $result);
        $this->assertEquals('http://localhost/', $result->get_url());
        $this->assertEquals(302, $result->get_status());
        $this->assertEquals('RouteHeader', $result->get_headers()['X-Custom']);
    }

    public function testRouteParam()
    {        
        $result = $this->redirect->route('post.show', ['id' => 1], 302)->with_header('X-Custom', 'RouteHeader');
        
        $this->assertInstanceOf(Redirect::class, $result);
        $this->assertEquals('http://localhost/post/1', $result->get_url());
        $this->assertEquals(302, $result->get_status());
        $this->assertEquals('RouteHeader', $result->get_headers()['X-Custom']);
    }

    public function testRouteMulParam()
    {        
        $result = $this->redirect->route('post.show', ["bella" => "bro", 'id' => 1], 302)->with_header('X-Custom', 'RouteHeader');
        
        $this->assertInstanceOf(Redirect::class, $result);
        $this->assertEquals('http://localhost/post/1?bella=bro', $result->get_url());
        $this->assertEquals(302, $result->get_status());
        $this->assertEquals('RouteHeader', $result->get_headers()['X-Custom']);

        $result = $this->redirect->route('post.show', ['id' => 1, "bella" => "bro"], 302)->with_header('X-Custom', 'RouteHeader');
        $this->assertInstanceOf(Redirect::class, $result);
        $this->assertEquals('http://localhost/post/1?bella=bro', $result->get_url());
        $this->assertEquals(302, $result->get_status());
        $this->assertEquals('RouteHeader', $result->get_headers()['X-Custom']);
    }


    public function testAction()
    {
        $result = $this->redirect->action('PostController@show', ['id' => 1, "ciao" => 'bro'], 302, ['X-Action' => 'ActionHeader']);
        
        $this->assertInstanceOf(Redirect::class, $result);
        $this->assertEquals('http://localhost/post/1?ciao=bro', $result->get_url());
        $this->assertEquals(302, $result->get_status());
        $this->assertEquals('ActionHeader', $result->get_headers()['X-Action']);
    }


    public function testBack()
    {
        $_SERVER['HTTP_REFERER'] = 'http://localhost/previous';
        $result = $this->redirect->back(302, ['X-Back' => 'Yes']);
        
        $this->assertInstanceOf(Redirect::class, $result);
        $this->assertEquals('http://localhost/previous', $result->get_url());
        $this->assertEquals(302, $result->get_status());
        $this->assertEquals('Yes', $result->get_headers()['X-Back']);
    }


    public function testBackWithFallback()
    {
        unset($_SERVER['HTTP_REFERER']);
        $result = $this->redirect->back(302, [], '/fallback');

        $this->assertInstanceOf(Redirect::class, $result);
        $this->assertEquals('http://localhost/fallback', $result->get_url());
        $this->assertEquals(302, $result->get_status());
    }

    public function testWithHeader()
    {
        $result = $this->redirect->to('/home')
            ->with_header('X-Custom-Header', 'HeaderValue');
        
        $this->assertEquals('HeaderValue', $result->get_headers()['X-Custom-Header']);
    }

    public function testWithInput()
    {
        $result = $this->redirect->to('/login')->with_input(['username' => 'John']);

        $this->assertTrue(session()->has('inputs'));
        $this->assertEquals('John', session()->get('inputs')['username']);
    }

    public function testRedirectToHome()
    {
        // Simula la richiesta alla homepage
        $request = Request::create('/', 'GET');
        
        // Simula la risposta di reindirizzamento alla homepage
        $response = redirect('/');
        $this->assertInstanceOf(Redirect::class, $response);
        $this->assertEquals('http://localhost/', $response->get_url());
    }

    public function testWithInputMultipleValues()
    {
        $result = $this->redirect->to('/login')->with_input(['username' => 'John', 'email' => 'john@example.com']);

        $this->assertTrue(session()->has('inputs'));
        $inputs = session()->get('inputs');
        $this->assertEquals('John', $inputs['username']);
        $this->assertEquals('john@example.com', $inputs['email']);
    }

    public function testInvalidPathThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Path must be set.');
        
        $this->redirect->to('');
    }

    public function testInvalidRouteThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Route must be set.');
        
        $this->redirect->route('');
    }

    public function testInvalidRouteThrowsException2()
    {
        $route = 'noexist';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Route '{$route}' not found.");
        
        $this->redirect->route($route);
    }

    public function testInvalidActionThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Action must be set.');
        
        $this->redirect->action('');
    }

    public function testInvalidActionThrowsException2()
    {
        $action = 'PostController@visit';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Action '{$action}' not found.");
        
        $this->redirect->action($action);
    }

    public function testBackWithoutRefererUsesFallback()
    {
        unset($_SERVER['HTTP_REFERER']);
        
        $result = $this->redirect->back(302, [], '/fallback');

        $this->assertEquals('http://localhost/fallback', $result->get_url());
    }

    public function testHeadersOverride()
    {
        $result = $this->redirect->to('/home')
            ->with_header('X-Test', 'Value1')
            ->with_header('X-Test', 'Value2');
        
        $this->assertEquals('Value2', $result->get_headers()['X-Test']);
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
        $response = $this->redirect->back()->with_input(['username' => 'wronguser']);
        
        // Verifica che sia un RedirectResponse
        $this->assertInstanceOf(Redirect::class, $response);
        
        // Verifica che il redirect punti indietro
        $this->assertEquals('http://localhost/', $response->get_url());

        // Verifica che l'input sia stato flashato nella sessione
        $session = $this->container->getLazy(Session::class);
        $this->assertEquals('wronguser', $session->get('inputs')['username']);
    }


}
