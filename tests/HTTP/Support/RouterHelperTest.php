<?php

use PHPUnit\Framework\TestCase;

use App\HTTP\Router;
use App\Core\ServiceContainer;
use App\HTTP\Support\UrlGenerator;
use App\HTTP\Support\RouterHelper;
use App\Facades\BaseFacade;

class RouterHelperTest extends TestCase
{
    protected $router;
    protected $routerHelper;
    protected $container;

    protected function setUp(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost';

        $this->container = new ServiceContainer();
        BaseFacade::set_container($this->container);
        $this->router = new Router($this->container);

        // Simulate route loading
        $this->router->load_routes([
            __DIR__ . '/../../routes/app.php',
            __DIR__ . '/../../routes/auth.php',
            __DIR__ . '/../../routes/admin.php',
            __DIR__ . '/../../routes/user.php',
            __DIR__ . '/../../routes/post.php'
        ]);

        $this->routerHelper = new RouterHelper($this->router);

        $this->container->register(Router::class, $this->router);
        $this->container->register(RouterHelper::class, $this->routerHelper);
    }

    public function testRouteIsExactMatch()
    {
        $this->assertTrue($this->routerHelper->route_is('user.*'));
        $this->assertTrue($this->routerHelper->route_is('admin.*'));
    }

    public function testRouteIsWithWildcard()
    {
        $this->assertFalse($this->routerHelper->route_is('user.*.edit'));
        $this->assertTrue($this->routerHelper->route_is('post.*'));
        $this->assertFalse($this->routerHelper->route_is('product.*'));
    }

    public function testRouteIsWithComplexPattern()
    {
        $this->assertTrue($this->routerHelper->route_is('admin.*.[a-z]+[0-9]?'));
        $this->assertFalse($this->routerHelper->route_is('admin.*.[A-Z]+[0-9]?'));
    }

    public function testGetRouteUriForExistingRoutn()
    {
        $result = $this->routerHelper->get_route_uri('user.show');
        $this->assertEquals('/user/{id}', $result);

        $result = $this->routerHelper->get_route_uri('post.show');
        $this->assertEquals('/post/{id}', $result);
    }

    public function testGetRouteUriForNonExistingRoutn()
    {
        $result = $this->routerHelper->get_route_uri('non.existing.route');
        $this->assertNull($result);
    }

    public function testGetActionUriForExistingAction()
    {
        $result = $this->routerHelper->get_action_uri('UserController@show');
        $this->assertEquals('/user/{id}', $result);

        $result = $this->routerHelper->get_action_uri('PostController@show');
        $this->assertEquals('/post/{id}', $result);
    }

    public function testGetActionUriForNonExistingAction()
    {
        $result = $this->routerHelper->get_action_uri('NonExistingController@action');
        $this->assertNull($result);
    }

    public function testGetCurrentRouteNameForExactMatch()
    {
        $_SERVER['REQUEST_URI'] = '/user/1';
        $this->assertEquals('user.show', $this->routerHelper->get_curr_route_name());
    }

    public function testGetCurrentRouteNameForExactMatch2()
    {
        $_SERVER['REQUEST_URI'] = '/post/my-first-post';
        $this->assertEquals('post.show', $this->routerHelper->get_curr_route_name());
    }

    public function testGetCurrentRouteNameForRoot()
    {
        $_SERVER['REQUEST_URI'] = '/';
        $this->assertEquals('home', $this->routerHelper->get_curr_route_name());
    }

    public function testGetCurrentRouteNameForNonExistentUri()
    {
        $_SERVER['REQUEST_URI'] = '/non-existing-path';
        $this->assertNull($this->routerHelper->get_curr_route_name());
    }

    public function testRouteIsWithComplexRegexPatterns()
    {
        // $this->assertTrue($this->routerHelper->route_is('post.*.[A-Za-z0-9_-]*'));
        $this->assertFalse($this->routerHelper->route_is('post.[0-9]*'));
    }

    public function testRouteWithMultipleParameters()
    {
        // Simulate a route with multiple parameters
        $this->router->get_routes('/post/{id}/comment/{comment_id}');

        // $this->assertTrue($this->routerHelper->route_is('post.comment.*'));
        $this->assertFalse($this->routerHelper->route_is('post.comment.create'));
    }

    public function testEdgeCasesWithEmptyRouteName()
    {
        $this->assertFalse($this->routerHelper->route_is(''));
        $this->assertNull($this->routerHelper->get_route_uri(''));
        $this->assertNull($this->routerHelper->get_action_uri(''));
    }

    public function testRouteIsCaseInsensitive()
    {
        $this->assertFalse($this->routerHelper->route_is('USER.*'));
        $this->assertFalse($this->routerHelper->route_is('post.SHOW'));
        $this->assertFalse($this->routerHelper->route_is('ADMIN.INDEX'));
    }

    public function testRouteWithSpecialCharacters()
    {
        // Simulate a route with special characters in the name
        $this->router->get_routes('/user/{id}/edit');

        // $this->assertTrue($this->routerHelper->route_is('user.edit.special-@!#'));
        $this->assertFalse($this->routerHelper->route_is('user.edit.wrong'));
    }

    public function testMissingRequiredParameters()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Missing required parameter: 'id' for route.");

        UrlGenerator::route('user.show', []);
    }

    public function testRouteWithExtraParameters()
    {
        $result = UrlGenerator::route('user.show', ['id' => 1, 'extra' => 'data']);
        $this->assertEquals('http://localhost/user/1?extra=data', $result);
    }

    public function testRouteWithMissingPlaceholders()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Missing required parameter: 'id' for route.");

        UrlGenerator::route('post.show', []);
    }
}
