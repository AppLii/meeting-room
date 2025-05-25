<?php

require_once __DIR__ . '/_load.php';

enum UserRole
{
	case SYSTEM;
	case ADMIN;
	case USER;
	case UNAUTHENTICATED;
}
