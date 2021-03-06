<?php

namespace Ixolit\Dislo\CDE\EventEngine\Event\Billing;

use Ixolit\Dislo\CDE\EventEngine\Event\AbstractEvent;

/**
 * Class FlexibleActivated
 *
 * @package Ixolit\Dislo\CDE\EventEngine\Event\Billing
 */
class FlexibleActivated extends AbstractEvent {

    /**
     * @return \Ixolit\Dislo\WorkingObjects\User
     */
    public function getUser() {
        return $this->getContext()->getUser();
    }
    
    /**
     * @return \Ixolit\Dislo\WorkingObjects\Flexible
     */
    public function getFlexible() {
        return $this->getContext()->getFlexible();
    }

}