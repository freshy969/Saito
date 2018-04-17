<?php

declare(strict_types = 1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Text;
use Saito\Posting\Posting;

class TitleComponent extends Component
{
    /**
     * {@inheritDoc}
     */
    public function beforeRender(Event $event)
    {
        $controller = $event->getSubject();
        $page = $this->getPageTitle($controller);
        $forum = $this->getForumName($controller);
        $title = $this->getTitleForLayout($controller, $page, $forum);
        $controller->set([
            'titleForPage' => $page,
            'forumName' => $forum,
            'titleForLayout' => $title
        ]);
    }

    /**
     * Get title for page shown on header on page
     *
     * @param Controller $controller The controller
     * @return string
     */
    protected function getPageTitle(Controller $controller): string
    {
        $controller = $this->getController();
        //= title for page, shown in default.ctp in header on page
        if (isset($controller->viewVars['titleForPage'])) {
            return $controller->viewVars['titleForPage'];
        }

        $ctrler = $controller->request->getParam('controller');
        $action = $controller->request->getParam('action');
        $key = lcfirst($ctrler) . '/' . $action;
        $page = __d('page_titles', $key);
        if ($key === $page) {
            $page = '';
        }

        return $page;
    }

    /**
     * Gets forum name
     *
     * @param Controller $controller The controller
     * @return string
     */
    public function getForumName(Controller $controller): string
    {
        return Configure::read('Saito.Settings.forum_name');
    }

    /**
     * title + forum name for layout, shown in HTML-<title>-tag
     *
     * @param Controller $controller The controller
     * @param string $page Title of the current page.
     * @param string $forum Title of the forum.
     * @return string
     */
    protected function getTitleForLayout(Controller $controller, string $page, string $forum): string
    {
        if (isset($controller->viewVars['titleForLayout'])) {
            $layout = $controller->viewVars['titleForLayout'];
        } else {
            $layout = $page;
        }
        if ($layout) {
            $layout = Text::insert(
                __('forum-title-template'),
                ['page' => $layout, 'forum' => $forum]
            );
        } else {
            $layout = $forum;
        }

        return $layout;
    }

    /**
     * Set title
     *
     * @param Posting $posting posting
     * @param string $type type
     * @return void
     */
    public function setFromPosting(Posting $posting, $type = null)
    {
        if ($type === null) {
            $template = __(':subject | :category');
        } else {
            $template = __(':subject (:type) | :category');
        }
        $this->getController()->set(
            'titleForLayout',
            Text::insert(
                $template,
                [
                    'category' => $posting->get('category')['category'],
                    'subject' => $posting->get('subject'),
                    'type' => $type
                ]
            )
        );
    }
}
