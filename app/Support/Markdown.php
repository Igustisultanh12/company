<?php

namespace App\Support;

use Illuminate\Support\Str;

class Markdown
{
    /** Markdown → HTML aman (HTML mentah di-escape, tautan javascript: diblokir). */
    public static function toHtml(string $markdown): string
    {
        if (trim($markdown) === '') {
            return '';
        }

        return Str::markdown($markdown, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]);
    }
}
