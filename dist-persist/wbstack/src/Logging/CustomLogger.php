<?php

namespace WBStack\Logging;

use Psr\Log\AbstractLogger;
use MediaWiki\Logger\LegacyLogger;

class CustomLogger extends AbstractLogger {

    private $channel;
    private $ignoreChannels;
    private $ignoreLevels;

    public function __construct( $channel, array $ignoreChannels, array $ignoreLevels ) {
        $this->channel = $channel;
        $this->ignoreChannels = $ignoreChannels;
        $this->ignoreLevels = $ignoreLevels;
    }

    public function log( $level, $message, array $context = [] ) {
        $ignore = in_array( $this->channel, $this->ignoreChannels )
            || in_array( $level, $this->ignoreLevels );

        if ( !$ignore ) {
            $this->doLog( $level, $message, $context );
        }
    }

    private function doLog( $level, $message, array $context = [] ) {
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
