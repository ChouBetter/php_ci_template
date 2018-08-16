<?php

define('REDIS_HOST', 'localhost');
define('REDIS_PORT', 6379);
define('REDIS_SELECT', 0);

define('CONSUMER_KEY', substr(md5("CONSUMER_KEY"), -16));
define('CONSUMER_SECRET', substr(md5("CONSUMER_SECRET"), 8, 16));
define('CONSUMER_TTL', 86400 * 3);

define('SESSION_EXPIRE', 86400 * 3);

define('DEFAULT_PAGE_SIZE', 20);
