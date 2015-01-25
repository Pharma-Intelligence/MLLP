<?php
namespace PharmaIntelligence\MLLP;

class MLLPParser
{
    public static function enclose($data) {
        return chr(11).$data.chr(28).chr(13);
    } 
    
    public static function unwrap($data) {
        if(substr($data, 0, 1) !== chr(11))
            throw new \InvalidArgumentException('Envelope does not start with <VT> (ASCII 11)');
        
        if(substr($data, -2) !== chr(28).chr(13))
            throw new \InvalidArgumentException('Envelope does not end with <FS><CR> (ASCII 28, 13)');
        return substr($data, 1, -2);
    }
}

