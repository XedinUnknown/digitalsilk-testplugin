<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport;

use Exception;
use WC_Logger_Interface;
use WC_Log_Levels;

/**
 * A logger decorator that allows specifying a default context.
 *
 * This context will be merged non-recursively with each context provided for logging.
 */
class CustomContextLogger implements WC_Logger_Interface
{
    protected WC_Logger_Interface $logger;
    protected array $defaultContext;

    public function __construct(WC_Logger_Interface $logger, array $defaultContext)
    {
        $this->logger = $logger;
        $this->defaultContext = $defaultContext;
    }

    /**
     * @inheritDoc
     */
    public function add($handle, $message, $level = WC_Log_Levels::NOTICE)
    {
        $this->logger->add($handle, $message, $level);
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, $context = [])
    {
        $context = array_merge($this->defaultContext, $context);
        $this->logger->log($level, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function emergency( $message, $context = array() ) {
        $this->log( WC_Log_Levels::EMERGENCY, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function alert( $message, $context = array() ) {
        $this->log( WC_Log_Levels::ALERT, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function critical( $message, $context = array() ) {
        $this->log( WC_Log_Levels::CRITICAL, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function error( $message, $context = array() ) {
        $this->log( WC_Log_Levels::ERROR, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function warning( $message, $context = array() ) {
        $this->log( WC_Log_Levels::WARNING, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function notice( $message, $context = array() ) {
        $this->log( WC_Log_Levels::NOTICE, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function info( $message, $context = array() ) {
        $this->log( WC_Log_Levels::INFO, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function debug( $message, $context = array() ) {
        $this->log( WC_Log_Levels::DEBUG, $message, $context );
    }
}
