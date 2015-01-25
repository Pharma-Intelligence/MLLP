<?php
namespace PharmaIntelligence\MLLP;

use Evenement\EventEmitter;
use Evenement\EventEmitterInterface;
use React\Socket\ConnectionInterface;


abstract class Server extends EventEmitter implements EventEmitterInterface
{
    private $io;
    
    public function __construct(EventEmitterInterface $io) {
        $this->io = $io;
        $this->io->on('connection', function(ConnectionInterface $connection) {
            $this->handleRequest($connection);
        });
    }
    
    public function handleRequest(ConnectionInterface $connection) {
        $this->emit('connection', array($connection));
        $connection->on('data', function($data) use ($connection) {
            try {
                $data = MLLPParser::unwrap($data);
                $this->emit('data', array($data, $connection));
            } catch(\InvalidArgumentException $e) {
                $this->handleInvalidMLLPEnvelope($data, $connection);
                $this->emit('error', array('Invalid MLLP envelope. Received: "'.$data.'"'));
            }
        });
    }
    
    public function send($data, ConnectionInterface $connection) {
        $this->emit('send', array($data));
        
        $connection->on('error', function(ConnectionInterface $connection, $error) {
           $this->emit('error', array('Error sending data: '.$error));
        });
        
        $data = MLLPParser::enclose($data);
        $connection->write($data);
        $connection->removeAllListeners('error');
        
    }
    
    protected function handleInvalidMLLPEnvelope($data, ConnectionInterface $connection) {
        $connection->end('INVALID ENVELOPE');
    }
}

