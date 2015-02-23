<?php

  namespace level2;

  use Silex\Application;

  class Level2 {

    static public function getStatus () {

      $spaceAPI = json_decode(
        file_get_contents( 'https://spaceapi.syn2cat.lu/status/json' ),
        true
      );

      $Level2[ 'open'   ] = $spaceAPI[ 'state'   ][ 'open' ];
      $Level2[ 'people' ] = $spaceAPI[ 'sensors' ][ 'people_now_present' ][ 0 ][ 'value' ];

      return $Level2;

    }

  }
