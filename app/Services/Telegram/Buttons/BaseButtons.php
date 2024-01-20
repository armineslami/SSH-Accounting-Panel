<?php

namespace App\Services\Telegram\Buttons;

final class BaseButtons
{
    static public array $simpleButtons = [
        Buttons::LOGIN,
        Buttons::HELP
    ];

    static public array $inlineButtons = [
        [
            'text' => 'YES',
            'callback_data' => 'data_yes',
        ],
        [
            'text' => 'NO',
            'callback_data' => 'data_no',
        ],
    ];
}
