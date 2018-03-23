<?php

namespace SourceBroker\TYPO3DatabaseBackup\Scheduler;

use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DatabaseBackup
 */
class DatabaseBackup extends AbstractTask
{
    protected $tmpDir;

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        if (php_sapi_name() == "cli") {
            exec('./vendor/bin/backup db:default-configuration --key=tmpDir', $output);
            if (empty($output)) {
                throw new \Exception('Missing vendor/bin/backup application');
            }

            $this->tmpDir = $output[0];

            if (
                VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) <
                VersionNumberUtility::convertVersionNumberToInteger('8.0.0'))
            {
                $database = $GLOBALS['TYPO3_CONF_VARS']['DB']['database'] ?? '';
                $username = $GLOBALS['TYPO3_CONF_VARS']['DB']['username'] ?? '';
                $password = $GLOBALS['TYPO3_CONF_VARS']['DB']['password'] ?? '';
                $host = $GLOBALS['TYPO3_CONF_VARS']['DB']['host'] ?? '';
                $port = $GLOBALS['TYPO3_CONF_VARS']['DB']['port'] ?? '';
            } else {
                $database = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'] ?? '';
                $username = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user'] ?? '';
                $password = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['password'] ?? '';
                $host = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host'] ?? '';
                $port = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['port'] ?? '';
            }


            $mysql = "[mysql]\n";
            $mysqlDump = "[mysqldump]\n";
            $data = ""
                . "user = '{$username}'\n"
                . "password = '{$password}'\n"
                . "host = '{$host}'\n"
                . "port = '{$port}'\n"
                . "\n"
            ;
            $myCnfFile = $this->tmpDir . '/' . uniqid(rand(), true). '.cnf';
            file_put_contents($myCnfFile, $mysql . $data . $mysqlDump . $data);

            $data = [
                'configs' => [
                    $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] => [
                        'cron' => [
                            'pattern' => '* * * * *',
                            'howMany' => (int)($this->howMany ?: 5)
                        ],
                        'defaultsFile' => $myCnfFile,
                        'databases' => [
                            'whitelist' => [$database]
                        ]
                    ]
                ]
            ];

            $yaml = Yaml::dump($data);
            $yamlFile = $this->tmpDir . '/' . uniqid(rand(), true). '.yaml';
            file_put_contents($yamlFile, $yaml);

            exec("./vendor/bin/backup db:dump {$yamlFile}");

            if (file_exists($yamlFile)) {
                unlink($yamlFile);
            }

            if (file_exists($myCnfFile)) {
                unlink($myCnfFile);
            }

        } else {
            throw new \Exception('Only cli mode is valid');
        }
        return true;
    }
}