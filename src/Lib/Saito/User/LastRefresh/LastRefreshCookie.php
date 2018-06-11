<?php

namespace Saito\User\LastRefresh;

use App\Controller\Component\CurrentUserComponent;
use Saito\User\Cookie;

/**
 * handles last refresh time for current user via cookie
 *
 * used for non logged-in users
 */
class LastRefreshCookie extends LastRefreshAbstract
{

    protected $_Cookie;

    /**
     * {@inheritDoc}
     */
    public function __construct(CurrentuserComponent $CurrentUser)
    {
        $this->_CurrentUser = $CurrentUser;
        $this->_Cookie = new Cookie\Storage(
            $this->_CurrentUser->getController(),
            'lastRefresh'
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function _get()
    {
        if ($this->_timestamp === null) {
            $this->_timestamp = $this->_Cookie->read();
            if (empty($this->_timestamp)) {
                $this->_timestamp = false;
            } else {
                $this->_timestamp = strtotime($this->_timestamp);
            }
        }

        return $this->_timestamp;
    }

    /**
     * {@inheritDoc}
     */
    protected function _set()
    {
        $this->_Cookie->write($this->_timestamp);
    }
}