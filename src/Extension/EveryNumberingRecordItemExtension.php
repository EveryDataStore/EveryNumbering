<?php

namespace EveryNumbering\Extension;

use EveryNumbering\Helper\EveryNumberingHelper;
use EveryDataStore\Helper\LoggerHelper;
use SilverStripe\ORM\DataExtension;

class EveryNumberingRecordSetItemExtension extends DataExtension {

    private static $db = [];
    private static $has_one = [];
    private static $has_many = [];
    private static $default_sort = "";
    private static $defaults = [];
    private $onActionName = null;
    private static $has_written = false;

    public function onBeforeWrite() {
        parent::onBeforeWrite();
    }

    public function onAfterWrite() {
        parent::onAfterWrite(); 
        if($this->owner->RecordSet()->EveryNumbering()->Count() > 0 && $this->owner->ID && $this->owner->Version > 0){
             LoggerHelper::info('EveryNumberRecordSetItemID ' . $this->owner->ID);
             EveryNumberingHelper::setRecordSetItemNumbering($this->owner->RecordSet()->EveryNumbering(), $this->owner->ID);
         } else {
             LoggerHelper::info('no EveryNumbering  ');
         } 
    }

    public function onBeforeDelete() {
        parent::onBeforeDelete();
    }

    public function onAfterDelete() {
        parent::onAfterDelete();
    }
}
