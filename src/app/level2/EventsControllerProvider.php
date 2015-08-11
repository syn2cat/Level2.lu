<?php

  namespace level2;

  use Silex\Application;
  use Silex\ControllerProviderInterface;

  class EventsControllerProvider implements ControllerProviderInterface {

    public function connect ( Application $app ) {

      $ctr = $app['controllers_factory'];

      $ctr->get('/', function() use ( $app ) {

        return $app['twig']->render(
          'event-list.twig',
          array(
            'page'      =>  'events',
            'level2'    =>  Level2::getStatus( $app ),
            'events'    =>  array_slice(
              Level2::getEvents( $app ),
              0,
              25
            )
          )
        );

      });

      $ctr->get('/{year}/{month}/', function( $year, $month ) use ( $app ) {

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

      $ctr->get('/{year}/{month}/{day}/', function( $year, $month, $day ) use ( $app ) {

        if ( strpos( $day, '.' ) !== false ) {

          $arguments = explode( '.', $day );
          $day  = $arguments[ 0 ];
          $format = $arguments[ 1 ];

          if ( $format == 'json' ) {

            return $app->json(
              Level2::getEventsByDay(
                Level2::getEvents( $app ),
                $year,
                $month,
                $day
              )
            );

          }

        }

        return $app['twig']->render(
          'event-list.twig',
          array(
            'page'      =>  'events',
            'level2'    =>  Level2::getStatus( $app ),
            'events'    =>  Level2::getEventsByDay(
              Level2::getEvents( $app ),
              $year,
              $month,
              $day
            )
          )
        );

      });

      $ctr->get('/{parameter}/', function( $parameter ) use ( $app ) {

        if ( $parameter == 'json' ) {

          return $app->json(
            Level2::getEvents( $app )
          );

        } else if ( $parameter == 'ical' ) {

          header('Content-type: text/calendar; charset=utf-8');
          header('Content-Disposition: attachment; filename=calendar.ics');

          return file_get_contents( $app[ 'cache' ][ 'calendar' ][ 'ical' ] );

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

        if ( strlen( $parameter ) == 10 ) {

          return $app['twig']->render(
            'perma-event.twig',
            array(
              'page'      =>  'events',
              'level2'    =>  Level2::getStatus( $app ),
              'event'     =>  Level2::getEvent(
                Level2::getEvents( $app ),
                $parameter
              )
            )
          );

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

      return $ctr;

    }

  }
