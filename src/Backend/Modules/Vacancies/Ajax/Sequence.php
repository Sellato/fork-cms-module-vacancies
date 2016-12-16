<?php

namespace Backend\Modules\Vacancies\Ajax;

use Backend\Core\Engine\Base\AjaxAction;
use Backend\Modules\Vacancies\Engine\Model as BackendVacanciesModel;

/**
 * Alters the sequence of Vacancies articles
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Sequence extends AjaxAction
{
    public function execute()
    {
        parent::execute();

        // get parameters
        $newIdSequence = trim(\SpoonFilter::getPostValue('new_id_sequence', null, '', 'string'));

        // list id
        $ids = (array) explode(',', rtrim($newIdSequence, ','));

        $max = count($ids);
        
        // loop id's and set new sequence
        foreach ($ids as $i => $id) {
            $item['id'] = $id;
            $item['sequence'] = $max--;

            // update sequence
            if (BackendVacanciesModel::exists($id)) {
                BackendVacanciesModel::update($item);
            }
        }

        // success output
        $this->output(self::OK, null, 'sequence updated');
    }
}
