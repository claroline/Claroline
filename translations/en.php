<?php

use Claroline\CoreBundle\Library\Installation\Settings\DatabaseChecker;

return array(
    'welcome' => 'Welcome',
    'welcome_message' => 'This assistant will guide you through the platform installation.',
    'install_language' => 'Installation language',
    'requirements_check' => 'Configuration checking',
    'failed_requirement_msg' => 'The application will not run correctly on your current configuration. Please fix the items highlighted in red and refresh this page.',
    'additional_failed_recommendation_msg'
        => 'It is also recommended to fix any item highlighted in orange, as it indicates settings that may impact negatively the application behaviour or performance.',
    'failed_recommendation_msg'
        => 'Your configuration meet the minimal requirements to run the application, but some settings may impact negatively its behaviour or performance. To solve this problem, fix the items highlighted in orange and refresh this page.',
    'correct_configuration_msg' => 'Your configuration meets all the requirements and recommendations to run the application correctly.',
    'correct_config' => 'Your configuration is correct.',
    'PHP version' => 'PHP version',
    'PHP version must be at least %version% (installed version is %installed_version%)'
        => 'PHP version must be at least %version% (installed version is %installed_version%)',
    'PHP version 5.3.16 has known bugs which will prevent the application from working properly'
        => 'PHP version 5.3.16 has known bugs which will prevent the application from working properly.',
    'PHP versions prior to 5.3.8 have known bugs which may prevent the application from working properly'
        => 'PHP versions prior to 5.3.8 have known bugs which may prevent the application from working properly.',
    'PHP version 5.4.0 has known bugs which may prevent the application from working properly'
        => 'PHP version 5.4.0 has known bugs which may prevent the application from working properly.',
    'PHP configuration' => 'PHP configuration',
    'Parameter date.timezone must be set in your php.ini' => 'Parameter <em>date.timezone</em> must be set in your <em>php.ini</em>.',
    'Your default timezone (%timezone%) is not supported' => 'Your default timezone (<em>%timezone%</em>) is not supported.',
    'Parameter %parameter% must be set to %value% in your php.ini' => 'Parameter <em>%parameter%</em> must be set to <em>%value%</em> in your php.ini.',
    'Parameter %parameter% should be set to %value% in your php.ini' => 'Parameter <em>%parameter%</em> should be set to <em>%value%</em> in your php.ini.',
    'PHP extensions' => 'PHP extensions',
    'Extension %extension% must be installed and enabled' => 'Extension <em>%extension%</em> must be installed and enabled.',
    'Extension %extension% should be installed and enabled' => 'Extension <em>%extension%</em> should be installed and enabled.',
    'PDO must have some drivers installed (i.e. for MySQL, PostgreSQL, etc.)'
        => 'PDO must have some drivers installed (i.e. for MySQL, PostgreSQL, etc.).',
    'A PHP accelerator (like APC or XCache) should be installed and enabled (highly recommended)'
        => 'A PHP accelerator (like APC or XCache) should be installed and enabled (highly recommended).',
    'APC version must be at least %version%' => 'APC version must be at least %version%.',
    'Extension %extension% should not be enabled' => 'Extension <em>%extension%</em> should not be enabled.',
    'Parameter %parameter% should be above 100 in php.ini' => 'Parameter <em>%parameter%</em> should be above 100 in your php.ini.',
    'File permissions' => 'File permissions',
    'The directory %directory% must be writable' => 'The directory <em>%directory%</em> must be writable.',
    'The file %file% must be writable' => 'The file <em>%file%</em> must be writable.',
    'database_parameters' => 'Database parameters',
    'driver' => 'Driver',
    'host' => 'Host',
    'database' => 'Database',
    'user' => 'User',
    'password' => 'Password',
    'port' => 'Port',
    DatabaseChecker::NOT_BLANK_EXPECTED => 'This value should not be blank',
    DatabaseChecker::NUMBER_EXPECTED => 'This value should be a positive number',
    DatabaseChecker::INVALID_DRIVER => 'Invalid driver',
    DatabaseChecker::DATABASE_NOT_EMPTY
        => 'The database you have selected is not empty. Please choose another one or let this installer create it for you.',
    DatabaseChecker::CANNOT_CONNECT_TO_SERVER
        => 'The connection with the database server cannot be established. Please check the parameters you provided are correct.',
    DatabaseChecker::CANNOT_CONNECT_OR_CREATE
        => 'The connection with the database cannot be established and the database cannot be created. Please check that the database user you have selected has sufficient permissions.',
    'platform_parameters' => 'Platform parameters',
    'platform_language' => 'Language',
    'previous_step' => 'Previous',
    'next_step' => 'Next'
);
