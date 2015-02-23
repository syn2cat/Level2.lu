<?php

  namespace level2;

  use Silex\Application;
  use Silex\ControllerProviderInterface;

  class WebControllerProvider implements ControllerProviderInterface {

    public function connect ( Application $app ) {

      $ctr = $app['controllers_factory'];

      $ctr->get('/', function() use ( $app ) {

        return $app['twig']->render(
          'level2.twig'
        );

      });

      return $ctr;

    }

  }
