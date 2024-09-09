<?php

namespace EveryNumbering\Model;

use EveryNumbering\Helper\EveryNumberingHelper;
use EveryDataStore\Model\RecordSet\RecordSet;
use EveryDataStore\Model\RecordSet\Form\FormField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Core\ClassInfo;

class EveryNumbering extends DataObject implements PermissionProvider 
{
    private static $table_name = 'EveryNumbering';
    private static $singular_name = 'EveryNumbering';
    private static $plural_name = 'EveryNumbering';
    private static $db = [
        'Slug' => 'Varchar(110)',
        'Active' => 'Boolean',
        'FieldName' => 'Varchar(110)',
        'StartsBy' => 'Int(11)',
        'Prefix' => 'Varchar(30)',
        'Suffix' => 'Varchar(30)',
        'Counter' => 'Int(11)'
    ];
    
    private static $has_one = [
        'RecordSet' => RecordSet::class,
        'FormField' => FormField::class,
    ];
    
    private static $many_many = [];
    private static $has_many = [];
    
    private static $hidden_fields = [];
    
    private static $summary_fields = [
        'Active',
        'FieldName',
        'StartsBy',
        'Prefix',
        'Suffix',
        'Counter',
    ];
 
    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels(true);
        if(!empty(self::$summary_fields)){
           $labels = EveryNumberingHelper::getNiceFieldLabels($labels, __CLASS__, self::$summary_fields);
        }
        return $labels;
    }
    
    private static $searchable_fields = [
        'FieldName' => [
            'field' => TextField::class,
            'filter' => 'PartialMatchFilter',
        ]
    ];
    
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->removeFieldFromTab('Root.Main', ['FormField','RecordSet', 'ThemeColor','CurrentDataStore', 'AdminID', 'Slug', 'Active', 'SendPasswordResetLink', 'DirectGroups', 'UpdatedByID', 'CreatedByID', 'RESTFulTokenExpire', 'FailedLoginCount', 'Password']);

        $fields->RemoveByName(['Counter', 'FormField', 'RecordSet']);
        $fields->addFieldToTab('Root.Main', HiddenField::create('Slug', 'Slug', $this->Slug), 'RecordSet');
        $fields->addFieldToTab('Root.Main', CheckboxField::create('Active', _t(__Class__ .'.ACTIVE', 'Active')));
        $fields->addFieldToTab('Root.Main', TextField::create('FieldName', _t(__Class__ .'.FIELDNAME', 'FieldName')));
        $fields->addFieldToTab('Root.Main', TextField::create('StartsBy', _t(__Class__ .'.STARTSBY', 'StartsBy')));
        $fields->addFieldToTab('Root.Main', TextField::create('Prefix', _t(__Class__ .'.PREFIX', 'Prefix')));
        $fields->addFieldToTab('Root.Main', TextField::create('Suffix', _t(__Class__ .'.SUFFIX', 'Suffix')));
        return $fields;
    }
     
    public function onBeforeWrite() {
        parent::onBeforeWrite();
        if (!$this->Slug) {
            $this->Slug = EveryNumberingHelper::getAvailableSlug(__CLASS__);
        }
        $this->FormFieldID = EveryNumberingHelper::setFormField($this->RecordSet(), $this->FieldName);
    }

    public function onAfterWrite() {
        parent::onAfterWrite();
    }

    public function onBeforeDelete() {
        parent::onBeforeDelete();
    }

    public function onAfterDelete() {
        if ($this->FormField()) {
             $this->FormField()->delete();
        }
        parent::onAfterDelete();
    }

    /**
     * This function should return true if the current user can view an object
     * @see Permission code VIEW_CLASSSHORTNAME e.g. VIEW_MEMBER
     * @param Member $member The member whose permissions need checking. Defaults to the currently logged in user.
     * @return bool True if the the member is allowed to do the given action
     */
    public function canView($member = null) {
        return EveryNumberingHelper::checkPermission(EveryNumberingHelper::getNicePermissionCode("VIEW", $this));
    }

    /**
     * This function should return true if the current user can edit an object
     * @see Permission code VIEW_CLASSSHORTNAME e.g. EDIT_MEMBER
     * @param Member $member The member whose permissions need checking. Defaults to the currently logged in user.
     * @return bool True if the the member is allowed to do the given action
     */
    public function canEdit($member = null) {
        return EveryNumberingHelper::checkPermission(EveryNumberingHelper::getNicePermissionCode("EDIT", $this));
    }

    /**
     * This function should return true if the current user can delete an object
     * @see Permission code VIEW_CLASSSHORTNAME e.g. DELTETE_MEMBER
     * @param Member $member The member whose permissions need checking. Defaults to the currently logged in user.
     * @return bool True if the the member is allowed to do the given action
     */
    public function canDelete($member = null) {
        return EveryNumberingHelper::checkPermission(EveryNumberingHelper::getNicePermissionCode("DELETE", $this));
    }

    /**
     * This function should return true if the current user can create new object of this class.
     * @see Permission code VIEW_CLASSSHORTNAME e.g. CREATE_MEMBER
     * @param Member $member The member whose permissions need checking. Defaults to the currently logged in user.
     * @param array $context Context argument for canCreate()
     * @return bool True if the the member is allowed to do this action
     */
    public function canCreate($member = null, $context = []) {
        return EveryNumberingHelper::checkPermission(EveryNumberingHelper::getNicePermissionCode("CREATE", $this));
    }

    /**
     * Return a map of permission codes for the Dataobject and they can be mapped with Members, Groups or Roles
     * @return array 
     */
    public function providePermissions() {
        return array(
            EveryNumberingHelper::getNicePermissionCode("CREATE", $this) => [
                'name' => _t('SilverStripe\Security\Permission.CREATE', "CREATE"),
                'category' => ClassInfo::shortname($this),
                'sort' => 1
            ],
            EveryNumberingHelper::getNicePermissionCode("EDIT", $this) => [
                'name' => _t('SilverStripe\Security\Permission.EDIT', "EDIT"),
                'category' => ClassInfo::shortname($this),
                'sort' => 1
            ],
            EveryNumberingHelper::getNicePermissionCode("VIEW", $this) => [
                'name' => _t('SilverStripe\Security\Permission.VIEW', "VIEW"),
                'category' => ClassInfo::shortname($this),
                'sort' => 1
            ],
            EveryNumberingHelper::getNicePermissionCode("DELETE", $this) => [
                'name' => _t('SilverStripe\Security\Permission.DELETE', "DELETE"),
                'category' => ClassInfo::shortname($this),
                'sort' => 1
        ]);
    }
}
