<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use DI\Container;
use DI\Bridge\Slim\Bridge;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;

require(__DIR__.'/../vendor/autoload.php');

// Create DI container
$container = new Container();
// Add Twig to Container
$container->set(Twig::class, function() {
  return Twig::create(__DIR__.'/../views');
});
// Add Monolog to Container
$container->set(LoggerInterface::class, function () {
  $logger = new Logger('default');
  $logger->pushHandler(new StreamHandler('php://stderr'), Level::Debug);
  return $logger;
});

// Add Cowsay to Container
$container->set(\Cowsayphp\AnimalInterface::class, function() {
  return \Cowsayphp\Farm::create(\Cowsayphp\Farm\Cow::class);
});

// Create main Slim app
$app = Bridge::create($container);
$app->addErrorMiddleware(true, false, false);

// Our web handlers
$app->get('/', function(Request $request, Response $response, LoggerInterface $logger, Twig $twig) {
  $logger->debug('logging output.');
  return $twig->render($response, 'index.twig');
});

$app->get('/coolbeans', function(Request $request, Response $response, LoggerInterface $logger, \Cowsayphp\AnimalInterface $animal) {
  $logger->debug('letting the Cowsay library write something cool.');
  $response->getBody()->write("<pre>".$animal->say("Cool beans")."</pre>");
  return $response;
});

$app->run();
