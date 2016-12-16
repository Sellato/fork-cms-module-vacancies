<?php

namespace Backend\Modules\Vacancies\Engine;

use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Language;
use Backend\Modules\Vacancies\Engine\Images as BackendVacanciesImagesModel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Backend\Modules\Vacancies\Engine\Entries as BackendVacanciesEntriesModel;

/**
 * In this file we store all generic functions that we will be using in the Vacancies module
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Model
{
    const QRY_DATAGRID_BROWSE =
        'SELECT i.id, c.name,  i.sequence, i.hidden
         FROM vacancies AS i
         INNER JOIN vacancy_content as c  on i.id = c.vacancy_id
         WHERE c.language = ? AND i.status = ? ORDER BY sequence DESC';

       /**
       * Get the maximum Team sequence.
       *
       * @return int
       */
      public static function getMaximumSequence()
      {
          return (int) BackendModel::get('database')->getVar(
              'SELECT MAX(i.sequence)
               FROM vacancies AS i'
          );
      }

     /**
      * Retrieve the unique URL for an item
      *
      * @param string $URL The URL to base on.
      * @param int    $id  The id of the item to ignore.
      * @return string
      */
     public static function getURL($URL, $language, $id = null)
     {
         $URL = (string) $URL;

         // get db
         $db = BackendModel::getContainer()->get('database');

         // new item
         if ($id === null) {
             // already exists
             if ((bool) $db->getVar(
                 'SELECT 1
                  FROM vacancies AS i
                  INNER JOIN vacancy_content AS m ON i.id = m.vacancy_id
                  WHERE m.language = ? AND m.url = ?
                  LIMIT 1',
                 array($language, $URL)
             )
             ) {
                 $URL = BackendModel::addNumber($URL);

                 return self::getURL($URL, $language);
             }
         } else {
             // current category should be excluded
             if ((bool) $db->getVar(
                 'SELECT 1
                  FROM vacancies AS i
                  INNER JOIN vacancy_content AS m ON i.id = m.vacancy_id
                  WHERE m.language = ? AND m.url = ? AND i.id != ?
                  LIMIT 1',
                 array($language, $URL, $id)
             )
             ) {
                 $URL = BackendModel::addNumber($URL);

                 return self::getURL($URL,$language, $id);
             }
         }

         return $URL;
     }

    /**
     * Delete a certain item
     *
     * @param int $id
     */
    public static function delete($id)
    {
        $entries = BackendVacanciesEntriesModel::getAllForVacancy($id);
        $fs = new Filesystem();
        foreach($entries as $entry){
          BackendVacanciesEntriesModel::delete($entry['id']);
          $fs->remove(FRONTEND_FILES_PATH . '/Vacancies/file/' . $entry['file']);

        }

        BackendModel::get('database')->delete('vacancies', 'id = ?', (int) $id);
        BackendModel::get('database')->delete('vacancy_content', 'vacancy_id = ?', (int) $id);
        BackendModel::get('database')->delete('vacancies_linked_catgories', 'vacancy_id = ?', (int) $id);

        $images = (array) BackendVacanciesImagesModel::getAll((int) $id);
        foreach($images as $image){
            BackendModel::deleteThumbnails(FRONTEND_FILES_PATH . '/' . BackendModel::get('url')->getModule() . '/uploaded_images',  $image['filename']);
        }

        BackendModel::get('database')->execute('DELETE c FROM vacancy_images_content c INNER JOIN vacancy_images i ON c.image_id = i.id WHERE i.vacancy_id = ?', array((int) $id));
        BackendModel::get('database')->delete('vacancy_images', 'vacancy_id = ?', (int) $id);
    }

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
             FROM vacancies AS i
             WHERE i.id = ?
             LIMIT 1',
            array((int) $id)
        );
    }

    /**
     * Fetches a certain item
     *
     * @param int $id
     * @return array
     */
    public static function get($id)
    {
        $db = BackendModel::get('database');

        $return =  (array) $db->getRecord(
            'SELECT i.*, UNIX_TIMESTAMP(i.publish_on) as publish_on
             FROM vacancies AS i
             WHERE i.id = ?',
            array((int) $id)
        );

        // data found
        $return['content'] = (array) $db->getRecords(
            'SELECT i.* FROM vacancy_content AS i
            WHERE i.vacancy_id = ?',
            array((int) $id), 'language');

        return  $return;

    }





    /**
     * Insert an item in the database
     *
     * @param array $item
     * @return int
     */
    public static function insert(array $item)
    {
        $item['created_on'] = BackendModel::getUTCDate();
        $item['edited_on'] = BackendModel::getUTCDate();

        return (int) BackendModel::get('database')->insert('vacancies', $item);
    }

    public static function insertContent(array $content)
    {
        foreach($content as &$item){

            if( BackendModel::get('fork.settings')->get('Vacancies', 'make_widget_per_vacancy') == true)
            {
                $data = [
                    'id' => $item['vacancy_id'],
                    'language' => $item['language'],
                    'extra_label' => 'Vacancy: ' . $item['name'],
                ];

                $item['extra_id'] = BackendModel::insertExtra(
                    'widget',
                    'Vacancies',
                    'Vacancy',
                    'Vacancy',
                    $data
                );
            }

            BackendModel::get('database')->insert('vacancy_content', $item);
        }
    }

    /**
     * Updates an item
     *
     * @param array $item
     */
    public static function update(array $item)
    {
        $item['edited_on'] = BackendModel::getUTCDate();

        BackendModel::get('database')->update(
            'vacancies', $item, 'id = ?', (int) $item['id']
        );
    }

    public static function updateContent(array $content, $id)
    {
        $db = BackendModel::get('database');
        foreach($content as $language => $row)
        {
            if( BackendModel::get('fork.settings')->get('Vacancies', 'make_widget_per_vacancy') == true && $row['extra_id'])
            {
                $data = [
                    'id' => $row['vacancy_id'],
                    'language' => $row['language'],
                    'extra_label' => 'Vacancy: ' . $row['name'],
                ];

                BackendModel::updateExtra($row['extra_id'], 'data', $data);
            }

            $db->update('vacancy_content', $row, 'vacancy_id = ? AND language = ?', array($id, $language));
        }
    }
}
