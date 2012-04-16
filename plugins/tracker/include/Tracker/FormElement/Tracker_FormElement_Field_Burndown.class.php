<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Tracker_FormElement_Field_ReadOnly.class.php';
require_once dirname(__FILE__).'/../Chart/Data/LinkedArtifacts.class.php';
require_once dirname(__FILE__).'/../Chart/Burndown.class.php';

class Tracker_FormElement_Field_Burndown extends Tracker_FormElement_Field implements Tracker_FormElement_Field_ReadOnly {
    
    /**
     * Request parameter to display burndown image 
     */
    const FUNC_SHOW_BURNDOWN          = 'show_burndown';
    
    const REMAINING_EFFORT_FIELD_NAME = 'remaining_effort';
    const DURATION_FIELD_NAME         = 'duration';
    const START_DATE_FIELD_NAME       = 'start_date';
    
    public $default_properties = array();
    
    /**
     * @var Tracker_HierarchyFactory
     */
    private $hierarchy_factory;
    
    /**
     * Returns the previously injected factory (e.g. in tests), or a new
     * instance (e.g. in production).
     * 
     * @return Tracker_HierarchyFactory
     */
    public function getHierarchyFactory() {
        if ($this->hierarchy_factory == null) {
            $this->hierarchy_factory = Tracker_HierarchyFactory::build();
        }
        return $this->hierarchy_factory;
    }
    
    /**
     * Provides a way to inject the HierarchyFactory, since it cannot be done
     * in the constructor.
     * 
     * @param Tracker_HierarchyFactory $hierarchy_factory 
     */
    public function setHierarchyFactory($hierarchy_factory) {
        $this->hierarchy_factory = $hierarchy_factory;
    }
    
    public function getCriteriaFrom($criteria) {
    }
    
    public function getCriteriaWhere($criteria) {
    }
    
    public function getQuerySelect() {
    }
    
    public function getQueryFrom() {
    }
    
    public function fetchChangesetValue($artifact_id, $changeset_id, $value, $from_aid = null) {
    }
    
    public function fetchCSVChangesetValue($artifact_id, $changeset_id, $value) {
    }
    
    public function fetchCriteriaValue($criteria) {
    }

    public function fetchRawValue($value) {
    }
    
    protected function getCriteriaDao() {
    }
    
    protected function fetchSubmitValue() {
    }

    protected function fetchSubmitValueMasschange() {
    }

    protected function getValueDao() {
    }

    public function afterCreate() {
    }

    public function fetchFollowUp($artifact, $from, $to) {
    }
    
    public function fetchRawValueFromChangeset($changeset) {
    }
    
    protected function saveValue($artifact, $changeset_value_id, $value, Tracker_Artifact_ChangesetValue $previous_changesetvalue = null) {
    }
    
    protected function keepValue($artifact, $changeset_value_id, Tracker_Artifact_ChangesetValue $previous_changesetvalue) {
    }
    
    public function getChangesetValue($changeset, $value_id, $has_changed) {
    }
    
    public function getSoapAvailableValues() {
    }

    public function fetchArtifactValue(Tracker_Artifact $artifact, Tracker_Artifact_ChangesetValue $value = null, $submitted_values = array()) {
        return $this->fetchArtifactValueReadOnly($artifact, $value);
    }
    
    /**
     * Fetch the html code to display the field value in artifact in read only mode
     *
     * @param Tracker_Artifact                $artifact The artifact
     * @param Tracker_Artifact_ChangesetValue $value    The actual value of the field
     *
     * @return string
     */
    public function fetchArtifactValueReadOnly(Tracker_Artifact $artifact, Tracker_Artifact_ChangesetValue $value = null) {
        $html = '';

        $url_query = http_build_query(array('formElement' => $this->getId(),
                                            'func'        => self::FUNC_SHOW_BURNDOWN,
                                            'src_aid'     => $artifact->getId()));
        
        $html .= '<img src="'. TRACKER_BASE_URL .'/?'.$url_query.'" alt="'.$this->getLabel().'" width="640" height="480" />';
        return $html;
    }

    /**
     *
     * @param Tracker_IDisplayTrackerLayout $layout
     * @param Codendi_Request               $request
     * @param User                          $current_user 
     */
    public function process(Tracker_IDisplayTrackerLayout $layout, $request, $current_user) {
        switch ($request->get('func')) {
            case self::FUNC_SHOW_BURNDOWN:
                try  {
                    $artifact_id = $request->getValidated('src_aid', 'uint', 0);
                    $artifact    = Tracker_ArtifactFactory::instance()->getArtifactById($artifact_id);
                    if (! $artifact) {
                        return false;
                    }
                    $this->fetchBurndownImage($artifact);
                } catch (Exception $e) {
                    $this->displayErrorImage($GLOBALS['Language']->getText('plugin_tracker', $e->getMessage()));
                }
                break;
            default:
                parent::process($layout, $request, $current_user);
        }
    }
    
    /**
     * Display a png image with the given error message
     * 
     * @param String $msg 
     */
    protected function displayErrorImage($msg) {
        $error = new Chart(640, 480);
        $error->displayMessage($msg);
    }

    /**
     * Render a burndown image based on $artifact artifact links
     * 
     * @param Tracker_Artifact $artifact 
     */
    public function fetchBurndownImage(Tracker_Artifact $artifact) {
        $burndown = $this->getBurndown($this->getLinkedArtifacts($artifact));
        $burndown->setStartDate($this->getBurndownStartDate($artifact));
        $burndown->setDuration($this->getBurndownDuration($artifact));
        $burndown->setTitle($this->getLabel());
        $burndown->setDescription($this->getDescription());
        $burndown->display();
    }
    
    /**
     * Returns linked artifacts
     * 
     * @param Tracker_Artifact $artifact
     * 
     * @return Array of Tracker_Artifact
     * 
     * @throws Exception 
     */
    private function getLinkedArtifacts(Tracker_Artifact $artifact) {
        $linked_artifacts = $artifact->getLinkedArtifacts();
        if (count($linked_artifacts)) {
            return $linked_artifacts;
        }
        throw new Exception('burndown_no_linked_artifacts');
    }
    
    /**
     * Returns a Burndown rendering object for given linked artifacts
     * 
     * @param Array of Tracker_Artifact $linked_artifacts
     * 
     * @return \Tracker_Chart_Burndown 
     */
    protected function getBurndown($linked_artifacts) {
        $burndown_data = new Tracker_Chart_Burndown_Data_LinkedArtifacts($linked_artifacts, self::REMAINING_EFFORT_FIELD_NAME);
        return new Tracker_Chart_Burndown($burndown_data);
    }

    /**
     * Returns the sprint start_date as a Timestamp field value of given artifact
     * 
     * @param Tracker_Artifact $artifact
     * 
     * @return Integer
     */
    private function getBurndownStartDate(Tracker_Artifact $artifact) {
        $start_date_field = $this->getFormElementFactory()->getFormElementByName($artifact->getTracker()->getId(), self::START_DATE_FIELD_NAME);
        if ($start_date_field) {
            if ($timestamp = $artifact->getValue($start_date_field)->getTimestamp()) {
                return $timestamp;
            }
            throw new Exception('burndown_empty_start_date_warning');
        }
        throw new Exception('burndown_missing_start_date_warning');
    }

    /**
     * Returns the sprint duration for burndown rendering
     * 
     * @param Tracker_Artifact $artifact
     * 
     * @return Integer
     */
    private function getBurndownDuration(Tracker_Artifact $artifact) {
        $field = $this->getFormElementFactory()->getFormElementByName($artifact->getTracker()->getId(), self::DURATION_FIELD_NAME);
        if ($field) {
            if ($duration = $artifact->getValue($field)->getValue()) {
                return $duration;
            }
            throw new Exception('burndown_empty_duration_warning');
        }
        throw new Exception('burndown_missing_duration_warning');
    }
        
    /**
     * Fetch data to display the field value in mail
     *
     * @param Tracker_Artifact                $artifact         The artifact
     * @param Tracker_Artifact_ChangesetValue $value            The actual value of the field
     * @param string                          $format           output format
     *
     * @return string
     */
    public function fetchMailArtifactValue(Tracker_Artifact $artifact, Tracker_Artifact_ChangesetValue $value = null, $format='text') {
        $output = '';
        //TODO: What to send in email?
        return $output;
    }

    /**
     * Display the html field in the admin ui
     * @return string html
     */
    public function fetchAdminFormElement() {
        $html = $this->fetchWarnings();
        $html .= '<img src="'. TRACKER_BASE_URL .'/images/fake-burndown-admin.png" />';
        return $html;
    }
    
    /**
     * Renders all the possible warnings for this field.
     * 
     * @return String
     */
    private function fetchWarnings() {
        $warnings  = '';
        $warnings .= $this->fetchMissingFieldWarning(self::START_DATE_FIELD_NAME, 'date');
        $warnings .= $this->fetchMissingFieldWarning(self::DURATION_FIELD_NAME, 'int');
        $warnings .= $this->fetchMissingRemainingEffortWarning();
        
        if ($warnings) {
            return '<ul class="feedback_warning">'.$warnings.'</ul>';
        }
    }
    
    /**
     * Renders a warning concerning some missing field in the tracker.
     * 
     * @param String $name
     * @param String $type
     * @return String 
     */
    private function fetchMissingFieldWarning($name, $type) {
        if (! $this->getTracker()->hasFormElementWithNameAndType($name, $type)) {
            $key     = "burndown_missing_${name}_warning";
            $warning = $GLOBALS['Language']->getText('plugin_tracker', $key);
            
            return '<li>'.$warning.'</li>';
        }
    }
    
    /**
     * Renders a warning concerning some child tracker(s) missing a remaining
     * effort field.
     * 
     * @return String
     */
    private function fetchMissingRemainingEffortWarning() {
        $tracker_links = implode(', ', $this->getLinksToChildTrackersWithoutRemainingEffort());
        
        if ($tracker_links) {
            $warning = $GLOBALS['Language']->getText('plugin_tracker', 'burndown_missing_remaining_effort_warning');
            return "<li>$warning $tracker_links.</li>";
        }
    }
    
    /**
     * Returns the names of child trackers missing a remaining effort.
     * 
     * @return array of String
     */
    private function getLinksToChildTrackersWithoutRemainingEffort() {
        return array_map(array($this, 'getLinkToTracker'),
                         $this->getChildTrackersWithoutRemainingEffort());
    }
    
    /**
     * Renders a link to the given tracker.
     * 
     * @param Tracker $tracker
     * @return String
     */
    private function getLinkToTracker(Tracker $tracker) {
        $tracker_id   = $tracker->getId();
        $tracker_name = $tracker->getName();
        $tracker_url  = TRACKER_BASE_URL."/?tracker=$tracker_id&func=admin-formElements";
        
        $hp = Codendi_HTMLPurifier::instance();
        return '<a href="'.$tracker_url.'">'.$hp->purify($tracker_name).'</a>';
    }
    
    /**
     * Returns the child trackers missing a remaining effort.
     * 
     * @return array of Tracker
     */
    private function getChildTrackersWithoutRemainingEffort() {
        return array_filter($this->getChildTrackers(),
                            array($this, 'missesRemainingEffort'));
    }
    
    /**
     * Returns true if the given tracker misses a remaining effort field.
     * 
     * @param Tracker $tracker
     * @return Boolean
     */
    private function missesRemainingEffort(Tracker $tracker) {
        return ! $this->hasRemainingEffort($tracker);
    }
    
    /**
     * Returns true if the given tracker has a remaining effort field.
     * 
     * @param Tracker $tracker
     * @return Boolean
     */
    private function hasRemainingEffort(Tracker $tracker) {
        return $tracker->hasFormElementWithNameAndType(self::REMAINING_EFFORT_FIELD_NAME, array('int', 'float'));
    }
    
    /**
     * Returns the children of the burndown field tracker.
     * 
     * @return array of Tracker
     */
    protected function getChildTrackers() {
        return $this->getHierarchyFactory()->getChildren($this->getTrackerId());
    }
    
    /**
     * @return the label of the field (mainly used in admin part)
     */
    public static function getFactoryLabel() {
        return $GLOBALS['Language']->getText('plugin_tracker_formelement_admin', 'burndown_label');
    }
    
    /**
     * @return the description of the field (mainly used in admin part)
     */
    public static function getFactoryDescription() {
        return $GLOBALS['Language']->getText('plugin_tracker_formelement_admin', 'burndown_description');
    }
    
    /**
     * @return the path to the icon
     */
    public static function getFactoryIconUseIt() {
        return $GLOBALS['HTML']->getImagePath('ic/burndown.png');
    }
    
    /**
     * @return the path to the icon
     */
    public static function getFactoryIconCreate() {
        return $GLOBALS['HTML']->getImagePath('ic/burndown--plus.png');
    }
    
    /**
     * Fetch the html code to display the field value in tooltip
     * 
     * @param Tracker_Artifact $artifact
     * @param Tracker_Artifact_ChangesetValue_Integer $value The changeset value of this field
     * @return string The html code to display the field value in tooltip
     */
    protected function fetchTooltipValue(Tracker_Artifact $artifact, Tracker_Artifact_ChangesetValue $value = null) {
        $html = '';
        return $html;
    }

    /**
     * Verifies the consistency of the imported Tracker
     * 
     * @return true if Tracler is ok 
     */
    public function testImport() {
        return true;
    }

    
    /**
     * Validate a value
     *
     * @param Tracker_Artifact $artifact The artifact 
     * @param mixed            $value    data coming from the request. 
     *
     * @return bool true if the value is considered ok
     */
    protected function validate(Tracker_Artifact $artifact, $value) {
        //No need to validate artifact id (read only for all)
        return true;
    }
    
    /**
     * Fetch the element for the submit new artifact form
     *
     * @return string html
     */
     public function fetchSubmit() {
         return '';
     }

     /**
     * Fetch the element for the submit masschange form
     *
     * @return string html
     */
     public function fetchSubmitMasschange() {
     }
}
?>
