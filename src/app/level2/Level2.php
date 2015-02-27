<?php

  namespace level2;

  use Silex\Application;

  class Level2 {

    static public $imageMatch = '/https?:\/\/[^ ]+?(?:\.jpg|\.png|\.gif)/i';
    static public $urlMatch = '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$\(\)?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i';

    static public function getStatus ( $app ) {

      $spaceAPI = Helpers::spaceAPI( $app );

      $Level2[ 'open'     ] = $spaceAPI[ 'state'      ][ 'open'               ];
      $Level2[ 'people'   ] = $spaceAPI[ 'sensors'    ][ 'people_now_present' ][ 0 ][ 'value' ];
      $Level2[ 'address'  ] = $spaceAPI[ 'location'   ][ 'address'            ];
      $Level2[ 'phone'    ] = $spaceAPI[ 'contact'    ][ 'phone'              ];
      $Level2[ 'logo'     ] = $spaceAPI[ 'logo'       ];

      return $Level2;

    }

    static public function getJSONCalendar ( $app ) {

      $googleCalendarJson = 'https://www.googleapis.com/calendar/v3/calendars/'
        . $app[ 'google' ][ 'calendar_id' ] . '/events'
        . '?singleEvents=true'
        . '&orderBy=startTime'
        . '&timeMin=' . date( 'Y-m-d' ) . 'T' . date( 'H' ) . '%3A' . date( 'i' ) . '%3A' . date( 's' ) . '%2B01%3A00'
        . '&fields=description%2Citems(description%2Crecurrence%2Csummary%2Clocation%2Cstart%2Cend)%2Csummary'
        . '&key=' . $app[ 'google' ][ 'api_key' ];

      return Helpers::JSON2Array( $googleCalendarJson );

    }

    static public function getEventDateTime ( $googleEvent ) {

      if ( array_key_exists( 'dateTime' , $googleEvent[ 'start' ] ) ){
        $event[ 'start' ] = strtotime( $googleEvent[ 'start' ][ 'dateTime' ] );
        $event[ 'end'   ] = strtotime( $googleEvent[ 'end'   ][ 'dateTime' ] );

        $event[ 'date'  ] = date( 'l, j. M G:i', $event[ 'start' ] );

      } else {
        $event[ 'start' ] = strtotime( $googleEvent[ 'start' ][ 'date' ] );
        $event[ 'end'   ] = strtotime( $googleEvent[ 'end'   ][ 'date' ] );

        $event[ 'date'  ] = date( 'l, j. M', $event[ 'start' ] );

      }

      return $event;

    }

    static public function getImages( $googleEvent ) {

      preg_match_all( self::$imageMatch, $googleEvent[ 'description' ], $image, PREG_PATTERN_ORDER );

      if ( sizeof( $image[ 0 ] ) > 0 ) {

        if ( $image[ 0 ][ 0 ] != '' ) {

          return $image[ 0 ][ 0 ];

        } else {

          return false;

        }

      }

    }

    static public function getURLs( $googleEvent ) {

      preg_match_all( self::$urlMatch, $googleEvent[ 'description' ], $url, PREG_PATTERN_ORDER );

      if ( sizeof( $url[ 0 ] ) > 0 ) {

        if ( $url[ 0 ][ 0 ] != '' ) {

          return $url[ 0 ][ 0 ];

        } else {

          return false;

        }

      }

    }

    static public function removeImages( $googleEvent ) {

      preg_match_all( self::$imageMatch, $googleEvent[ 'description' ], $image, PREG_PATTERN_ORDER );

      return preg_replace(
        self::$imageMatch,
        '',
        $googleEvent[ 'description' ]
      );

    }

    static public function removeURLs( $googleEvent ) {

      preg_match_all( self::$urlMatch, $googleEvent[ 'description' ], $url, PREG_PATTERN_ORDER );

      return preg_replace(
        self::$urlMatch,
        '',
        $googleEvent[ 'description' ]
      );

    }

    static public function getEvents ( $app ) {

      $googleCalendar = Helpers::JSON2Array( $app[ 'cache' ][ 'calendar' ][ 'json' ] );

      foreach( $googleCalendar[ 'items' ] as $googleEvent ) {

        unset( $event );

        $event = self::getEventDateTime( $googleEvent );

        $event[ 'name'        ] = $googleEvent[ 'summary' ];

        if ( array_key_exists( 'location' , $googleEvent ) ){
          $event[ 'location'  ] = $googleEvent[ 'location' ];
        }

        $event[ 'image'       ] = false;
        $event[ 'url'         ] = false;

        if ( array_key_exists( 'description' , $googleEvent ) ){

          $event[ 'description' ] = $googleEvent[ 'description' ];

          $event[ 'image'       ] = self::getImages( $event );
          $event[ 'description' ] = self::removeImages( $event );
          $event[ 'url'         ] = self::getURLs( $event );
          $event[ 'description' ] = self::removeURLs( $event );
          $event[ 'description' ] = nl2br( $event[ 'description' ] );

        }

        $events[] = $event;

      }

      return $events;

    }

    static public function getEventsByMonth( $events, $year, $month ) {

      foreach( $events as $event ) {

        if ( ( date( 'Y', $event[ 'start' ] ) == $year ) && ( date( 'm', $event[ 'start' ] ) == $month ) ) {

          $eventsInMonth[] = $event;

        }

      }

      return $eventsInMonth;

    }

  }
