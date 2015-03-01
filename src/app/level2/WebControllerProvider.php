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

      $ctr->get('/events/', function() use ( $app ) {

        $eventsToReturn = Level2::getEventsByMonth(
          Level2::getEvents( $app ),
          date( 'Y' ),
          date( 'm' )
        );

        return $app['twig']->render(
          'event-list.twig',
          array(
            'page'      =>  'events',
            'level2'    =>  Level2::getStatus( $app ),
            'events'    =>  $eventsToReturn
          )
        );

      });

      $ctr->get('/events/{year}/{month}', function( $year, $month ) use ( $app ) {

        if ( strpos( $month, '.' ) !== false ) {

          $arguments = explode( '.', $month );
          $month  = $arguments[ 0 ];
          $format = $arguments[ 1 ];

          if ( $format == 'json' ) {

            return $app->json(
              Level2::getEventsByMonth(
                Level2::getEvents( $app ),
                $year,
                $month
              )
            );

          }

        }

        return $app['twig']->render(
          'event-list.twig',
          array(
            'page'      =>  'events',
            'level2'    =>  Level2::getStatus( $app ),
            'events'    =>  Level2::getEventsByMonth(
              Level2::getEvents( $app ),
              $year,
              $month
            )
          )
        );

      });

      $ctr->get('/spaceapi', function(  ) use ( $app ) {

        return $app->json(
          Helpers::spaceAPI( $app )
        );

      });

      $ctr->get('/events/{parameter}', function( $parameter ) use ( $app ) {

        if ( $parameter == 'json' ) {

          return $app->json(
            Level2::getEvents( $app )
          );

        } else if ( strpos( $parameter, '.' ) !== false ) {

          $arguments = explode( '.', $parameter );
          $parameter  = $arguments[ 0 ];
          $format = $arguments[ 1 ];

          if ( $format == 'json' ) {

            return $app->json(
              array_slice(
                Level2::getEvents( $app ),
                0,
                $parameter
              )
            );

          }

        }

        return $app['twig']->render(
          'event-list.twig',
          array(
            'page'      =>  'events',
            'level2'    =>  Level2::getStatus( $app ),
            'events'    =>  array_slice(
              Level2::getEvents( $app ),
              0,
              $parameter
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
