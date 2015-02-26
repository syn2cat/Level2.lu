<?php

  namespace level2;

  use Silex\Application;

  class Level2 {

    static public function getStatus( $app ) {

      $spaceAPI = json_decode(
        file_get_contents( $app[ 'spaceAPI' ] ),
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

        unset( $event );

        $event[ 'name'        ] = $googleEvent[ 'summary' ];

        if ( array_key_exists( 'dateTime' , $googleEvent[ 'start' ] ) ){
          $event[ 'start' ] = strtotime( $googleEvent[ 'start' ][ 'dateTime' ] );
          $event[ 'end'   ] = strtotime( $googleEvent[ 'end'   ][ 'dateTime' ] );

          $event[ 'date'  ] = date( 'l, j. M G:i', $event[ 'start' ] );

        } else {
          $event[ 'start' ] = strtotime( $googleEvent[ 'start' ][ 'date' ] );
          $event[ 'end'   ] = strtotime( $googleEvent[ 'end'   ][ 'date' ] );

          $event[ 'date'  ] = date( 'l, j. M', $event[ 'start' ] );

        }

        if ( array_key_exists( 'location' , $googleEvent ) ){
          $event[ 'location'    ] = $googleEvent[ 'location' ];
        }

        if ( array_key_exists( 'description' , $googleEvent ) ){

          $event[ 'description' ] = $googleEvent[ 'description' ];

          unset( $image );

          $imageMatch = '/https?:\/\/[^ ]+?(?:\.jpg|\.png|\.gif)/i';
          preg_match_all( $imageMatch, $event[ 'description' ], $image, PREG_PATTERN_ORDER );

          $event[ 'description' ] = preg_replace(
            $imageMatch,
            '',
            $event[ 'description' ]
          );

          if ( sizeof( $image[ 0 ] ) > 0 ) {
            if ( $image[ 0 ][ 0 ] != '' ) {
              $event[ 'image' ] = $image[ 0 ][ 0 ];
            }
          }

          $event[ 'description' ] = nl2br( $event[ 'description' ] );

          unset( $url );

          $urlMatch = '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$\(\)?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i';
          preg_match_all( $urlMatch, $event[ 'description' ], $url, PREG_PATTERN_ORDER );

          $event[ 'description' ] = preg_replace(
            $urlMatch,
            '',
            $event[ 'description' ]
          );

          if ( sizeof( $url[ 0 ] ) > 0 ) {
            $event[ 'url' ] = $url[ 0 ][ 0 ];
          }

        }

        $events[] = $event;

      }

      return $events;

    }

  }
