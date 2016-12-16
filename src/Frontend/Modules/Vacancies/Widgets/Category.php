<?php

namespace Frontend\Modules\Vacancies\Widgets;

use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Modules\Vacancies\Engine\Model as FrontendVacanciesModel;

class Category extends FrontendBaseWidget
{
    /**
     * Execute the extra
     */
    public function execute()
    {
        parent::execute();
        $this->loadTemplate();
        $this->parse();
    }

    /**
     * Parse
     */
    private function parse()
    {
        if (isset($this->data['id'])) {
            $filter['categories'][] = $this->data['id'];
            $this->tpl->assign('widgetVacanciesCategory', FrontendVacanciesModel::getAll(3, 0, $filter));
        }
    }
}
