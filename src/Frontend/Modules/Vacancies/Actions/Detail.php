<?php

namespace Frontend\Modules\Vacancies\Actions;

use Frontend\Core\Engine\Base\Block;
use Frontend\Core\Engine\Model;
use Frontend\Core\Engine\Navigation;
use Frontend\Modules\Vacancies\Engine\Model as FrontendVacanciesModel;
use Frontend\Modules\Vacancies\Engine\Categories as FrontendVacanciesCategoriesModel;
use Frontend\Core\Language\Language;
use Frontend\Core\Engine\Form as FrontendForm;
use Frontend\Modules\Vacancies\Engine\Entries as FrontendVacanciesEntriesModel;
use Frontend\Modules\SiteHelpers\Engine\Email as FrontendnSiteHelpersEmail;
use Frontend\Modules\SiteHelpers\Engine\Template as FrontendnSiteHelpersTemplate;
use Common\Uri as CommonUri;

/**
 * This is the index-action (default), it will display the overview of Vacancies posts
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Detail extends Block
{
    /**
     * The record
     *
     * @var    array
     */
    private $record;

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();
        $this->tpl->assignGlobal('hideContentTitle', true);
        $this->loadTemplate();
        $this->getData();
        $this->loadForm();
        $this->validateForm();
        $this->parse();
    }

    private function loadForm()
    {
        $this->frm = new FrontendForm('details');
        $this->frm->setAction($this->frm->getAction() . '#details');

        // create elements
        $this->frm->addText('first_name')->setAttributes(array('required' => null));
        $this->frm->addText('last_name')->setAttributes(array('required' => null));
        $this->frm->addText('email')->setAttributes(array('required' => null, 'type' => 'email'));
        $this->frm->addFile('file')->setAttributes(array('required' => null));
        $this->frm->addTextarea('description')->setAttributes(array('required' => null));
    }

    /**
     * Validate the form.
     */
    private function validateForm()
    {

        // is the form submitted
        if ($this->frm->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            // validate required fields
            $this->frm->getField('first_name')->isFilled(Language::err('FieldIsRequired'));
            $this->frm->getField('last_name')->isFilled(Language::err('FieldIsRequired'));
            if ($this->frm->getField('email')->isFilled(Language::err('FieldIsRequired'))) {
                $this->frm->getField('email')->isEmail(Language::err('EmailIsInvalid'));
            }
            $this->frm->getField('description')->isFilled(Language::err('FieldIsRequired'));

            if ($this->frm->getField('file')->isFilled(Language::err('FieldIsRequired'))) {
                //$this->frm->getField('file')->isAllowedMimeType(array(), Language::err('PdfAndDocOnly'));
            }

            // no errors?
            if ($this->frm->isCorrect()) {

                // build array
                $insert['first_name'] = $this->frm->getField('first_name')->getValue();
                $insert['last_name'] = $this->frm->getField('last_name')->getValue();
                $insert['email'] = $this->frm->getField('email')->getValue();
                $insert['description'] = $this->frm->getField('description')->getValue();
                $insert['vacancy_id'] = $this->record['id'];

                // file provided?
                if ($this->frm->getField('file')->isFilled()) {
                    // build the file name
                    $insert['file'] =  date('Y-m-d--h-i-s')
                        . '--' . CommonUri::getUrl($insert['first_name']
                        . '-' . $insert['last_name'])
                        . '.' . $this->frm->getField('file')->getExtension();

                    // upload the image & generate thumbnails
                    $this->frm->getField('file')->moveFile(FRONTEND_FILES_PATH . '/Vacancies/file/' . $insert['file'], '0775');
                }

                FrontendVacanciesEntriesModel::insert($insert);

                if ($this->record['form_entries_email']) {
                    $backendUrl = SITE_URL . Navigation::getBackendURLForBlock('edit', 'vacancies', FRONTEND_LANGUAGE, array('id' => $this->record['id']));

                    $emailMessage = FrontendnSiteHelpersTemplate::render(
                        FRONTEND_MODULES_PATH . '/Vacancies/Layout/Templates/Mails/NewEntry.html.twig',
                        array(
                            'backendUrl' => $backendUrl,
                            'data' => $insert
                        )
                    );

                    FrontendnSiteHelpersEmail::notify(
                        $this->record['form_entries_email'],
                        sprintf(Language::getMessage('NewEntrySubjectFor'), Language::getLabel('Vacancies')),
                        array(
                            'message' => $emailMessage
                        )
                    );
                }

                // redirect
                $this->redirect($this->record['full_url'] . '/' . Language::getAction('Success'));
            }
        }
    }



    /**
     * Get the data
     */
    private function getData()
    {
        $parameter = $this->URL->getParameter(0);

        if (empty($parameter)) {
            $this->redirect(Navigation::getURL(404));
        }

        // load revision
        if ($this->URL->getParameter('draft', 'bool')) {
            // get data
            $this->record = FrontendVacanciesModel::getDraft($parameter);

            // add no-index, so the draft won't get accidentally indexed
            $this->header->addMetaData(array('name' => 'robots', 'content' => 'noindex, nofollow'), true);
        } else {
            // get by URL
             $this->record = FrontendVacanciesModel::get($parameter);
        }

        if (empty($this->record)) {
            $this->redirect(Navigation::getURL(404));
        }

        // get status
        $this->status = $this->URL->getParameter(1);
        if ($this->status == Language::getAction('Success')) {
            $this->status = 'success';
        }
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        if ($this->get('fork.settings')->get('Vacancies', 'use_image_as_og_image') && $this->record['image']) {
            $this->header->addOpenGraphImage(FRONTEND_FILES_URL . '/Vacancies/image/1200x630/' . $this->record['image']);
        }

        // build Facebook  OpenGraph data
        $this->header->addOpenGraphData('title', $this->record['name'], true);
        $this->header->addOpenGraphData(
            'url',
            SITE_URL . $this->record['full_url'],
            true
        );
        $this->header->addOpenGraphData(
            'site_name',
            $this->get('fork.settings')->get('Core', 'site_title_' . FRONTEND_LANGUAGE, SITE_DEFAULT_TITLE),
            true
        );
        $this->header->addOpenGraphData('description', $this->record['seo_description'], true);

        // add into breadcrumb
        $this->breadcrumb->addElement($this->record['name']);
        // set meta
        $this->header->setPageTitle($this->record['seo_title'], ($this->record['seo_title_overwrite'] == 'Y'));
        $this->header->addMetaDescription(
            $this->record['seo_description'],
            ($this->record['seo_description_overwrite'] == 'Y')
        );

        $navigation = FrontendVacanciesModel::getNavigation($this->record['id']);
        $this->tpl->assign('navigation', $navigation);


        // assign item
        $this->tpl->assign('item', $this->record);

        // parse the form
        if (empty($this->status) && $this->record['allow_form_entries'] == 'Y') {
            $this->frm->parse($this->tpl);
        }

        // parse the form status
        if (!empty($this->status)) {
            $this->tpl->assign($this->status, true);
        }
    }

    /**
     * @return mixed
     */
    private function getLastParameter()
    {
        $numberOfParameters = count($this->URL->getParameters());
        return $this->URL->getParameter($numberOfParameters - 1);
    }
}
