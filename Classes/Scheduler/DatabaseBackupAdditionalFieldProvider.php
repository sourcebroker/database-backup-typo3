<?php

namespace SourceBroker\TYPO3DatabaseBackup\Scheduler;

use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class DatabaseBackupAdditionalFieldProvider implements AdditionalFieldProviderInterface
{
    /**
     * Gets additional fields to render in the form to add/edit a task
     *
     * @param array $taskInfo
     * @param AbstractTask $task
     * @param SchedulerModuleController $schedulerModule
     *
     * @return array
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $additionalFields = [];

        if (empty($taskInfo['howMany'])) {
            if ($schedulerModule->CMD == 'add') {
                $taskInfo['howMany'] = [];
            } elseif ($schedulerModule->CMD == 'edit') {
                $taskInfo['howMany'] = $task->howMany;
            } else {
                $taskInfo['howMany'] = $task->howMany;
            }
        }

        // input for howMany
        $fieldId = 'task_howMany';
        $fieldCode = '<input name="tx_scheduler[howMany]" type="number" id="' . $fieldId . '" value="' . ($task->howMany ?: 5) . '" />';
        $additionalFields[$fieldId] = array(
            'code' => $fieldCode,
            'label' => 'How many backups'
        );

        return $additionalFields;
    }

    /**
     * Validates the additional fields' values
     *
     * @param array $submittedData
     * @param SchedulerModuleController $schedulerModule
     * @return bool
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        return true;
    }

    /**
     * Takes care of saving the additional fields' values in the task's object
     *
     * @param array $submittedData
     * @param AbstractTask $task
     * @return void
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->howMany = $submittedData['howMany'];
    }
}