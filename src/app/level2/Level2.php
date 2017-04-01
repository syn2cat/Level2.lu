<?php

namespace level2;

use DateTime;

class Level2
{
    public static $imageMatch = 'https?:\/\/[^ ]+?(?:\.jpg|\.png|\.gif|\.svg)';
    public static $urlMatch   = '\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$\(\)?!:,.]*[A-Z0-9+&@#\/%=~_|$]';

    public static function getStatus($app)
    {
        $spaceAPI = Helpers::spaceAPI($app);

        $Level2[ 'open'       ] = $spaceAPI[ 'state'      ][ 'open'               ];
        $Level2[ 'lastchange' ] = $spaceAPI[ 'state'      ][ 'lastchange'         ];
        $Level2[ 'people'     ] = $spaceAPI[ 'sensors'    ][ 'people_now_present' ][ 0 ][ 'value' ];
        $Level2[ 'address'    ] = $spaceAPI[ 'location'   ][ 'address'            ];
        $Level2[ 'phone'      ] = $spaceAPI[ 'contact'    ][ 'phone'              ];
        $Level2[ 'logo'       ] = $spaceAPI[ 'logo'       ];

        return $Level2;
    }

    public static function getJSONCalendar($app)
    {
        $googleCalendarJson = 'https://www.googleapis.com/calendar/v3/calendars/'
        .$app[ 'google' ][ 'calendar_id' ].'/events'
        .'?singleEvents=true'
        .'&orderBy=startTime'
        .'&timeMin='.date('Y-m-d').'T'.date('H').'%3A'.date('i').'%3A'.date('s').'%2B01%3A00'
        .'&fields=description%2Citems(description%2Crecurrence%2Csummary%2Clocation%2Cstart%2Cend)%2Csummary'
        .'&key='.$app[ 'google' ][ 'api_key' ];

        return Helpers::JSON2Array($googleCalendarJson);
    }

    public static function getEventDateTime($googleEvent)
    {
        if (array_key_exists('dateTime', $googleEvent[ 'start' ])) {
            $event[ 'start' ] = strtotime($googleEvent[ 'start' ][ 'dateTime' ]);
            $event[ 'end'   ] = strtotime($googleEvent[ 'end'   ][ 'dateTime' ]);

            $event[ 'date'  ] = date('l, j. M G:i', $event[ 'start' ]);
        } else {
            $event[ 'start' ] = strtotime($googleEvent[ 'start' ][ 'date' ]);
            $event[ 'end'   ] = strtotime($googleEvent[ 'end'   ][ 'date' ]);

            $event[ 'date'  ] = date('l, j. M', $event[ 'start' ]);
        }

        return $event;
    }

    public static function getImages($googleEvent)
    {
        preg_match_all('/'.self::$imageMatch.'/i', $googleEvent[ 'description' ], $image, PREG_PATTERN_ORDER);

        if (sizeof($image[ 0 ]) > 0) {
            if ($image[ 0 ][ 0 ] != '') {
                return $image[ 0 ][ 0 ];
            } else {
                return false;
            }
        }
    }

    public static function getURLs($googleEvent)
    {
        preg_match_all('/'.self::$urlMatch.'/i', $googleEvent[ 'description' ], $url, PREG_PATTERN_ORDER);

        if (sizeof($url[ 0 ]) > 0) {
            if ($url[ 0 ][ 0 ] != '') {
                return $url[ 0 ][ 0 ];
            } else {
                return false;
            }
        }
    }

    public static function removeImages($googleEvent)
    {
        preg_match_all('/'.self::$imageMatch.'/i', $googleEvent[ 'description' ], $image, PREG_PATTERN_ORDER);

        return preg_replace(
        '/\n'.self::$imageMatch.'/i',
        '',
        $googleEvent[ 'description' ]
      );
    }

    public static function removeURLs($googleEvent)
    {
        preg_match_all('/'.self::$urlMatch.'/i', $googleEvent[ 'description' ], $url, PREG_PATTERN_ORDER);

        return preg_replace(
        '/\n'.self::$urlMatch.'/i',
        '',
        $googleEvent[ 'description' ]
      );
    }

    public static function getEvents($app)
    {
        $googleCalendar = Helpers::JSON2Array($app[ 'cache' ][ 'calendar' ][ 'json' ]);

        foreach ($googleCalendar[ 'items' ] as $googleEvent) {
            unset($event);

            $event = self::getEventDateTime($googleEvent);

            $event[ 'name'        ] = $googleEvent[ 'summary' ];

            if (array_key_exists('location', $googleEvent)) {
                $event[ 'location'  ] = $googleEvent[ 'location' ];
            }

            $event[ 'image'       ] = false;
            $event[ 'url'         ] = false;

            if (array_key_exists('description', $googleEvent)) {
                $event[ 'description' ] = $googleEvent[ 'description' ];

                $event[ 'image'       ] = self::getImages($event);
                $event[ 'description' ] = self::removeImages($event);
                $event[ 'url'         ] = self::getURLs($event);
                $event[ 'description' ] = self::removeURLs($event);
                $event[ 'description' ] = nl2br($event[ 'description' ]);
            }

            $events[] = $event;
        }

        return $events;
    }

    public static function getEventsByMonth($events, $year, $month)
    {
        $year   = (int) $year;
        $month  = (int) $month;

        $eventsInMonth = array();

        foreach ($events as $event) {
            if ((date('Y', $event[ 'start' ]) == $year) && (date('m', $event[ 'start' ]) == $month)) {
                $eventsInMonth[] = $event;
            }
        }

        return $eventsInMonth;
    }

    public static function getEventsByDay($events, $year, $month, $day)
    {
        $year   = (int) $year;
        $month  = (int) $month;
        $day    = (int) $day;

        $eventsInDay = array();

        foreach ($events as $event) {
            if ((date('Y', $event[ 'start' ]) == $year) && (date('m', $event[ 'start' ]) == $month) && (date('d', $event[ 'start' ]) == $day)) {
                $eventsInDay[] = $event;
            }
        }

        return $eventsInDay;
    }

    public static function getEvent($events, $startUnix)
    {
        $startUnix = (int) $startUnix;

        foreach ($events as $event) {
            if ($event[ 'start' ] == $startUnix) {
                return $event;
            }
        }
    }

    public static function getChartData($app)
    {
        $dowMap = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');

        $chartDataQuery = 'SELECT ROUND( AVG( people ) )
        FROM      state
        WHERE datetime >= curdate() - INTERVAL 312 Hour
        AND   WEEKDAY( datetime ) = ?
        AND   HOUR   ( datetime ) = ?';

        for ($dow = 0; $dow < 7; ++$dow) {
            $chartDataByDay[ 'name' ] = $dowMap[ $dow ];

            for ($hod = 0; $hod < 24; ++$hod) {
                $chartDataByDay[ 'data' ][ $hod % 24 ] = $app[ 'db' ]->fetchColumn(
            $chartDataQuery,
            array(
              $dow,
              $hod,
            )
          );
            }

            $chart[ $dow ] = $chartDataByDay;
        }

        return $chart;
    }
}
