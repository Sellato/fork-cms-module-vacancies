<?php

namespace Frontend\Modules\Vacancies\Engine;

use Frontend\Core\Engine\Model as FrontendModel;

/**
 * In this file we store all generic functions that we will be using in the Vacancies module
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Images
{
    public static function getAll($id)
    {
        $db = FrontendModel::get('database');

        $return =  (array) $db->getRecords(
           'SELECT i.*, c.name
            FROM vacancy_images AS i
            INNER JOIN vacancy_images_content AS c on c.image_id = i.id
            WHERE i.vacancy_id = ? GROUP BY i.id ORDER BY i.sequence',
           array((int) $id)
       );

        return  $return;
    }
}
