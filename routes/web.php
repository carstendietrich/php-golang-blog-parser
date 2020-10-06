<?php

use App\ArticleRepository;
use Laravel\Lumen\Routing\Router;
use OpenCensus\Trace\Tracer;

/** @var Router $router */
$router->get('/', function () {
    return Tracer::inSpan(['name' => 'Render index'], static function () {
        return '
    <strong>Available routes:</strong>
    <ul>
        <li>GET <a href="/articles">/articles</a></li>
        <li>GET <a href="/articles/id/hello-world">/articles/id/hello-world</a></li>
    </ul>
    ';
    });
});

$router->get('/articles', function () {
    return Tracer::inSpan(['name' => 'Get all articles and render JSON response'], static function () {
        $articles = ArticleRepository::getAll();
        return response(json_encode($articles, JSON_THROW_ON_ERROR))->header('Content-Type', 'application/json');
    });
});


$router->get('/articles/id/{id}', function (string $id) {
    return Tracer::inSpan(['name' => 'Get single articles and render JSON response'], static function () use ($id) {
        $article = ArticleRepository::getByID($id);
        return response(json_encode($article, JSON_THROW_ON_ERROR))->header('Content-Type', 'application/json');
    });
});

