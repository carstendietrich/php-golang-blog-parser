<?php

namespace App;

use DateTimeImmutable;
use OpenCensus\Trace\Tracer;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\Node\Collection;

class ArticleRepository
{
    private const BLOG_INDEX_PATH = '/index';
    private const BLOG_BASE_URL = 'https://blog.golang.org';

    /**
     * @return array
     */
    public static function getAll(): array
    {

        $dom = Tracer::inSpan(['name' => 'Fetch HTML from blog.golang.org and parse DOM'], static function () {
            $dom = new Dom();
            return $dom->loadFromUrl(self::BLOG_BASE_URL . self::BLOG_INDEX_PATH);
        });


        return Tracer::inSpan(['name' => 'Grab all articles from the DOM'], static function () use ($dom) {
            $articles = [];
            /** @var Collection $blogEntries */
            $blogEntries = $dom->find("#content .blogtitle");
            /** @var Dom\Node\HtmlNode $entry */
            foreach ($blogEntries as $entry) {
                /** @var Dom\Node\HtmlNode $title */
                $title = $entry->find('a')[0];
                /** @var Dom\Node\HtmlNode $date */
                $date = $entry->find('span.date')[0];
                /** @var Dom\Node\HtmlNode $author */
                $author = $entry->find('span.author')[0];
                /** @var Dom\Node\HtmlNode $tags */
                $tags = $entry->find('span.tags')[0];

                $article = new Article();

                if ($title !== null) {
                    $article->id = basename($title->getTag()->getAttribute('href')->getValue());
                    $article->title = htmlspecialchars_decode($title->text(), ENT_QUOTES);
                    $article->link = self::BLOG_BASE_URL . $title->getTag()->getAttribute('href')->getValue();
                }

                if ($author !== null) {
                    $article->author = $author->text();
                }

                if ($date !== null) {
                    $article->date = DateTimeImmutable::createFromFormat('j F Y', $date->text());
                }

                if ($tags !== null) {
                    $article->tags = explode(' ', trim($tags->text()));
                }

                $articles[] = $article;
            }
            return $articles;
        });
    }

    /**
     * @param string $id
     * @return Article
     */
    public static function getByID(string $id): Article
    {
        return Tracer::inSpan(['name' => 'Search for single article'], static function () use ($id) {
            foreach (self::getAll() as $article) {
                if ($article->id === $id) {
                    return $article;
                }
            }
            throw new ArticleNotFoundException('Article with id: ' . $id . ' not found in blog.');
        });
    }
}
