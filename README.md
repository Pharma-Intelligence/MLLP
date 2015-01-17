# MLLP
MLLP Server implementation in PHP built on [reactphp](http://reactphp.org/). The MLLP Server listens to a TCP port. Accepts MLLP payloads, handles the unwrapping of the MLLP envelope and wrapping or the response.

## Server
This distribution contains one abstract `Server` class that can be used to build upon and emits 4 events. Responses to clients should be send through the `send()` function.

## Events
 1. `connection` : When a connection is made this event is emitted. Argument is the connection. Instance of `ConnectionInterface`
 2. `data`: When data is received this event is emitted. Event contains two arguments: `$data` the data received, stripped from it's MLLP envelope, and the connection which received the data, instance of `ConnectionInterface`
 3. `send`: When data is sent through the server this event is emitted. Event contains the unwrapped data.
 4. `error`: Whenever an error occurs on receiving data, unwrapping the MLLP envelope, or sending the data this event is emitted. Contains one argument: `$errorMessage`

## Example implementation

```<?php

use PharmaIntelligence\MLLP\Server;
use PharmaIntelligence\HL7\Unserializer;
use React\Socket\ConnectionInterface;

class MyServer extends Server {
    // No added logic in this example
}

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
// Set up a React stream to STDOUT to log everything to the console.
$logging = new React\Stream\Stream(STDOUT, $loop);
$server = new MyServer($socket);

// Log connection info
$server->on('connection', function(ConnectionInterface $connection) use($logging) {
    $logging->write('Connection from: '.$connection->getRemoteAddress().PHP_EOL);
});

// Log error info
$server->on('error', function($errorMessage) use($logging)  {
    $logging->write('Error: '.$errorMessage.PHP_EOL);
});

// Log sent data
$server->on('send', function($data) use($logging)  {
    $logging->write('Sending: '.str_replace(chr(13), PHP_EOL, $data).PHP_EOL);
});

// Log received data
$server->on('data', function($data) use($logging)  {
    $logging->write('Received: '.str_replace(chr(13), PHP_EOL, $data).PHP_EOL);
});
$server->on('data', function ($data, ConnectionInterface $connection) use($server) {
    // $data contains a HL7 Payload
    // Parse HL7 and create an ACK message
    $ack = 'A_ACK_STRING';
    $server->send($ack, $connection);
    $connection->end();
});
$socket->listen(23887);

$loop->run();
```