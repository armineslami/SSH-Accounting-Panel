<?php

namespace App\Telegram\Keyboards;

use App\Telegram\Buttons\BaseButtons;
use Telegram\Bot\Keyboard\Keyboard as TelegramSdkKeyboard;

class Keyboard
{
    static function simpleMarkupKeyboard(array $buttons = null, bool $resize = true, bool $oneTime = true): TelegramSdkKeyboard {
        if (is_null($buttons)) {
            $buttons = BaseButtons::$simpleButtons;
        }

        return TelegramSdkKeyboard::make([
            'keyboard' => [$buttons],
            'resize_keyboard' => $resize,
            'one_time_keyboard' => $oneTime
        ]);
    }

    static function inlineKeyboard(array $buttons = null, bool $resize = true, bool $oneTime = true): TelegramSdkKeyboard {
        if (is_null($buttons)) {
            $buttons = BaseButtons::$inlineButtons;
        }

        return TelegramSdkKeyboard::make([
            'inline_keyboard' => [$buttons],
            'resize_keyboard' => $resize,
            'one_time_keyboard' => $oneTime
        ]);
    }
}
