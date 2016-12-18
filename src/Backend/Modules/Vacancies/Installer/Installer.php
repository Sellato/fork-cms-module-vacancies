<?php

namespace Backend\Modules\Vacancies\Installer;

use Backend\Core\Installer\ModuleInstaller;

/**
 * Installer for the Vacancies module
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Installer extends ModuleInstaller
{
    public function install()
    {
        // import the sql
        $this->importSQL(dirname(__FILE__) . '/Data/install.sql');

        // install the module in the database
        $this->addModule('Vacancies');

        // install the locale, this is set here beceause we need the module for this
        $this->importLocale(dirname(__FILE__) . '/Data/locale.xml');

        $this->setModuleRights(1, 'Vacancies');

        $this->setActionRights(1, 'Vacancies', 'Add');
        $this->setActionRights(1, 'Vacancies', 'AddCategory');
        //$this->setActionRights(1, 'Vacancies', 'AddImages');
        $this->setActionRights(1, 'Vacancies', 'Categories');
        $this->setActionRights(1, 'Vacancies', 'Delete');
        $this->setActionRights(1, 'Vacancies', 'DeleteCategory');
        $this->setActionRights(1, 'Vacancies', 'DeleteImage');
        $this->setActionRights(1, 'Vacancies', 'Edit');
        $this->setActionRights(1, 'Vacancies', 'EditCategory');
        $this->setActionRights(1, 'Vacancies', 'Index');

        $this->setActionRights(1, 'Vacancies', 'Sequence');
        $this->setActionRights(1, 'Vacancies', 'SequenceCategories');
        $this->setActionRights(1, 'Vacancies', 'SequenceImages');
        $this->setActionRights(1, 'Vacancies', 'UploadImages');
        $this->setActionRights(1, 'Vacancies', 'EditImage');
        $this->setActionRights(1, 'Vacancies', 'GetAllTags');

        $this->setActionRights(1, 'Vacancies', 'Settings');
        $this->setActionRights(1, 'Vacancies', 'GenerateUrl');
        $this->setActionRights(1, 'Vacancies', 'UploadImage');
        $this->setActionRights(1, 'Vacancies', 'ExportEntries');
        $this->setActionRights(1, 'Vacancies', 'DeleteEntry');

        $this->makeSearchable('Vacancies');

        // add extra's
        $subnameID = $this->insertExtra('Vacancies', 'block', 'Vacancies', null, null, 'N', 1000);
        $this->insertExtra('Vacancies', 'block', 'VacancyDetail', 'Detail', null, 'N', 1001);
        $this->insertExtra('Vacancies', 'widget', 'Recent', 'RecentVacancies', null, 'N', 1001);

        $navigationModulesId = $this->setNavigation(null, 'Modules');
        //$navigationModulesId = $this->setNavigation($navigationModulesId, 'Vacancies');
        $this->setNavigation($navigationModulesId, 'Vacancies', 'vacancies/index', array('vacancies/add', 'vacancies/edit', 'vacancies/index', 'vacancies/add_images', 'vacancies/edit_image'), 1);
        //$this->setNavigation($navigationModulesId, 'Categories', 'vacancies/categories', array('vacancies/add_category','vacancies/edit_category', 'vacancies/categories'), 2);

         // settings navigation
        $navigationSettingsId = $this->setNavigation(null, 'Settings');
        $navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
        $this->setNavigation($navigationModulesId, 'Vacancies', 'vacancies/settings');
    }
}
