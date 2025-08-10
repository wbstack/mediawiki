<?php

namespace WBStack\Logging;

use MediaWiki\Logger\Spi;

class CustomSpi implements Spi {

    protected $config;

    /**
     * @param array $config Logger configuration
     * @see CustomLogger::__construct()
     */
    public function __construct( array $config ) {
        if ( !array_key_exists( 'ignoreLevels', $config ) ) {
            throw new \InvalidArgumentException('ignoreLevels arg needed');
        }
        if ( !array_key_exists( 'ignoreAllInGroup', $config ) ) {
            throw new \InvalidArgumentException( 'ignoreAllInGroup arg needed' );
        }
        if ( !array_key_exists( 'logAllInGroup', $config ) ) {
            throw new \InvalidArgumentException( 'logAllInGroup arg needed' );
        }
        if ( !array_key_exists( 'logAllInGroupExceptDebug', $config ) ) {
            throw new \InvalidArgumentException( 'logAllInGroupExceptDebug arg needed' );
        }
        $this->config = $config;
    }

    /**
     * Get a logger instance.
     *
     * @param string $channel Logging channel
     * @return \Psr\Log\AbstractLogger Logger instance
     */
    public function getLogger( $channel ) {
        return new CustomLogger( $channel, $this->config );
    }
}
