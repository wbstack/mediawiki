<?php

namespace WBStack\Logging;

use Psr\Log\AbstractLogger;
use MediaWiki\Logger\LegacyLogger;

class CustomLogger extends AbstractLogger {

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
        $payload = false;

        if ( str_contains( $this->channel, 'json' ) ) {
            $decoded = json_decode( $message, true );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                $payload = $decoded;
            }
        }

        if ( !$payload ) {
            $payload = [
                'message' => LegacyLogger::format( $this->channel, $message, $context )
            ];
        }

        $payload[ '@type' ] = 'type.googleapis.com/google.devtools.clouderrorreporting.v1beta1.ReportedErrorEvent';
        $payload[ 'severity' ] = $level;
        $payload[ 'serviceContext' ] = [
            'service' => 'WBaaS MediaWiki',
            'version' => '1.0.0'
        ];

        $output = json_encode( $payload );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $output = "[$level] " . LegacyLogger::format( $this->channel, $message, $context );
        }

        fwrite( STDERR, $output . PHP_EOL );
    }

}
