<?php

use Psr\Log\AbstractLogger;
use MediaWiki\Logger\LegacyLogger;

class WikWikiLogger extends AbstractLogger {

    private $channel;
    private $config;

    public function __construct($channel, $config ) {
        $this->channel = $channel;
        $this->config = $config;
    }

    public function log( $level, $message, array $context = [] ) {
        if(in_array($this->channel, $this->config['logAllInGroup'])) {
            $this->doLog( $level, $message, $context );
            return;
        }
        if(in_array($this->channel, $this->config['logAllInGroupExceptDebug']) && $level !== 'debug') {
            $this->doLog( $level, $message, $context );
            return;
        }

        if(in_array($this->channel, $this->config['ignoreAllInGroup'])) {
            return;
        }
        if(in_array($level, $this->config['ignoreLevels'])) {
            return;
        }

        $this->doLog($level, $message, $context);
    }

    private function doLog( $level, $message, $context ) {
        fwrite( STDERR, "[$level] " .
            LegacyLogger::format( $this->channel, $message, $context ) );
    }

}
