<?php

namespace Tests\Moo;

use PHPUnit\Framework\TestCase;
use Moo\Moo;
use Moo\Response;
use Moo\Request;
use Moo\Router;
use Moo\Template;

function array_exclude(array $excluded, array $source)
{
    $result = [];
    foreach ($source as $element) {
        if (!in_array($element, $excluded)) {
            array_push($result, $element);
        }
    }
    return $result;
}

class ClassyMooMock extends Moo
{
    public int $state = 0;

    public function __construct()
    {
        parent::__construct();
        $this->get('/', [$this, 'index']);
        $this->post('/test/(\d+)', [$this, 'test']);
    }

    public function before()
    {
        $this->state++;
    }

    public function after()
    {
        $this->state++;
    }

    public function flush()
    {
        // Keep it empty.
    }

    public function error(\Exception $exc)
    {
        $this->state = -1;
        $this->response = new Response([
            'code' => 404,
            'message' => 'Not Found',
            'body' => 'error'
        ]);
    }

    public function index()
    {
        return 'hello world';
    }

    public function test($x)
    {
        $this->response->code = 202;
        return $x;
    }
}

class MooTest extends TestCase
{
    const METHODS = ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH', 'GET'];

    public function testBeforeAfter()
    {
        $test = 0;
        $moo = new Moo();
        $moo->flush = null;
        $moo->before =function () use (&$test) {
            $test++;
        };
        $moo->after = function () use (&$test) {
            $test++;
        };
        $moo->route('/', function () { return 'xxx'; });
        $moo();
        $this->assertEquals(2, $test);
    }

    public function testCustomError()
    {
        $test = 0;
        $moo = new Moo();
        $moo->before = function () use(&$test) {
            $test++;
            throw new \RuntimeException("test");
        };
        $moo->error = function(\Exception $exc) use(&$test) {
            $test++;
        };
        $moo();
        $this->assertEquals(2, $test);
    }
    
    public function testFlush()
    {
        $moo = new Moo();
        $moo->error = function () {};
        $moo->flush = function () use ($moo) { return strtoupper($moo->response->body); };
        $moo->route('/', function () { return 'hello'; });
        $content = $moo();
        $this->assertEquals("HELLO", $content);
    }

    public function testPluginAndState()
    {
        $moo = new Moo();
        $moo->state = 0;
        $moo->plugin = function ($x) use ($moo) {
            $moo->state = $x;
            return $x;
        };
        $y = $moo->plugin(123);
        $this->assertEquals(123, $y);
        $this->assertEquals(123, $moo->state);
    }

    public function testPluginException()
    {
        $this->expectException(\BadMethodCallException::class);
        $moo = new Moo();
        $moo->nonExistentPlugin();
    }

    public function testPluginTypeException()
    {
        $this->expectException(\BadMethodCallException::class);
        $moo = new Moo();
        $moo->plugin = 123;
        $moo->plugin();
    }

    public function testIndex()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->route('/', function () {
            return 'this is index';
        });
        $moo();
        $this->assertEquals(200, $moo->response->code);
        $this->assertEquals('OK', $moo->response->message);
        $this->assertEquals('this is index', $moo->response->body);
    }

    public function testNotFound()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo(new Request(['uri' => '/dfhsdhfg']));
        $this->assertEquals(404, $moo->response->code);
        $this->assertEquals('Not Found', $moo->response->message);
        $this->assertEquals('Not Found', $moo->response->body);
    }

    public function testRoute()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->route('/', function () {
            return 'ok';
        });
        $moo();
        $this->assertRouteFound($moo, self::METHODS, ['/', '//', '///'], 'ok');
        $this->assertRouteNotFound($moo, self::METHODS, ['/foobar']);
    }

    public function testRouteOverride()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->route('/', function () {
            return 1;
        });
        $moo->route('/', function () {
            return 2;
        });
        $moo->route('/', function () {
            return 3;
        });
        $moo();
        $this->assertEquals(3, $moo->response->body);
        $this->assertEquals(1, $moo->router->routes->count());
    }

    public function testRouteParameters()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->route('/', function () {
            return 'index';
        });
        $moo->route('/test', function () {
            return 'test';
        });
        $moo->route('/test/(\d+)', function ($x) {
            return $x;
        });
        $moo->route('/test/(\d+)/test', function ($x) {
            return 'test 2';
        });
        $moo->route('/test/([a-z]+)/test', function ($x) {
            return 'test 3 '.$x;
        });
        $moo();
        $this->assertEquals('index', $moo->response->body);
        $moo(new Request(['uri' => '/test']));
        $this->assertEquals('test', $moo->response->body);
        $moo(new Request(['uri' => '/test/123']));
        $this->assertEquals('123', $moo->response->body);
        $moo(new Request(['uri' => '/test/123/test']));
        $this->assertEquals('test 2', $moo->response->body);
        $moo(new Request(['uri' => '/test/ok/test']));
        $this->assertEquals('test 3 ok', $moo->response->body);
        $moo(new Request(['uri' => '/test/ABC/test']));
        $this->assertEquals(404, $moo->response->code);
        $this->assertEquals('Not Found', $moo->response->message);
        $this->assertEquals('Not Found', $moo->response->body);
    }

    public function testMultiRoute()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->router->register(['GET', 'POST'], ['/foo/(\d+)', '/bar/(\d+)'], function ($x) {
            return $x;
        });
        $this->assertRouteFound($moo, ['GET', 'POST'], ['/foo/123', '/bar/123'], '123');
        $this->assertRouteNotFound($moo, array_exclude(['GET', 'POST'], self::METHODS), ['/foo', '/bar']);
        $this->assertRouteNotFound($moo, self::METHODS, ['/', '/foo/abc', '/bar/xyz']);        
    }

    public function testGet()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->get('/', function () {
            return 'ok';
        });
        $moo();
        $this->assertRouteFound($moo, ['GET'], ['/'], 'ok');
        $this->assertRouteNotFound($moo, array_exclude(['GET'], self::METHODS), ['/']);
    }

    public function testHead()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->head('/', function () {
            return 'ok';
        });
        $moo();
        $this->assertRouteFound($moo, ['HEAD'], ['/'], 'ok');
        $this->assertRouteNotFound($moo, array_exclude(['HEAD'], self::METHODS), ['/']);
    }

    public function testPost()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->post('/', function () {
            return 'ok';
        });
        $moo();
        $this->assertRouteFound($moo, ['POST'], ['/'], 'ok');
        $this->assertRouteNotFound($moo, array_exclude(['POST'], self::METHODS), ['/']);
    }

    public function testPut()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->put('/', function () {
            return 'ok';
        });
        $moo();
        $this->assertRouteFound($moo, ['PUT'], ['/'], 'ok');
        $this->assertRouteNotFound($moo, array_exclude(['PUT'], self::METHODS), ['/']);
    }

    public function testDelete()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->delete('/', function () {
            return 'ok';
        });
        $moo();
        $this->assertRouteFound($moo, ['DELETE'], ['/'], 'ok');
        $this->assertRouteNotFound($moo, array_exclude(['DELETE'], self::METHODS), ['/']);
    }

    public function testConnect()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->connect('/', function () {
            return 'ok';
        });
        $moo();
        $this->assertRouteFound($moo, ['CONNECT'], ['/'], 'ok');
        $this->assertRouteNotFound($moo, array_exclude(['CONNECT'], self::METHODS), ['/']);
    }

    public function testOptions()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->options('/', function () {
            return 'ok';
        });
        $moo();
        $this->assertRouteFound($moo, ['OPTIONS'], ['/'], 'ok');
        $this->assertRouteNotFound($moo, array_exclude(['OPTIONS'], self::METHODS), ['/']);
    }

    public function testTrace()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->trace('/', function () {
            return 'ok';
        });
        $moo();
        $this->assertRouteFound($moo, ['TRACE'], ['/'], 'ok');
        $this->assertRouteNotFound($moo, array_exclude(['TRACE'], self::METHODS), ['/']);
    }

    public function testPatch()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->patch('/', function () {
            return 'ok';
        });
        $moo();
        $this->assertRouteFound($moo, ['PATCH'], ['/'], 'ok');
        $this->assertRouteNotFound($moo, array_exclude(['PATCH'], self::METHODS), ['/']);
    }

    public function testCallableReturnValueOverrideBody()
    {
        $moo = new Moo();
        $moo->flush = null;
        $moo->get('/test1', function () use ($moo) {
            $moo->response->body = 123;
        });
        $moo->get('/test2', function () use ($moo) {
            $moo->response->body = 456;
            return 'xyz';
        });

        $moo(new Request(['uri' => '/test1']));
        $this->assertEquals(123, $moo->response->body);

        $moo(new Request(['uri' => '/test2']));
        $this->assertEquals('xyz', $moo->response->body);
    }

    public function testClassyMoo()
    {
        $app = new ClassyMooMock();

        $app(new Request());
        $this->assertEquals(200, $app->response->code);
        $this->assertEquals('hello world', $app->response->body);
        $this->assertEquals(2, $app->state);

        $app(new Request(['method' => 'POST', 'uri' => '/test/123']));
        $this->assertEquals(202, $app->response->code);
        $this->assertEquals('123', $app->response->body);
        $this->assertEquals(4, $app->state);

        $app(new Request(['method' => 'GET', 'uri' => '/test/123']));
        $this->assertEquals(404, $app->response->code);
        $this->assertEquals('error', $app->response->body);
        $this->assertEquals(-1, $app->state);
    }

    public function testNestedMoo()
    {
        $mooA = new Moo();
        $mooA->get('/a/test', function () {
            return 'test a';
        });

        $mooB = new Moo();
        $mooB->get('/b/test', function () {
            return 'test b';
        });

        $moo = new Moo();
        $moo->flush = null;
        $moo->route('/a/.*', $mooA);
        $moo->route('/b/.*', $mooB);

        $moo(new Request());
        $this->assertEquals(404, $moo->response->code);

        $moo(new Request(['uri' => '/a/']));
        $this->assertEquals(404, $moo->response->code);

        $moo(new Request(['uri' => '/a/xxx']));
        $this->assertEquals(404, $moo->response->code);

        $moo(new Request(['uri' => '/a/test']));
        $this->assertEquals(200, $moo->response->code);
        $this->assertEquals('test a', $moo->response->body);

        $moo(new Request(['uri' => '/b/']));
        $this->assertEquals(404, $moo->response->code);

        $moo(new Request(['uri' => '/b/xxx']));
        $this->assertEquals(404, $moo->response->code);

        $moo(new Request(['uri' => '/b/test']));
        $this->assertEquals(200, $moo->response->code);
        $this->assertEquals('test b', $moo->response->body);
    }

    public function testTemplateErrors()
    {
        $moo = new Moo();
        $moo->flush = null;

        $moo->template = function (string $script) use ($moo) {
            return (new Template(__DIR__ . '/templates'))->render($script);
        };

        $moo->get('/', function () use ($moo) {
            return $moo->template('dummy.phtml');
        });

        $moo->get('/test3', function () use ($moo) {
            return $moo->template('test3.phtml');
        });

        $moo();
        $this->assertEquals(500, $moo->response->code);

        $moo(new Request(['uri' => '/test3']));
        $this->assertEquals(500, $moo->response->code);
    }

    public function testTemplateLoadWithPartial()
    {
        $moo = new Moo();
        $moo->flush = null;

        $moo->template = function (string $script) use ($moo) {
            return (new Template(__DIR__ . '/templates'))->render($script);
        };

        $moo->get('/', function () use ($moo) {
            return $moo->template('test.phtml');
        });

        $moo();
        $this->assertEquals(200, $moo->response->code);
        $this->assertStringContainsString('test partial', $moo->response->body);
    }

    public function testTemplateContextAndPlugin()
    {
        $moo = new Moo();
        $moo->flush = null;

        $moo->template = function (string $script, array $context = []) use ($moo) {
            $template = new Template(__DIR__ . '/templates', [
                'global' => 'test'
            ]);
            $template->plugin = function ($x) { return $x; };
            return $template->render($script, $context);
        };

        $moo->get('/', function () use ($moo) {
            return $moo->template('test2.phtml', [
                'local' => 'ok',
                'var' => 123
            ]);
        });

        $moo();
        $this->assertEquals(200, $moo->response->code);
        $this->assertStringContainsString('test ok 123', $moo->response->body);
    }

    /**
     * Keep this test last because it overrides values of global variables.
     */
    public function testRequestFactory()
    {
        global $_SERVER;
        global $_GET;
        global $_POST;
        global $_FILES;

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test?xxx=123';
        $_GET['xxx'] = 123;
        $_POST['yyy'] = 456;
        $_FILES['zzz'] = ['name' => 'foo.bar'];

        $moo = new Moo();
        $moo->flush = null;
        $moo->post('/test', function () {
            return 'test';
        });

        $request = Router::requestFactory();
        $this->assertEquals('POST', $request->method);
        $this->assertEquals('/test?xxx=123', $request->uri);
        $this->assertEquals(1, $request->query->count());
        $this->assertEquals(123, $request->query->xxx);
        $this->assertEquals(1, $request->post->count());
        $this->assertEquals(456, $request->post->yyy);
        $this->assertEquals(1, $request->files->count());
        $this->assertEquals('foo.bar', $request->files->zzz['name']);

        $moo($request);
        $this->assertEquals(200, $moo->response->code);
        $this->assertEquals('OK', $moo->response->message);
        $this->assertEquals('test', $moo->response->body);
    }

    protected function assertRouteFound(Moo $moo, array $methods, array $uris, string $expected)
    {
        foreach ($methods as $method) {
            foreach ($uris as $uri) {
                $moo(new Request(['method' => $method, 'uri' => $uri]));
                $this->assertEquals($expected, $moo->response->body);
            }
        }
    }

    protected function assertRouteNotFound(Moo $moo, array $methods, array $uris)
    {
        foreach ($methods as $method) {
            foreach ($uris as $uri) {
                $moo(new Request(['method' => $method, 'uri' => $uri]));
                $this->assertEquals(404, $moo->response->code);
                $this->assertEquals('Not Found', $moo->response->message);
                $this->assertEquals('Not Found', $moo->response->body);
            }
        }
    }
}
