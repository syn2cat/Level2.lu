<?php

  namespace level2;

  use Silex\Application;
  use Silex\ControllerProviderInterface;

  class WebControllerProvider implements ControllerProviderInterface {

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

      $ctr->get('/events', function() use ( $app ) {

        return $app['twig']->render(
          'level2.twig',
          array(
            'page'      =>  'events',
            'level2'    =>  Level2::getStatus( $app ),
            'events'    =>  array_slice(
              Level2::getEvents( $app ),
              0,
              10
            )
          )
        );

      });

      $ctr->get('/scrape', function() use ( $app ) {

        Helpers::saveFile ( json_encode( Level2::getJSONCalendar( $app ) ),   'cache/calendar.json' );
        Helpers::saveFile ( file_get_contents( $app[ 'google' ][ 'ical' ] ) , 'cache/calendar.ics' );

        return true;

      });

      return $ctr;

    }

  }
