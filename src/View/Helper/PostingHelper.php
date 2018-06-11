<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\View\Helper;

use App\Model\Entity\Entry;
use Cake\Core\Configure;
use Cake\Event\Event;
use Saito\Event\SaitoEventManager;
use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Posting\PostingInterface;
use Saito\Thread\Renderer;

/**
 * Class PostingHelper
 *
 * @package App\View\Helper
 */
class PostingHelper extends AppHelper
{
    public $helpers = ['Form', 'Html', 'TimeH'];

    /**
     * @var array perf-cheat for renderers
     */
    protected $_renderers = [];

    /**
     * @var SaitoEventManager
     */
    protected $_SEM;

    /**
     * get paginated index
     *
     * @param int $tid tid
     * @param null|string $lastAction last action
     * @return string
     */
    public function getPaginatedIndexPageId(int $tid, ?string $lastAction = null): string
    {
        $params = [];
        if ($lastAction !== 'add') {
            $session = $this->request->getSession();
            if ($session->read('paginator.lastPage')) {
                $params[] = 'page=' . $session->read('paginator.lastPage');
            }
        }
        $params[] = 'jump=' . $tid;

        return '/?' . implode('&', $params);
    }

    /**
     * Get fast link for posting.
     *
     * @param BasicPostingInterface $posting posting
     * @param array $options options
     * @return string HTML
     */
    public function getFastLink(BasicPostingInterface $posting, array $options = [])
    {
        $options += ['class' => ''];
        $id = $posting->get('id');
        $webroot = $this->request->getAttribute('webroot');
        $url = "{$webroot}entries/view/{$id}";
        $link = "<a href=\"{$url}\" class=\"{$options['class']}\">" . $this->getSubject($posting) . '</a>';

        return $link;
    }

    /**
     * category select
     *
     * @param Entry $posting posting
     * @param array $categories categories
     *
     * @return string
     */
    public function categorySelect(Entry $posting, array $categories)
    {
        if (!$posting->isRoot()) {
            // Send category for easy access in entries/preview when answering
            // (not used when saved).
            return $this->Form->hidden('category_id');
        }

        $html = $this->Form->control(
            'category_id',
            [
                'class' => 'form-control',
                'div' => false,
                'options' => $categories,
                'empty' => true,
                'label' => [
                    'class' => 'col-form-label mr-3',
                    'value' => __('Category'),
                ],
                'tabindex' => 1,
                'error' => ['notEmpty' => __('error_category_empty')],
                'templates' => [
                    'inputContainer' => '{{content}}',
                ]
            ]
        );
        $html = $this->Html->div('form-group d-flex', $html);
        // $html = $this->Html->div('form-inline', $html);

        return $html;
    }

    /**
     * Render view counter
     *
     * @param BasicPostingInterface $posting posting
     *
     * @return string
     */
    public function views(BasicPostingInterface $posting)
    {
        return __('views_headline') . ': ' . $posting->get('views');
    }

    /**
     * renders a posting tree as thread
     *
     * @param PostingInterface $tree passed as reference to share CU-decorator "up"
     * @param array $options options
     *    - 'renderer' [thread]|mix
     * @return string
     * @internal param CurrentUser $CurrentUser current user
     */
    public function renderThread(PostingInterface $tree, array $options = [])
    {
        $options += [
            'lineCache' => $this->_View->get('LineCache'),
            'maxThreadDepthIndent' => (int)Configure::read(
                'Saito.Settings.thread_depth_indent'
            ),
            'renderer' => 'thread',
            'rootWrap' => false
        ];
        $renderer = $options['renderer'];
        unset($options['renderer']);

        if (isset($this->_renderers[$renderer])) {
            $renderer = $this->_renderers[$renderer];
        } else {
            $name = $renderer;
            switch ($name) {
                case 'mix':
                    $renderer = new Renderer\MixHtmlRenderer($this);
                    break;
                case 'thread':
                    $renderer = new Renderer\ThreadHtmlRenderer($this);
                    break;
                case (is_string($renderer)):
                    $renderer = new $renderer($this);
                    break;
                default:
                    $renderer = new Renderer\ThreadHtmlRenderer($this);
            }
            $this->_renderers[$name] = $renderer;
        }
        $renderer->setOptions($options);

        return $renderer->render($tree);
    }

    /**
     * Get badges
     *
     * @param PostingInterface $entry posting
     * @return string
     */
    public function getBadges(PostingInterface $entry)
    {
        $out = '';
        if ($entry->isPinned()) {
            $out .= '<i class="fa fa-thumb-tack" title="' . __('fixed') . '"></i> ';
        }
        // anchor for inserting solve-icon via FE-JS
        $out .= '<span class="solves ' . $entry->get('id') . '">';
        if ($entry->get('solves')) {
            $out .= $this->solvedBadge();
        }
        $out .= '</span>';

        $additionalBadges = $this->getSaitoEventManager()->dispatch(
            'Request.Saito.View.Posting.badges',
            ['posting' => $entry->toArray()]
        );
        if ($additionalBadges) {
            $out .= implode('', $additionalBadges);
        }

        return $out;
    }

    /**
     * Get solved badge
     *
     * @return string
     */
    public function solvedBadge()
    {
        return '<i class="fa fa-badge-solves solves-isSolved" title="' .
        __('Helpful entry') . '"></i>';
    }

    /**
     * This function may be called serveral hundred times on the front page.
     * Don't make ist slow, benchmark!
     *
     * @param BasicPostingInterface $posting posting
     * @return string
     */
    public function getSubject(BasicPostingInterface $posting)
    {
        return \h($posting->get('subject')) . ($posting->isNt() ? ' n/t' : '');
    }

    /**
     * Gets SaitoEventManager
     *
     * @return SaitoEventManager
     */
    private function getSaitoEventManager(): SaitoEventManager
    {
        if ($this->_SEM === null) {
            $this->_SEM = SaitoEventManager::getInstance();
        }

        return $this->_SEM;
    }
}