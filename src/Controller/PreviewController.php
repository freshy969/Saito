<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller;

use Api\Controller\ApiAppController;
use Cake\I18n\Time;
use Cake\View\Helper\IdGeneratorTrait;
use Saito\App\Registry;

/**
 * Class EntriesController
 *
 * @property EntriesTable $Entries
 */
class PreviewController extends ApiAppController
{
    use IdGeneratorTrait;

    /**
     * Generate posting preview for JSON frontend.
     *
     * @return \Cake\Network\Response|void
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function preview()
    {
        $this->Entries = $this->loadModel('Entries');

        $newEntry = [
            'id' => 'preview',
            'pid' => $this->request->getData('pid'),
            'subject' => $this->request->getData('subject'),
            'text' => $this->request->getData('text'),
            'category_id' => $this->request->getData('category_id'),
            'edited_by' => null,
            'fixed' => false,
            'solves' => 0,
            'views' => 0,
            'ip' => '',
            'time' => new Time()
        ];
        $newEntry = $this->Entries->prepareChildPosting($newEntry);
        $newEntry = $this->Entries->newEntity($newEntry);

        $errors = $newEntry->getErrors();

        if (empty($errors)) {
            // no validation errors
            $newEntry['user'] = $this->CurrentUser->getSettings();
            $newEntry['category'] = $this->Entries->Categories->find()
                ->where(['id' => $newEntry['category_id']])
                ->first();
            $posting = Registry::newInstance(
                '\Saito\Posting\Posting',
                ['rawData' => $newEntry->toArray()]
            );
            $this->set(compact('posting'));
        } else {
            // validation errors
            $jsonApiErrors = ['errors' => []];
            foreach ($errors as $field => $error) {
                $out = [
                    'meta' => ['field' => '#' . $this->_domId($field)],
                    'status' => '400',
                    'title' => __d('nondynamic', $field) . ": " . __d('nondynamic', current($error)),
                ];

                $jsonApiErrors['errors'][] = $out;
            }
            $this->autoRender = false;

            $this->response = $this->response
                ->withType('json')
                ->withStatus(400)
                ->withStringBody(json_encode($jsonApiErrors));

            return $this->response;
        }
    }
}