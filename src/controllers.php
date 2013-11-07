<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html');
})
->bind('homepage')
;

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    $page = 404 == $code ? '404.html' : '500.html';

    return new Response($app['twig']->render($page, array('code' => $code)), $code);
});

$app->get('/venues', function () use ($app) {
    $m = new MongoClient();
    $c = $m->silex->venues;

    return $app['twig']->render('venue/list.html', array(
        'venues' => iterator_to_array($c->find()),
    ));
})
->bind('venues')
;

$app->get('/venue/{id}', function ($id) use ($app) {
    $m = new MongoClient();
    $c = $m->silex->venues;

    $venue = $c->findOne(array('_id' => new MongoId($id)));

    if ($venue === null) {
        throw new NotFoundHttpException(sprintf('Venue %s does not exist', $id));
    }

    return $app['twig']->render('venue/show.html', array(
        'venue' => $venue,
    ));
})
->assert('id', '^[0-9a-f]{24}$')
->bind('venue')
;

$app->get('/oauth/request', function () use ($app) {
    $code = $app['tmhoauth']->apponly_request(array(
        'without_bearer' => true,
        'method' => 'POST',
        'url' => $app['tmhoauth']->url('oauth/request_token', ''),
        'params' => array(
            'oauth_callback' => $app['url_generator']->generate('oauth_access', array(), true),
        ),
    ));

    if ($code != 200) {
        return $app['twig']->render('oauth/error.html', array(
            'message' => 'There was an error communicating with Twitter.',
            'resp' => $app['tmhoauth']->response['response'],
        ));
    }

    $oauth = $app['tmhoauth']->extract_params($app['tmhoauth']->response['response']);
    $app['session']->set('oauth', $oauth);

    if ($oauth['oauth_callback_confirmed'] === 'true') {
        $url = $app['tmhoauth']->url('oauth/authorize', '') . '?oauth_token=' . $oauth['oauth_token'];

        return $app['twig']->render('oauth/goto.html', array('url' => $url));
    }

    return $app['twig']->render('oauth/error.html', array(
        'message' => 'The callback was not confirmed by Twitter so we cannot continue.',
    ));
})
->bind('oauth_request')
;

$app->get('/oauth/access', function (Request $request) use ($app) {
    $oauth = $app['session']->get('oauth');

    if ($request->get('oauth_token') !== $oauth['oauth_token']) {
        $app['session']->invalidate();

        return $app['twig']->render('oauth/error.html', array(
            'message' => 'The oauth token you started with doesn\'t match the one you\'ve been redirected with. Do you have multiple tabs open?',
        ));
    }

    if ( ! $request->get('oauth_token')) {
        $app['session']->invalidate();

        return $app['twig']->render('oauth/error.html', array(
            'message' => 'The oauth verifier is missing so we cannot continue. Did you deny the appliction access?',
        ));
    }

    $app['tmhoauth']->reconfigure(array_merge($app['tmhoauth']->config, array(
        'token' => $oauth['oauth_token'],
        'secret' => $oauth['oauth_token_secret'],
    )));

    $code = $app['tmhoauth']->user_request(array(
        'method' => 'POST',
        'url' => $app['tmhoauth']->url('oauth/access_token', ''),
        'params' => array(
            'oauth_verifier' => trim($request->get('oauth_verifier')),
        ),
    ));

    if ($code == 200) {
        $credentials = $app['tmhoauth']->extract_params($app['tmhoauth']->response['response']);

        return $app['twig']->render('oauth/success.html', array(
            'message' => 'Successfully authenticated!',
            'screen_name' => $credentials['screen_name'],
            'user_token' => $credentials['oauth_token'],
            'user_secret' => $credentials['oauth_token_secret'],
        ));
    }

    return $app['twig']->render('oauth/error.html', array(
        'message' => 'The oauth access token could not be redeeemd.',
    ));
})
->bind('oauth_access')
;
