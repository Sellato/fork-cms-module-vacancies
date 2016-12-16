<?php

namespace Frontend\Modules\Vacancies\Widgets;


use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Modules\Vacancies\Engine\Model as FrontendVacanciesModel;


class Recent extends FrontendBaseWidget
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
        $this->tpl->assign('widgetVacanciesRecent', FrontendVacanciesModel::getAll( $this->get('fork.settings')->get('Vacancies', 'overview_num_items_recent', 3) ));
    }
}
