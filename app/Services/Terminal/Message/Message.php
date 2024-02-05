<?php

namespace App\Services\Terminal\Message;

final class Message
{
    static function red(string $message, string $color = "text-terminal-error", string $styles = null): string
    {
        return "<span class='block $color $styles'>$message</span>";
    }

    static function green(string $message, string $color = "text-terminal-success", string $styles = null): string
    {
        return "<span class='block $color $styles'>$message</span>";
    }

    static function blue(string $message, string $color = "text-terminal-info", string $styles = null): string
    {
        return "<span class='block $color $styles'>$message</span>";
    }
}
