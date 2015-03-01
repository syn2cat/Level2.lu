<?php

  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;

  require_once __DIR__.'/bootstrap.php';

  date_default_timezone_set ( 'Europe/Luxembourg' );

  $app = new Silex\Application();

  $app->register(
    new Igorw\Silex\ConfigServiceProvider(
      __DIR__.'/config.json'
    )
  );

  $app->register( new Silex\Provider\DoctrineServiceProvider(),
    array( $app[ 'db.options' ] )
  );

  // Add header to allow access from everywhere
  $app->after( function ( Request $request, Response $response ) {
    $response->headers->set( 'Access-Control-Allow-Origin', '*' );
  });

  // Provides Twig template engine
  $app->register( new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
  ));

  $app->mount( '/', new level2\Level2ControllerProvider()  );

  $app->mount( '/events', new level2\EventsControllerProvider()  );

  $app->error(function (\Exception $e, $code) use ( $app ) {
    if ( $code  == 404 ) {
      return $app->redirect('/');
    }
  });

  return $app;
