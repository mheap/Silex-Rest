<?php

namespace mheap\Rest\Tests;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use mheap\Rest\ResponseFactory;
use mheap\Rest\XML\Parser;

class RestTest extends \PHPUnit_Framework_TestCase
{

    protected $app;

    public function setUp()
    {
        $app = new Application();
        $app->get('/', function() use($app) {
            return ResponseFactory::build($app['request'], "index", array(
                "user" => array(
                    "id" => 1,
                    "name" => "mheap"
                ),
                "developer" => true,
                "enjoysXML" => false,
                "devices" => 7,
                "height" => 1.8
            ));
        });

        $app->get('/error', function() use($app) {
            return ResponseFactory::build($app['request'], "error", array(
                "message" => "That email address is already in use",
                "code" => 400
            ), 400);
        });

        $app->get('/exception', function() use($app) {
            throw new \mheap\Rest\Exception("Exception Thrown", 500);
        });

        $app->error(function(\mheap\Rest\Exception $e) use ($app){
            return ResponseFactory::build($app['request'], "exception", array(
                "message" => $e->getMessage(),
                "code" => $e->getCode()
            ), $e->getCode());
        });

        $this->app = $app;
    }

    public function testDefaultResponseJSON()
    {
        $request = Request::create('/');
        $response = $this->app->handle($request);
        $this->assertEquals("application/json", $response->headers->get("content-type"));
    }

    public function testResponseJSON()
    {
        $request = Request::create('/');
        $request->setRequestFormat("application/json");
        $response = $this->app->handle($request);

        $this->assertEquals("application/json", $response->headers->get("content-type"));
    }

    public function testResponseXML()
    {
        $request = Request::create('/');
        $request->setRequestFormat("application/xml");
        $response = $this->app->handle($request);

        $this->assertEquals("application/xml", $response->headers->get("content-type"));
    }

    public function testParseJSONSuccess()
    {
        $request = Request::create('/');
        $request->setRequestFormat("application/json");
        $response = $this->app->handle($request);
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotNull($content->data);
        $this->assertEquals("mheap", $content->data->user->name);
        $this->assertEquals(true, $content->data->developer);
        $this->assertEquals(false, $content->data->enjoysXML);
        $this->assertEquals(7, $content->data->devices);
        $this->assertEquals(1.8, $content->data->height);
    }

    public function testParseXMLSuccess()
    {
        $request = Request::create('/');
        $request->setRequestFormat("application/xml");

        $response = $this->app->handle($request);
        $content = new \SimpleXMLElement($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotNull($content->data);
        $this->assertEquals("mheap", $content->data->user->name);
        $this->assertSame(true, (bool)(int) $content->data->developer);
        $this->assertSame(false, (bool)(int) $content->data->enjoysXML);
        $this->assertEquals(7, (int) $content->data->devices);
        $this->assertEquals(1.8, (double) $content->data->height);
    }

    public function testParseJSONError()
    {
        $request = Request::create('/error');
        $request->setRequestFormat("application/json");
        $response = $this->app->handle($request);
        $content = json_decode($response->getContent());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertNotNull($content->error);
        $this->assertEquals("That email address is already in use", $content->error->message);
        $this->assertEquals(400, $content->error->code);
    }

    public function testParseXMLError()
    {
        $request = Request::create('/error');
        $request->setRequestFormat("application/xml");
        $response = $this->app->handle($request);
        $content = new \SimpleXMLElement($response->getContent());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertNotNull($content->error);
        $this->assertEquals("That email address is already in use", $content->error->message);
        $this->assertEquals(400, (int) $content->error->code);
    }

    public function testExceptionContent()
    {
        $request = Request::create('/exception');
        $request->setRequestFormat("application/json");
        $response = $this->app->handle($request);
        $content = json_decode($response->getContent());

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertNotNull($content->error);
        $this->assertEquals("Exception Thrown", $content->error->message);
        $this->assertEquals(500, $content->error->code);
    }

}