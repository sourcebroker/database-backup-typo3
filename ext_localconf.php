<?php

defined('TYPO3_MODE') or die();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['SourceBroker\\TYPO3DatabaseBackup\\Scheduler\\DatabaseBackup'] = [
    'extension' => $_EXTKEY,
    'title' => 'Database Backup',
    'description' => 'Do database backup',
    'additionalFields' => 'SourceBroker\\TYPO3DatabaseBackup\\Scheduler\\DatabaseBackupAdditionalFieldProvider'
];