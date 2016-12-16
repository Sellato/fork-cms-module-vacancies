<?php

namespace Frontend\Modules\Vacancies\Widgets;


use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Modules\Vacancies\Engine\Model as FrontendVacanciesModel;
use Frontend\Modules\Vacancies\Engine\Categories as FrontendVacanciesCategoriesModel;

class Categories extends FrontendBaseWidget
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
        $this->tpl->assign('widgetVacanciesCategories', FrontendVacanciesCategoriesModel::getAll(array('parent_id' => 0)));
    }
}
