<?php

namespace Backend\Modules\Vacancies\Actions;

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Core\Engine\Authentication;
use Backend\Core\Engine\DataGridDB;
use Backend\Core\Language\Language;
use Backend\Core\Engine\Model;
use Backend\Modules\Vacancies\Engine\Model as BackendVacanciesModel;
use Backend\Core\Engine\Form;
use Backend\Modules\Vacancies\Engine\Category as BackendVacanciesCategoryModel;

/**
 * This is the index-action (default), it will display the overview of Vacancies posts
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Index extends ActionIndex
{
    private $filter = [];

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        $this->setFilter();
        $this->loadForm();

        $this->loadDataGridVacancies();
        $this->loadDataGridVacanciesDrafts();
        $this->parse();
        $this->display();
    }

    /**
     * Load the dataGrid
     */
    protected function loadDataGridVacancies()
    {
        $query = 'SELECT i.id, c.name,  i.sequence, i.hidden
         FROM vacancies AS i
         INNER JOIN vacancy_content as c  on i.id = c.vacancy_id';

        if (isset($this->filter['categories']) && $this->filter['categories'] !== null && count($this->filter['categories'])) {
            $query .= ' INNER JOIN vacancies_linked_catgories AS cat ON i.id = cat.vacancy_id';
        }

        $query .= ' WHERE 1';

        $parameters = array();
        $query .= ' AND c.language = ?';
        $parameters[] = Language::getWorkingLanguage();

        $query .= ' AND i.status = ?';
        $parameters[] = 'active';

        if ($this->filter['value']) {
            $query .= ' AND c.name LIKE ?';
            $parameters[] = '%' . $this->filter['value'] . '%';
        }

        if (isset($this->filter['categories']) && $this->filter['categories'] !== null && count($this->filter['categories'])) {
            $query .= ' AND cat.category_id IN(' . implode(',', array_values($this->filter['categories'])) . ')';
        }

        $query .= 'GROUP BY i.id ORDER BY sequence DESC';

        $this->dataGridVacancies = new DataGridDB(
            $query,
            $parameters
        );

        $this->dataGridVacancies->enableSequenceByDragAndDrop();
        $this->dataGridVacancies->setURL($this->dataGridVacancies->getURL() . '&' . http_build_query($this->filter));

        $this->dataGridVacancies->setColumnAttributes(
            'name', array('class' => 'title')
        );

        // check if this action is allowed
        if (Authentication::isAllowedAction('Edit')) {
            $this->dataGridVacancies->addColumn(
                'edit', null, Language::lbl('Edit'),
                Model::createURLForAction('Edit') . '&amp;id=[id]',
                Language::lbl('Edit')
            );
            $this->dataGridVacancies->setColumnURL(
                'name', Model::createURLForAction('Edit') . '&amp;id=[id]'
            );
        }
    }

    /**
     * Load the dataGrid
     */
    protected function loadDataGridVacanciesDrafts()
    {
        $query = 'SELECT i.id, c.name,  i.sequence, i.hidden
         FROM vacancies AS i
         INNER JOIN vacancy_content as c  on i.id = c.vacancy_id';

        if (isset($this->filter['categories']) && $this->filter['categories'] !== null && count($this->filter['categories'])) {
            $query .= ' INNER JOIN vacancies_linked_catgories AS cat ON i.id = cat.vacancy_id';
        }

        $query .= ' WHERE 1';

        $parameters = array();
        $query .= ' AND c.language = ?';
        $parameters[] = Language::getWorkingLanguage();

        $query .= ' AND i.status = ?';
        $parameters[] = 'draft';



        if ($this->filter['value']) {
            $query .= ' AND c.name LIKE ?';
            $parameters[] = '%' . $this->filter['value'] . '%';
        }

        if (isset($this->filter['categories']) && $this->filter['categories'] !== null && count($this->filter['categories'])) {
            $query .= ' AND cat.category_id IN(' . implode(',', array_values($this->filter['categories'])) . ')';
        }


        $query .= 'GROUP BY i.id ORDER BY sequence DESC';

        $this->dataGridVacanciesDrafts = new DataGridDB(
            $query,
            $parameters
        );

        $this->dataGridVacanciesDrafts->enableSequenceByDragAndDrop();
        $this->dataGridVacanciesDrafts->setURL($this->dataGridVacanciesDrafts->getURL() . '&' . http_build_query($this->filter));

        $this->dataGridVacancies->setColumnAttributes(
            'name', array('class' => 'title')
        );

        // check if this action is allowed
        if (Authentication::isAllowedAction('Edit')) {
            $this->dataGridVacanciesDrafts->addColumn(
                'edit', null, Language::lbl('Edit'),
                Model::createURLForAction('Edit') . '&amp;id=[id]',
                Language::lbl('Edit')
            );
            $this->dataGridVacanciesDrafts->setColumnURL(
                'name', Model::createURLForAction('Edit') . '&amp;id=[id]'
            );
        }
    }

    /**
     * Load the form
     */
    private function loadForm()
    {
        $this->frm = new Form('filter', Model::createURLForAction(), 'get');

        $categories = BackendVacanciesCategoryModel::getForMultiCheckbox();

        $this->frm->addText('value', $this->filter['value']);

        if (!empty($categories) && Authentication::isAllowedAction('AddCategory')) {
            $this->frm->addMultiCheckbox(
                'categories',
                $categories,
                '',
                'noFocus'
            );
        }

        // manually parse fields
        $this->frm->parse($this->tpl);
    }


    /**
     * Sets the filter based on the $_GET array.
     */
    private function setFilter()
    {
        $this->filter['categories'] = $this->getParameter('categories', 'array');
        $this->filter['value'] = $this->getParameter('value') == null ? '' : $this->getParameter('value');
    }


    /**
     * Parse the page
     */
    protected function parse()
    {
        // parse the dataGrid if there are results
        $this->tpl->assign('dataGridVacancies', (string) $this->dataGridVacancies->getContent());
        $this->tpl->assign('dataGridVacanciesDraft', (string) $this->dataGridVacanciesDrafts->getContent());
    }
}
