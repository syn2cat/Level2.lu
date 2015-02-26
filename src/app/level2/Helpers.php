<?php

  namespace level2;

  use Silex\Application;

  class Helpers {

    static public function JSON2Array ( $URL ) {

      return json_decode(
        file_get_contents( $URL ),
        true
      );

    }

    static public function spaceAPI ( $app ) {

      return json_decode(
        file_get_contents( $app[ 'spaceAPI' ] ),
        true
      );

    }

  }
