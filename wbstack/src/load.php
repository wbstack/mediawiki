<?php

# Load things that are needed on all requests
# This is normally called in the shims

const WBSTACK_INFO_GLOBAL = 'WBStackInfo';

require_once __DIR__ . '/Info/WBStackInfo.php';
require_once __DIR__ . '/Logging/CustomSpi.php';
require_once __DIR__ . '/Logging/CustomLogger.php';