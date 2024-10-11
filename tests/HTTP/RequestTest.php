<?php

use App\Core\ServiceContainer;
use PHPUnit\Framework\TestCase;
use App\Http\Request;
use App\HTTP\Router;

class RequestTest extends TestCase
{
    protected Request $request;

    protected function setUp(): void
    {
        // Simula le variabili globali per ogni test
        $_GET = [
            'name' => 'John',
            'products' => [
                ['name' => 'Laptop'],
                ['name' => 'Phone'],
            ],
            'empty' => '',
            'query' => 'value'
        ];
        $_POST = ['age' => 30, 'admin' => true];
        $_COOKIE = ['session' => 'abc123'];
        $_FILES = [
            'photo' => [
                'name' => 'photo.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/php12345',
                'error' => 0,
                'size' => 123456,
            ]
        ];
        $_SERVER = [
            'HTTP_HOST' => 'localhost',
            'REQUEST_URI' => '/user/profile',
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '192.168.1.1, 127.0.0.1',
            'HTTPS' => 'on',
            'HTTP_AUTHORIZATION' => 'Bearer testtoken123',
            'HTTP_ACCEPT' => 'text/html,application/json',
        ];

        // Crea l'istanza della richiesta
        $this->request = new Request();
        
        $container = ServiceContainer::get_container();
        $router = new Router($container);
        $router->load_routes([
            __DIR__ . '/../../routes/app.php',
            __DIR__ . '/../../routes/auth.php',
            __DIR__ . '/../../routes/admin.php',
            __DIR__ . '/../../routes/user.php',
            __DIR__ . '/../../routes/post.php'
        ]);

        $container->register(Router::class, $router);
    }

    public function test_path()
    {
        $this->assertEquals('user/profile', $this->request->path());
    }

    public function test_is()
    {
        $this->assertTrue($this->request->is('user/*'));
        $this->assertFalse($this->request->is('admin/*'));
    }

    public function test_route_is()
    {
        $this->assertTrue($this->request->route_is('user.*'));
        $this->assertTrue($this->request->route_is('admin.*'));
        $this->assertTrue($this->request->route_is('admin.*.create'));
        $this->assertTrue($this->request->route_is('admin.posts.*'));
        $this->assertTrue($this->request->route_is('post.*'));
        $this->assertFalse($this->request->route_is('nonexistent.*'));
        $this->assertFalse($this->request->route_is('admin.posts.*.nonexistent'));
        $this->assertFalse($this->request->route_is('admin.posts.nonexistent'));
        $this->assertFalse($this->request->route_is('admin.posts.nonexistent.*'));
        $this->assertFalse($this->request->route_is('admin.posts.nonexistent.*.create'));
        $this->assertFalse($this->request->route_is('admin.posts.nonexistent.create'));
    }

    public function test_url()
    {
        $this->assertEquals('https://localhost/user/profile', $this->request->url());
    }

    public function test_full_url_with_query()
    {
        // crea variabile prendendo parametro da get
        $get = $this->request->get();
        $url = $this->request->url();
        $expected = $url . '?' .http_build_query($get + ['type' => 'phone']);

        $this->assertEquals(
            $expected,
            $this->request->full_url_with_query(['type' => 'phone'])
        );
    }

    public function test_full_url_without_query()
    {
        $get = $this->request->get();
        $url = $this->request->url();
        $expected = $url . '?' . http_build_query(array_diff_key($get, array_flip(['query'])));
        $this->assertEquals(
            $expected,
            $this->request->full_url_without_query(['query'])
        );
    }

    public function test_host()
    {
        $this->assertEquals('localhost', $this->request->host());
    }

    public function test_http_host()
    {
        $this->assertEquals('localhost:', $this->request->http_host());
    }

    public function test_scheme_and_http_host()
    {
        $this->assertEquals('https://localhost', $this->request->scheme_and_http_host());
    }

    public function test_method()
    {
        $this->assertEquals('POST', $this->request->method());
    }

    public function test_is_method()
    {
        $this->assertTrue($this->request->is_method('POST'));
        $this->assertFalse($this->request->is_method('GET'));
    }

    public function test_header()
    {
        $this->assertEquals('XMLHttpRequest', $this->request->header('X-Requested-With'));
        $this->assertEquals('default', $this->request->header('Non-Existing-Header', 'default'));
    }

    public function test_has_header()
    {
        $this->assertTrue($this->request->has_header('X-Requested-With'));
        $this->assertFalse($this->request->has_header('Non-Existing-Header'));
    }

    public function test_bearer_token()
    {
        $this->assertEquals('testtoken123', $this->request->bearer_token());
    }

    public function test_ip()
    {
        $this->assertEquals('127.0.0.1', $this->request->ip());
    }

    public function test_ips()
    {
        $this->assertEquals(['192.168.1.1', '127.0.0.1'], $this->request->ips());
    }

    public function test_accepts()
    {
        $this->assertTrue($this->request->accepts(['application/json']));
        $this->assertFalse($this->request->accepts(['image/png']));
    }

    public function test_expects_json()
    {
        $this->assertTrue($this->request->expects_json());
    }

    public function test_all()
    {
        $this->assertEquals(
            ['name' => 'John', 'products' => [['name' => 'Laptop'], ['name' => 'Phone']], 'empty' => '', 'age' => 30, 'admin' => true, 'query' => 'value'],
            $this->request->all()
        );
    }

    public function test_input()
    {
        $this->assertEquals('John', $this->request->input('name'));
        $this->assertEquals('default', $this->request->input('nonexistent', 'default'));
        $this->assertEquals('Laptop', $this->request->input('products.0.name'));
    }

    public function test_query()
    {
        $this->assertEquals('John', $this->request->query('name'));
        $this->assertEquals(['name' => 'John', 'products' => [['name' => 'Laptop'], ['name' => 'Phone']], 'empty' => '', 'query' => 'value'], $this->request->query());
    }

    public function test_string()
    {
        $this->assertEquals('John', $this->request->string('name'));
    }

    public function test_integer()
    {
        $this->assertEquals(30, $this->request->integer('age'));
    }

    public function test_boolean()
    {
        $this->assertTrue($this->request->boolean('admin'));
        $this->assertFalse($this->request->boolean('nonexistent'));
    }

    public function test_date()
    {
        $date = date('Y-m-d-00:00:00');
        $this->assertEquals($date, $this->request->date('nonexistent', 'Y-m-d-00:00:00', 'UTC'));
    }

    public function test_only()
    {
        $this->assertEquals(['name' => 'John', 'age' => 30], $this->request->only(['name', 'age']));
    }

    public function test_except()
    {
        $this->assertEquals(['products' => [['name' => 'Laptop'], ['name' => 'Phone']], 'empty' => '', 'admin' => true, 'query' => 'value'], $this->request->except(['name', 'age']));
    }

    public function test_has()
    {
        $this->assertTrue($this->request->has('name'));
        $this->assertFalse($this->request->has('nonexistent'));
    }

    public function test_has_any()
    {
        $this->assertTrue($this->request->has_any(['name', 'nonexistent']));
        $this->assertFalse($this->request->has_any(['nonexistent1', 'nonexistent2']));
    }

    public function test_filled()
    {
        $this->assertTrue($this->request->filled('name'));
        $this->assertFalse($this->request->filled('empty'));
    }

    public function test_merge()
    {
        $this->request->merge(['new_key' => 'new_value']);
        $this->assertEquals('new_value', $this->request->input('new_key'));
    }

    public function test_merge_if_missing()
    {
        $this->request->merge_if_missing(['name' => 'Jane']);
        $this->assertEquals('John', $this->request->input('name')); // Existing value should not be overwritten
    }

    public function test_cookie()
    {
        $this->assertEquals('abc123', $this->request->cookie('session'));
        $this->assertEquals('default', $this->request->cookie('nonexistent', 'default'));
    }

    public function test_file()
    {
        $this->assertEquals('photo.jpg', $this->request->file('photo')['name']);
    }

    public function test_has_file()
    {
        $this->assertTrue($this->request->has_file('photo'));
        $this->assertFalse($this->request->has_file('nonexistent'));
    }
}
