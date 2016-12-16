<?php

namespace Backend\Modules\Vacancies\Engine;

use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Language;
use Backend\Modules\Vacancies\Engine\Images as BackendVacanciesImagesModel;

/**
 * In this file we store all generic functions that we will be using in the Vacancies module
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Entries
{
    const QRY_DATAGRID_BROWSE =
        'SELECT i.id, CONCAT(i.first_name, " ", i.last_name) as name, i.email, i.file
         FROM vacancies_entries AS i
         WHERE i.vacancy_id = ? ORDER BY created_on DESC';



   /**
    * Checks if a certain item exists
    *
    * @param int $id
    * @return bool
    */
   public static function exists($id)
   {
       return (bool) BackendModel::get('database')->getVar(
           'SELECT 1
            FROM vacancies_entries AS i
            WHERE i.id = ?
            LIMIT 1',
           array((int) $id)
       );
   }

   /**
    * Delete a certain item
    *
    * @param int $id
    */
   public static function delete($id)
   {
       BackendModel::get('database')->delete('vacancies_entries', 'id = ?', (int) $id);
   }

    public static function get($id)
    {
        $db = BackendModel::get('database');

        $return =  (array) $db->getRecord(
           'SELECT i.*
            FROM vacancies_entries AS i
            WHERE i.id = ?',
           array((int) $id)
       );


        return  $return;
    }

    public static function getAllForVacancy($id)
    {
        $db = BackendModel::get('database');

        $fileUrl = SITE_URL . FRONTEND_FILES_URL . '/Vacancies/file/';

        $return =  (array) $db->getRecords(
             'SELECT i.id,i.first_name,  i.last_name, i.email, i.file
              FROM vacancies_entries AS i
              WHERE i.vacancy_id = ?',
             array((int) $id)
         );

        foreach ($return as &$record) {
            if (!empty($record['file'])) {
                $record['file_url'] = $fileUrl . $record['file'];
            }
        }

        return  $return;
    }
}
