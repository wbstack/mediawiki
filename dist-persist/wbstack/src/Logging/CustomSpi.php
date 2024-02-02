<?php

namespace WBStack\Logging;

use MediaWiki\Logger\Spi;

class CustomSpi implements Spi {

    private $ignoreChannels = [];
    private $ignoreLevels = [];

    public function __construct( array $config = [] ) {
        if ( isset( $config[ 'ignore' ][ 'channels' ] ) ) {
            $this->ignoreChannels = $config[ 'ignore' ][ 'channels' ];
        }
        if ( isset( $config[ 'ignore' ][ 'levels' ] ) ) {
            $this->ignoreLevels = $config[ 'ignore' ][ 'levels' ];
        }
    }

    /**
     * Get a logger instance.
     *
     * @param string $channel Logging channel
     * @return \Psr\Log\AbstractLogger Logger instance
     */
    public function getLogger( $channel ) {
        return new CustomLogger( $channel, $this->ignoreChannels, $this->ignoreLevels );
    }
}
