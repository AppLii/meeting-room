<?php

require_once __DIR__ . '/init.php';

enum UserRole
{
	case SYSTEM;
	case ADMIN;
	case USER;
	case UNAUTHENTICATED;
}
