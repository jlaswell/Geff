<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \GuzzleHttp\Client as Client;

require '../vendor/autoload.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$container = new \Slim\Container($configuration);
$app = new \Slim\App($container);

$app->post('/', function (Request $request, Response $response) {
    $text = $request->getParsedBody()['text'];
    $rightGifResponse = (new Client())->request('POST', 'https://rightgif.com/search/web', [
        'json' => ['text' => $text]
    ]);
    $rightGif = json_decode($rightGifResponse->getBody(), 1)['url'];

    $giphyResponse = (new Client())->request('GET', 'http://api.giphy.com/v1/gifs/search?q='.urlencode($text).'&api_key=dc6zaTOxFJmzC&limit=1&offset=0');
    $gif = json_decode($giphyResponse->getBody(), 1);
    $giphyGif = $gif['data'][0]['images']['original']['url'];

    $guzzleResponse = (new Client())->request('POST', getenv('CALLBACK'), [
        'json' => [
            'text' => '*Gif duel:* _'.$text.'_',
            'attachments' => [
                [
                    'fallback' => $text,
                    'text' => 'Rightgif',
                    'image_url' => $rightGif,
                ],
                [
                    'fallback' => $text,
                    'text' => 'Giphy',
                    'image_url' => $giphyGif,
                ],
            ],
        ],
    ]);

    return $response->withStatus(200);
});
$app->run();
