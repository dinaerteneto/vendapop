<?php

return [
    'initial_spots' => (int) env('SPOTS_INITIAL', 100),
    'replenish_amount' => (int) env('SPOTS_REPLENISH_AMOUNT', 10),
    'replenish_day' => env('SPOTS_REPLENISH_DAY', 'monday'),
    'replenish_time' => env('SPOTS_REPLENISH_TIME', '08:00'),
];
