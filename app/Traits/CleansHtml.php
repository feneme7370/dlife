<?php

namespace App\Traits;

trait CleansHtml
{
    public function cleanNotes(?string $html): string
    {
        if (!$html) {
            return '';
        }

        $text = str_replace(
            ['</p>', '<br>', '<br/>', '<br />'],
            "\n",
            $html
        );

        return trim(
            html_entity_decode(
                strip_tags($text),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            )
        );
    }
}