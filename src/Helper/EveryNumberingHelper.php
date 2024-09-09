<?php

namespace EveryNumbering\Helper;

use EveryDataStore\Helper\EveryDataStoreHelper;
use EveryDataStore\Model\RecordSet\Form\FormField;
use EveryDataStore\Model\RecordSet\Form\FormFieldType;

/** EveryDataStore/EveryNumbering v1.0
 * 
 * This class is concerned with settings and values of the field dedicated to the 
 * everyNumbering value, i.e., unique identifier value of the RecordSetItem
 * 
 */
class EveryNumberingHelper extends EveryDataStoreHelper {

    /**
     * This function sets up a filed on a form if it does not already exist
     * The field shall hold numbering value
     * 
     * @param DataObject $recordSet
     * @param string $fieldName
     * @return string
     */
    public static function setFormField($recordSet, $fieldName) {
        $recordSetForm = $recordSet ? self::getFormFieldForm($recordSet) : null;
        $formFieldSection = $recordSetForm ? self::getFormFieldSection($recordSetForm) : null;
        $formFieldSectionColumn = $formFieldSection ? self::getFormFieldSectionColumn($formFieldSection) : null;
        $formField = $formFieldSectionColumn && $fieldName ? FormField::get()->filter(['Settings.Title' => 'label', 'Settings.Value' => $fieldName, 'ColumnID' => $formFieldSectionColumn->ID])->first() : null;
        if (!$formField) {
            $formField = new FormField;
        }

        $formField->FormFieldTypeID = self::getFiedlTypeBySlug('textfield') ? self::getFiedlTypeBySlug('textfield')->ID : 0;
        if (!$formField->ColumnID) {
            $formField->ColumnID = $formFieldSectionColumn ? $formFieldSectionColumn->ID : 0;
        }

        if (!$formField->Sort) {
            $formField->Sort = 0;
        }

        $formFieldID = $formField->write();

        self::setFormFieldSettings($formField, $fieldName);
        return $formFieldID;
    }

    /**
     * This function assigns appropriate numbering to the RecordSetItem
     * 
     * @param array $RecordEveryNumbering
     * @param string $itemID
     * @return DataObject
     */
    public static function setRecordSetItemNumbering($RecordEveryNumbering, $itemID) {
        foreach ($RecordEveryNumbering as $everyNumbering) {
            if ($everyNumbering->Active) {
                $existsNumbering = \EveryDataStore\Model\RecordSet\RecordSetItemData::get()->filter([
                            'FormField.Slug' => $everyNumbering->FormField()->Slug,
                            'RecordSetItemID' => $itemID])->first();

                if ($existsNumbering) {
                    return;
                }


                $counter = $everyNumbering->Counter + 1;
                $value = $everyNumbering->Prefix . $counter . $everyNumbering->Suffix;
                $args = [];
                $args[] = array(
                    'Value' => $value,
                    'Slug' => $everyNumbering->FormField()->Slug
                );

                $ItemData = new \EveryDataStore\Model\RecordSet\RecordSetItemData;
                $ItemData->Value = $value;
                $ItemData->FormFieldID = $everyNumbering->FormField()->ID;
                $ItemData->RecordSetItemID = $itemID;
                $ItemData->write();
                $everyNumbering->Counter = $counter;
                $everyNumbering->write();
            }
        }
    }

    /**
     * This function returns Form of the $recordSet
     * 
     * @param DataObject $recordSet
     * @return DataObject
     */
    private static function getFormFieldForm($recordSet) {
        return $recordSet->Form();
    }

    /**
     * This function returns the first section of the $recordSetForm
     * 
     * @param DataObject $recordSetForm
     * @return DataObject
     */
    private static function getFormFieldSection($recordSetForm) {
        return $recordSetForm->Sections()->sort('Sort ASC')->First();
    }

    /**
     * This function returns the first column of the $section
     * 
     * @param DataObject $section
     * @return DataObject
     */
    private static function getFormFieldSectionColumn($section) {
        return $section->Columns()->sort('Sort ASC')->First();
    }

    /**
     * This function configures field settings for everyNumbering field
     * 
     * @param DataObject $formdField
     * @param string $fieldName
     */
    private static function setFormFieldSettings($formdField, $fieldName) {
        self::cleanFormFieldSettings($formdField);
        \EveryRESTfulAPI\Custom\CustomRecordSet::setFormFieldSetting($formdField->ID, 'label', $fieldName);
        \EveryRESTfulAPI\Custom\CustomRecordSet::setFormFieldSetting($formdField->ID, 'resultlist', 'true');
        \EveryRESTfulAPI\Custom\CustomRecordSet::setFormFieldSetting($formdField->ID, 'readonly', 'true');
        \EveryRESTfulAPI\Custom\CustomRecordSet::setFormFieldSetting($formdField->ID, 'placeholder', $fieldName);
        \EveryRESTfulAPI\Custom\CustomRecordSet::setFormFieldSetting($formdField->ID, 'active', 'true');
    }

    /**
     * This function clears the everyNumbering field settings 
     * @param DataObject $formdField
     */
    private static function cleanFormFieldSettings($formdField) {
        if ($formdField->Settings()->Count() > 0) {
            foreach ($formdField->Settings() as $setting) {
                $setting->delete();
            }
        }
    }

    /**
     * This function returns the type of the formField with the given $slug
     * 
     * @param string $slug
     * @return string
     */
    private static function getFiedlTypeBySlug($slug) {
        return FormFieldType::get()->filter(['Slug' => $slug])->first();
    }
}
