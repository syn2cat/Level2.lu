<?php

  namespace level2;

  use Silex\Application;
  use Silex\ControllerProviderInterface;

  class PressControllerProvider implements ControllerProviderInterface {

    public function connect ( Application $app ) {

      $ctr = $app['controllers_factory'];

      $ctr->get('/', function() use ( $app ) {

        return $app['twig']->render(
          'press.twig',
          array(
            'page'      =>  'press',
            'level2'    =>  Level2::getStatus( $app )
          )
        );

      });

      return $ctr;

    }

  }
