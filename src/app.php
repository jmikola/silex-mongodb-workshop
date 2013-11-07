<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(__DIR__.'/../templates'),
    'twig.options' => array('cache' => __DIR__.'/../cache/twig'),
));
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    // add custom globals, filters, tags, ...

    $twig->addFunction(new Twig_SimpleFunction('gmap_url', function ($lng, $lat, $size) {
        $params = array(
            'zoom' => 15,
            'size' => $size,
            'markers' => $lat . ',' . $lng,
            'sensor' => 'false',
        );
        $url = '//maps.googleapis.com/maps/api/staticmap?';

        foreach ($params as $k => $v) {
            $url .= sprintf('%s=%s&', $k, rawurlencode($v));
        }

        return rtrim($url, '&');
    }));

    $twig->addFunction(new Twig_SimpleFunction('icon_url', function ($category, $size) {
        if ( ! in_array($size, array(32, 44, 64, 88))) {
            throw new InvalidArgumentException('Size is not supported');
        }

        if ( ! isset($category['icon']['prefix'], $category['icon']['suffix'])) {
            return null;
        }

        return sprintf('%s%d%s', $category['icon']['prefix'], $size, $category['icon']['suffix']);
    }));

    return $twig;
}));

$app->register(new TmhOAuthServiceProvider());

return $app;
