<?php

namespace Frontend\Modules\Vacancies\Engine;

use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Language;
use Frontend\Core\Engine\Navigation;
use Frontend\Modules\Vacancies\Engine\Images as FrontendVacanciesImagesModel;

/**
 * In this file we store all generic functions that we will be using in the Vacancies module
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Entries
{
    public static function insert($item)
    {
        // get db
        $item['created_on'] = FrontendModel::getUTCDate('Y-m-d H:i:s');
        $item['edited_on'] = FrontendModel::getUTCDate('Y-m-d H:i:s');
        $item['language'] = FRONTEND_LANGUAGE;

        // insert
        return FrontendModel::get('database')->insert('vacancies_entries', $item);
    }
}
