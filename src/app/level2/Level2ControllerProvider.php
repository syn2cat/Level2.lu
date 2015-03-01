<?php

  namespace level2;

  use Silex\Application;
  use Silex\ControllerProviderInterface;

  class Level2ControllerProvider implements ControllerProviderInterface {

    public function connect ( Application $app ) {

      $ctr = $app['controllers_factory'];

      $ctr->get('/', function() use ( $app ) {

        return $app['twig']->render(
          'level2.twig',
          array(
            'page'      =>  'home',
            'level2'    =>  Level2::getStatus( $app ),
            'events'    =>  array_slice(
              Level2::getEvents( $app ),
              0,
              1
            )
          )
        );

      });

      $ctr->get('/spaceapi', function(  ) use ( $app ) {

        return $app->json(
          Helpers::spaceAPI( $app )
        );

      });

      $ctr->get('/openingTimes', function(  ) use ( $app ) {

        return $app['twig']->render(
          'openingTimes.twig',
          array(
            'page'    =>  'chart',
            'level2'  =>  Level2::getStatus( $app ),
            'chart'   =>  Helpers::JSON2Array( $app[ 'cache' ][ 'attendance' ][ 'json' ] )
          )
        );

      });

      $ctr->get('/scrape', function() use ( $app ) {

        Helpers::saveFile ( json_encode( Level2::getJSONCalendar( $app )  ) , $app[ 'cache' ][ 'calendar'   ][ 'json' ] );
        Helpers::saveFile ( file_get_contents( $app[ 'google' ][ 'ical' ] ) , $app[ 'cache' ][ 'calendar'   ][ 'ical' ] );
        Helpers::saveFile ( json_encode( Level2::getChartData( $app )     ) , $app[ 'cache' ][ 'attendance' ][ 'json' ] );

        return true;

      });

      return $ctr;

    }

  }
