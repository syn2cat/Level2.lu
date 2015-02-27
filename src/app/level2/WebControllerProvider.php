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
          'events.twig',
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

            print_r( $eventsToReturn );

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
          'events.twig',
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

      $ctr->get('/events/{count}', function( $count ) use ( $app ) {

        if ( strpos( $count, '.' ) !== false ) {

          $arguments = explode( '.', $count );
          $count  = $arguments[ 0 ];
          $format = $arguments[ 1 ];

          if ( $format == 'json' ) {

            print_r( $eventsToReturn );

            return $app->json(
              array_slice(
                Level2::getEvents( $app ),
                0,
                $count
              )
            );

          }

        }

        return $app['twig']->render(
          'events.twig',
          array(
            'page'      =>  'events',
            'level2'    =>  Level2::getStatus( $app ),
            'events'    =>  array_slice(
              Level2::getEvents( $app ),
              0,
              $count
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
