<?php

namespace App;

use DateTimeImmutable;
use JsonSerializable;

class Article implements JsonSerializable
{
    public string $id = '';
    public string $title = '';
    public string $link = '';
    public string $author = '';
    public array $tags = [];
    public DateTimeImmutable $date;

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'link' => $this->link,
            'author' => $this->author,
            'date' => $this->date->format(DATE_RFC3339),
            'tags' => $this->tags,
        ];
    }
}
