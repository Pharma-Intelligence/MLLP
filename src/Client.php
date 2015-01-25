<?php
namespace PharmaIntelligence\MLLP;

use Evenement\EventEmitter;
use Evenement\EventEmitterInterface;
use React\EventLoop\LoopInterface;
use React\Stream\Stream;



class Client extends EventEmitter implements EventEmitterInterface
{
    protected $host;
    protected $port;
    /**
     * 
     * @var NoDNSConnector
     */
    protected $connector;    
    
    public function __construct($host, $port, LoopInterface $loop) {
        $this->host = $host;
        $this->port = $port;
        $this->loop = $loop;
        
        $this->connector = new NoDNSConnector($loop);
    }
    
    public function send($data) {
        $this->emit('send', array($data));
        $wrappedData = MLLPParser::enclose($data);
        
        $this->connector->create($this->host, $this->port)->then(function (Stream $stream) use ($wrappedData) {
            $stream->write($wrappedData);
            $stream->on('data', function($data) use ($stream) {
                $data = MLLPParser::unwrap($data);
                $this->emit('data', array($data, $stream));
                $stream->end();
            });
        });
    }
}
