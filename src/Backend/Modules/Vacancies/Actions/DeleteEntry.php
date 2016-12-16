<?php

namespace Backend\Modules\Vacancies\Actions;

use Backend\Core\Engine\Base\ActionDelete;
use Backend\Core\Engine\Model;
use Backend\Modules\Vacancies\Engine\Entries as BackendVacanciesEntriesModel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * This is the delete-action, it deletes an item
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class DeleteEntry extends ActionDelete
{
    /**
     * Execute the action
     */
    public function execute()
    {
        $this->id = $this->getParameter('id', 'int');

        // does the item exist
        if ($this->id !== null && BackendVacanciesEntriesModel::exists($this->id)) {
            parent::execute();
            $this->record = (array) BackendVacanciesEntriesModel::get($this->id);

            BackendVacanciesEntriesModel::delete($this->id);

            $fs = new Filesystem();
            $fs->remove(FRONTEND_FILES_PATH . '/Vacancies/file/' . $this->record['file']);

            Model::triggerEvent(
                $this->getModule(), 'after_delete',
                array('id' => $this->id)
            );

            $this->redirect(
                Model::createURLForAction('Edit') . '&report=deleted&id=' . $this->record['vacancy_id']
            );
        } else {
            $this->redirect(Model::createURLForAction('Index') . '&error=non-existing');
        }
    }
}
