<?php

namespace App\Enums;

enum ArticleType: string
{
    case Blog = 'blog';
    case Opinion = 'opinion';
    case Educational = 'educational';
    case Research = 'research';

    public function label(): string
    {
        return match ($this) {
            self::Blog => 'Blog post',
            self::Opinion => 'Opinion',
            self::Educational => 'Educational',
            self::Research => 'Research',
        };
    }
}
