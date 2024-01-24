<?php

class Localization {
    private $extensionMessagesFiles;
    private $messagesDirs;
    private $baseDirectory;
    private $isLocalisationRebuild;

    public function __construct(
        &$extensionMessagesFiles,
        &$messagesDirs,
        $baseDirectory,
        $isLocalisationRebuild
    ) {
        $this->extensionMessagesFiles = &$extensionMessagesFiles;
        $this->messagesDirs = &$messagesDirs;
        $this->baseDirectory = $baseDirectory;
        $this->isLocalisationRebuild = $isLocalisationRebuild;
    }

    private function addLocalization( $config ) {
        $settings = json_decode( file_get_contents( $config ), true );
        $path = implode( '/', array_slice( explode( '/', $config ), 0, -1 ) );

        $addPath = function ( $file ) use ( $path ) {
            return $path . '/' . $file;
        };

        if ( array_key_exists( 'ExtensionMessagesFiles', $settings ) ) {
            foreach ( $settings[ 'ExtensionMessagesFiles' ] as $key => $file ) {
                $this->extensionMessagesFiles[ $key ] = $addPath( $file );
            }
        }

        if ( array_key_exists( 'MessagesDirs', $settings ) ) {
            foreach ( $settings[ 'MessagesDirs' ] as $extension => $dirs ) {
                if ( is_array( $dirs ) ) {
                    $this->messagesDirs[ $extension ] = $dirs;
                } else {
                    $this->messagesDirs[ $extension ] = array( $dirs );
                }
                $this->messagesDirs[ $extension ] = array_map(
                    $addPath, $this->messagesDirs[ $extension ]
                );
            }
        }
    }

    public function loadExtension( $name ) {
        if ( $this->isLocalisationRebuild ) {
            self::addLocalization( "$this->baseDirectory/extensions/" . $name . "/extension.json" );
        }
    }
}
