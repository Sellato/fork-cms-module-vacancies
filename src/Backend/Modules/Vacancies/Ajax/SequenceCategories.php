<?php

namespace Backend\Modules\Vacancies\Ajax;

use Backend\Core\Engine\Base\AjaxAction;
use Backend\Modules\Vacancies\Engine\Category as BackendVacanciesCategoryModel;

/**
 * Alters the sequence of Vacancies articles
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class SequenceCategories extends AjaxAction
{
    public function execute()
    {
        parent::execute();

        // get parameters
        $newIdSequence = trim(\SpoonFilter::getPostValue('new_id_sequence', null, '', 'string'));

        // list id
        $ids = (array) explode(',', rtrim($newIdSequence, ','));

        // loop id's and set new sequence
        foreach ($ids as $i => $id) {
            $item['id'] = $id;
            $item['sequence'] = $i + 1;

            // update sequence
            if (BackendVacanciesCategoryModel::exists($id)) {
                BackendVacanciesCategoryModel::update($item);
            }
        }

        // success output
        $this->output(self::OK, null, 'sequence updated');
    }
}
