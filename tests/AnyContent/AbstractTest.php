<?php

namespace AnyContent\Service;

use AnyContent\Cache\CachingRepository;
use AnyContent\Client\Repository;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ApcuCache;
use Silex\Application;

use Silex\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTest extends WebTestCase
{

    /** @var  CachingRepository */
    protected $repository;


    public function createApplication()
    {
        $fs = new Filesystem();

        if ($fs->exists(APPLICATION_PATH . '/tmp/test')) {
            $fs->remove(APPLICATION_PATH . '/tmp/test');
        }

        $fs->mkdir(APPLICATION_PATH . '/tmp/test');
        $fs->mirror(APPLICATION_PATH . '/tests/resources/repository', APPLICATION_PATH . '/tmp/test/repository');

        $config         = [];
        $config['test'] = ['type' => "archive", 'folder' => APPLICATION_PATH . '/tmp/test/repository', 'files' => true];

        $cache = new ApcuCache();

        $app         = new Application();
        $app['acrs'] = new Service($app, $config, '/1/', Service::API_RESTLIKE_1,$cache);

        $this->repository = $app['acrs']->getRepository('test');

        $app['debug'] = true;
        unset($app['exception_handler']);



        return $app;
    }


    protected function getResponse($url, $code = 200, $params = [])
    {
        $client = $this->createClient();
        $client->request('GET', $url, $params);

        $response = $client->getResponse()->getContent();
        $this->assertEquals($code, $client->getResponse()->getStatusCode(), 'Wrong http Status code:');

        return $response;
    }


    protected function getJsonResponse($url, $code = 200, $params = [])
    {
        $client = $this->createClient();
        $client->request('GET', $url, $params);

        $response = $client->getResponse()->getContent();
        $this->assertEquals($code, $client->getResponse()->getStatusCode(), 'Wrong http Status code:');

        return json_decode($response, true);
    }


    protected function postJsonResponse($url, $code = 200, $params = [], $content = null)
    {
        $client = $this->createClient();
        $client->request('POST', $url, $params, [], [], $content);

        $response = $client->getResponse()->getContent();
        $this->assertEquals($code, $client->getResponse()->getStatusCode(), 'Wrong http Status code:');

        return json_decode($response, true);
    }


    protected function deleteJsonResponse($url, $code = 200, $params = [])
    {
        $client = $this->createClient();
        $client->request('DELETE', $url, $params);

        $response = $client->getResponse()->getContent();
        $this->assertEquals($code, $client->getResponse()->getStatusCode(), 'Wrong http Status code:');

        return json_decode($response, true);
    }
}