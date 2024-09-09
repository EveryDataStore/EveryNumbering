<?php

namespace EveryNumbering\Extension;

use EveryNumbering\Helper\EveryNumberingHelper;
use EveryNumbering\Model\EveryNumbering;
use SilverStripe\ORM\DataExtension;

class EveryNumberingRecordExtension extends DataExtension {

    private static $db = [];
    private static $has_one = [];
    private static $has_many = [
        'EveryNumbering' => EveryNumbering::class
    ];
    private static $default_sort = "";
    private static $defaults = [];
    private $onActionName = null;
    private static $has_written = false;

    public function onBeforeWrite() {
         parent::onBeforeWrite();
    }

    public function onAfterWrite() {
        parent::onAfterWrite();      
    }

    public function onBeforeDelete() {
        parent::onBeforeDelete();
    }

    public function onAfterDelete() {
        parent::onAfterDelete();
        if ($this->owner->EveryNumbering()->Count() > 0) {
            foreach ($this->owner->EveryNumbering() as $everyNumbering) {
                $everyNumbering->delete();
            }
        }
    }
}
