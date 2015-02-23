<?php

  namespace level2;

  use Silex\Application;

  class Level2 {

    static public function getStatus() {

      $spaceAPI = json_decode(
        file_get_contents( 'https://spaceapi.syn2cat.lu/status/json' ),
        true
      );

      $Level2[ 'open'     ] = $spaceAPI[ 'state'      ][ 'open'               ];
      $Level2[ 'people'   ] = $spaceAPI[ 'sensors'    ][ 'people_now_present' ][ 0 ][ 'value' ];
      $Level2[ 'address'  ] = $spaceAPI[ 'location'   ][ 'address'            ];
      $Level2[ 'phone'    ] = $spaceAPI[ 'contact'    ][ 'phone'              ];

      return $Level2;

    }

    static public function getEvents( $app ) {

      $googleCalendarJson = 'https://www.googleapis.com/calendar/v3/calendars/'
        . $app[ 'google' ][ 'calendar_id' ] . '/events'
        . '?singleEvents=true'
        . '&orderBy=startTime'
        . '&timeMin=2015-02-22T00%3A00%3A00%2B01%3A00'
        . '&fields=description%2Citems(description%2Crecurrence%2Csummary%2Clocation%2Cstart%2Cend)%2Csummary'
        . '&key=' . $app[ 'google' ][ 'api_key' ];

      $googleCalendar = json_decode(
        file_get_contents( $googleCalendarJson ),
        true
      );

      foreach( $googleCalendar[ 'items' ] as $googleEvent ) {

        $event[ 'name'        ] = $googleEvent[ 'summary' ];

        if ( array_key_exists( 'dateTime' , $googleEvent[ 'start' ] ) ){
          $event[ 'start' ][ 'datetime' ] = strtotime( $googleEvent[ 'start' ][ 'dateTime' ] );
        } else {
          $event[ 'start' ][ 'date'     ] = strtotime( $googleEvent[ 'start' ][ 'date' ] );
        }

        if ( array_key_exists( 'dateTime' , $googleEvent[ 'end' ] ) ){
          $event[ 'end' ][ 'datetime' ] = strtotime( $googleEvent[ 'end' ][ 'dateTime' ] );
        } else {
          $event[ 'end' ][ 'date'     ] = strtotime( $googleEvent[ 'end' ][ 'date' ] );
        }

        if ( array_key_exists( 'location' , $googleEvent ) ){
          $event[ 'location'    ] = $googleEvent[ 'location' ];
        }

        unset( $url );

        if ( array_key_exists( 'description' , $googleEvent ) ){
          $event[ 'description' ] = $googleEvent[ 'description' ];
        }

        $urlMatch = '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$\(\)?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i';
        preg_match_all( $urlMatch, $event[ 'description' ], $url, PREG_PATTERN_ORDER );

        $event[ 'description' ] = preg_replace( $urlMatch, '', $event[ 'description' ] );

        if ( is_array( $url ) ) {
          $event[ 'url' ] = $url[ 0 ];
        }

        $events[] = $event;

      }

      return $events;

    }

  }
