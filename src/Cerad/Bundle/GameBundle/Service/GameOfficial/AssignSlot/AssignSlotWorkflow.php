<?php
namespace Cerad\Bundle\GameBundle\Service\GameOfficial\AssignSlot;

use Symfony\Component\Yaml\Yaml;

/* =========================================================
 * This could probably be encoded in a yaml file
 */
class AssignSlotWorkflow
{
    const StateOpen = 'Open';
    
    // Assignor Workflow
    const StatePending   = 'Pending';   // By assignor
    const StatePublished = 'Published'; // By assignor
    const StateNotified  = 'Notified';  // By assignor or system when user views assignment
    
    const StateAccepted  = 'Accepted';  // By user
    const StateDeclined  = 'Declined';  // By user
    
    const StateTurnedBack          = 'TurnedBack';          // By user for previously accepted assignment
    const StateTurnedBackApproved  = 'TurnedBackApproved';  // By assignor - acknowledge turnback

    // Self Assign Workflow
    const StateRequested = 'Requested'; // By user for self assigning
    const StateIfNeeded  = 'IfNeeded';  // By user, will take assignment of needed
    const StateRemove    = 'Remove';    // By user to be removed
    
    const StateApproved  = 'Approved'; // By assignor
    const StateRejected  = 'Rejected'; // By assignor
    const StateReview    = 'Review';   // By assignor, thinking about it
    
    /* ================================================
     * Same states with role
     */
    const StateOpenedByAssignor    = 'Open';     // Went from pedning/published/notified back to open
    const StatePendingByAssignor   = 'Pending';
    const StatePublishedByAssignor = 'Published';
    const StateNotifiedByAssignor  = 'Notified';
    const StateApprovedByAssignor  = 'Approved';
    
    const StateRejectedByAssignor  = 'Rejected';
    const StateRemovedByAssignor   = 'Removed';
    const StateReviewByAssignor    = 'Review';
    
    const StateTurnedBackApprovedByAssignor  = 'TurnedBackApproved';
   
    const StateAcceptedByAssignee  = 'Accepted';
    const StateDeclinedByAssignee  = 'Declined';
    const StateTurnbackByAssignee  = 'Turnback';

    const StateRequestedByAssignee = 'Requested';
    const StateIfNeededByAssignee  = 'IfNeeded';
    const StateRemoveByAssignee    = 'Remove';

    /* =======================================================
     * Initialize using yaml
     * 
     * Next time get a name mismatch then do a verification
     */
    public function __construct($configFilePath)
    {
        $config = Yaml::parse(file_get_contents($configFilePath));
        
        $this->assigneeStateTransitions  = $config['assigneeStateTransitions'];
        $this->assignorStateTransitions  = $config['assignorStateTransitions'];
        $this->mapInternalToPostedStates = $config['assignStateMap'];
        
        $map = array();
        foreach($this->mapInternalToPostedStates as $key => $value)
        {
            $map[$value] = $key;
        }
        $this->mapPostedToInternalStates = $map;
    }
    public function mapInternalStateToPostedState($state)
    {
        if (isset( $this->mapInternalToPostedStates[$state])) {
            return $this->mapInternalToPostedStates[$state];
        }
        echo sprintf("Missing State: %s<br />\n",$state);
        print_r(array_keys($this->mapInternalToPostedStates));
        die();
    }
    public function mapPostedStateToInternalState($state)
    {
        if (isset( $this->mapPostedToInternalStates[$state])) {
            return $this->mapPostedToInternalStates[$state];
        }
        echo sprintf("Missing State: %s<br />\n",$state);
        print_r(array_keys($this->mapPostedToInternalStates));
        die();
    }
    /* =======================================================
     * Select options for current state
     */
    public function getStateOptionsForAssignorWorkflow($state)
    {
        return $this->getStateOptions($state,$this->assignorStateTransitions);
    }
    public function getStateOptionsForUserWorkflow($state)
    {
        return $this->getStateOptions($state,$this->assigneeStateTransitions);
    }
    protected function getStateOptions($state,$transitions)
    {
        $state = $this->mapPostedStateToInternalState($state);
        
        $items = $transitions[$state];
        $options = array();
        foreach($items as $state => $item)
        {   
            $state = $this->mapInternalStateToPostedState($state);   
            $options[$state] = $item['desc'];
        }
        return $options;
    }
    /* ============================================================
     * Do all the updating and stuff
     */
    public function processPostByAssignee($gameOfficialNew,$personPlan)
    {
        $gameOfficialOrg = $gameOfficialNew->retrieveOriginalInfo();
        
        $assignStateNew = $this->mapPostedStateToInternalState($gameOfficialNew->getAssignState());
        $assignStateOrg = $this->mapPostedStateToInternalState($gameOfficialOrg['assignState']);
        
        if ($assignStateNew == $assignStateOrg) return;
        
        $transition = $this->assigneeStateTransitions[$assignStateOrg][$assignStateNew];
        
        // Normally go directly to new state but sometimes want a different state
        $assignStateMod = isset($transition['modState']) ? $transition['modState'] : $assignStateNew;
        if ($assignStateMod != $assignStateNew)
        {
            $gameOfficialNew->setAssignState($this->mapInternalStateToPostedState($assignStateMod));
        }
        // Transfer or clear person
        switch($assignStateMod)
        {
            case 'StateOpen':
                $gameOfficialNew->setPerson(null);
                break;
            default:
                $gameOfficialNew->setPerson($personPlan);
        }
        // Should we notify the assignor
        $notifyAssignor = isset($transition['notifyAssignor']) ? true : false;
        
        if (!$notifyAssignor) return;
        
        // Need to setup message to the notify assignor listener
        
    }
}
?>
