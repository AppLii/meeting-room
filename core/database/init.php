<?php

require_once __DIR__ . '/../init.php';

require_once __DIR__ . '/AbstractTable.class.php';
require_once __DIR__ . '/AbstractRecord.class.php';
require_once __DIR__ . '/Database.class.php';

require_once __DIR__ . '/tables/init.php';
require_once __DIR__ . '/records/init.php';


Database::getInstance()->init();
