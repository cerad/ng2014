<?php

namespace Cerad\Bundle\PersonBundle\Model;

use Cerad\Bundle\PersonBundle\Model\PersonName;
use Cerad\Bundle\PersonBundle\Model\PersonAddress;

use Cerad\Bundle\PersonBundle\Model\PersonFed;
use Cerad\Bundle\PersonBundle\Model\PersonPlan;
use Cerad\Bundle\PersonBundle\Model\PersonPerson;

class Person extends BaseModel implements PersonInterface
{
    const GenderMale    = 'M';
    const GenderFemale  = 'F';
    const GenderUnknown = 'U';

  //protected $id;
    protected $guid;   // For linking across contexts
    
    protected $name;   // VO PersonName
    protected $icon;
    
    protected $dob;    // DateTime
    protected $gender;
    
    protected $email;

    protected $phone;
    protected $phoneProvider; // For texting?

    protected $address; // VO PersonAddress
    
    protected $notes;
        
    protected $verified  = 'No';
    protected $status    = 'Active';
        
    // Setting to array messes up qp left join
    protected $feds;    // = array();
    protected $plans;   // = array();
    protected $teams;
    protected $persons; // = array();
    
    public function __construct()
    {
        $this->guid = $this->genGuid();
        
        $this->setName   ($this->createName());
        $this->setAddress($this->createAddress());
        
        $this->feds    = array();
        $this->plans   = array();
        $this->teams   = array();
        $this->persons = array();
    }
    /* ======================================================================
     * Standard getters/setters/creators
     */
  //public function getId       () { return $this->id;     }
    public function getDob      () { return $this->dob;    }
    public function getKey      () { return $this->guid;   }
    public function getGuid     () { return $this->guid;   }
    public function getIcon     () { return $this->icon;   }
    public function getNotes    () { return $this->notes;  }
    public function getEmail    () { return $this->email;  }
    public function getPhone    () { return $this->phone;  }
    public function getGender   () { return $this->gender; }

    public function getStatus   () { return $this->status;    }
    public function getVerified () { return $this->verified;  }

    // Value Objects
    public function getName     () { return clone $this->name;    }
    public function getAddress  () { return clone $this->address; }
    
    public function setName     (PersonName    $value) { $this->onPropertySet('name',   clone $value); }
    public function setAddress  (PersonAddress $value) { $this->onPropertySet('address',clone $value); }
       
    // Not sure
    public function createPerson ($params = null) { return new Person       ($params); }
    public function createName   ($params = null) { return new PersonName   ($params); }
    public function createAddress($params = null) { return new PersonAddress($params); }
    
    // Setters
  //public function setId       ($value) { $this->onPropertySet('id',       $value); }
    public function setDob      ($value) { $this->onPropertySet('dob',      $value); }
    public function setGuid     ($value) { $this->onPropertySet('guid',     $value); }
    public function setIcon     ($value) { $this->onPropertySet('icon',     $value); }
    public function setNotes    ($value) { $this->onPropertySet('notes',    $value); }
    public function setEmail    ($value) { $this->onPropertySet('email',    $value); }
    public function setPhone    ($value) { $this->onPropertySet('phone',    $value); }
    public function setGender   ($value) { $this->onPropertySet('gender',   $value); }
    public function setStatus   ($value) { $this->onPropertySet('status',   $value); }
    public function setVerified ($value) { $this->onPropertySet('verified', $value); }

    static function getGenderTypes()
    {
        return array(
            self::GenderMale    => 'Male',
            self::GenderFemale  => 'Female',
            self::GenderUnknown => 'Unknown',
        );
    }
    /* =============================================================
     * The feds stuff
     */
    public function createFed($params = null) { return new PersonFed($params); }
    
    public function getFeds($asArray = false) 
    { 
        return $asArray ? $this->feds->toArray() : $this->feds; 
    }
    
    public function removeFed(PersonFed $fed)
    {
        $fedRole = $fed->getFedRole();
        
        if (!isset($this->feds[$fedRole])) return;
        
        unset($this->feds[$fedRole]);
        
        $this->onPropertyChanged('feds');
    }
    public function addFed(PersonFed $fed)
    {
        $fedRole = $fed->getFedRole();
        
        if (isset($this->feds[$fedRole])) return;
        
        $this->feds[$fedRole] = $fed;
        
        $fed->setPerson($this);
        
        $this->onPropertyChanged('feds');
    }
    public function getFed($fedRole, $autoCreate = true, $autoAdd = true)
    {
        if (isset($this->feds[$fedRole])) return $this->feds[$fedRole];
 
        if (!$autoCreate) return null;
        
        $fed = $this->createFed();
        $fed->setFedRole($fedRole);
        
        if (!$autoAdd) return $fed;
        
        $this->addFed($fed);
        
        return $fed;
    }
    public function getProjectFed()
    {
        if (count($this->feds) != 1)
        {
            throw new \Exception('Missing project fed');
        }
        // Need a better way to get the first item
        foreach($this->feds as $fed)
        {
            return $fed;
        }
    }
    
    /* =============================================================
     * The plans
     */
    public function createPlan($params = null) { return new PersonPlan($params); }
    
    public function getPlans() { return $this->plans; }
    
    public function addPlan(PersonPlan $plan)
    {
        $projectId = $plan->getProjectId();
        
        if (isset($this->plans[$projectId])) return;
        
        $this->plans[$projectId] = $plan;
        
        $plan->setPerson($this);
        
        $this->onPropertyChanged('plans');
    }
    public function getProjectPlan()
    {
        if (count($this->plans) != 1)
        {
            throw new \Exception('Missing project plan');
        }
        // Need a better way to get the first item
        foreach($this->plans as $plan)
        {
            return $plan;
        }
    }
    public function getPlan($projectKey = null, $autoCreate = true, $autoAdd = true)
    {
        if (!$projectKey)
        {
            // Should be a better way but reset does not like assoc arrays
            // And array_keys does not like objects
            // Seems to work okay even though returning in the middle of a foreach loop
            // Use to array if need be
            foreach($this->plans as $plan)
            {
                return $plan;
            }
        }
        if (isset($this->plans[$projectKey])) return $this->plans[$projectKey];
        
        if (!$autoCreate) return null;
        
        $plan = $this->createPlan();
        $plan->setProjectId($projectKey);
        $plan->setPersonName($this->getName()->full);
        if (!$autoAdd) return $plan;
        
        $this->addPlan($plan);
        
        return $plan;
    }
    public function getPlanByProject($project)
    {
        $projectKey = $project->getKey();
        
        if (!isset($this->plans[$projectKey])) return null;
            
        return $this->plans[$projectKey];
    }
    /* ========================================================
     * PersonToPerson relation
     *
     * WIP
     * Name is confusing still
     * Have multiple Family members
     * 
     * Might also have project property later
     */
    public function createPersonPerson($params = null) { return new PersonPerson($params); }
    
    // 27 Mar 2014 - Create fake primary if necessary
    public function getPersonPersons() 
    { 
        return $this->persons;
        
        $persons = $this->persons->toArray();
        if (isset($persons[PersonPerson::RolePrimary])) return $persons;
        
        $personPerson = $this->createPersonPerson();
        $personPerson->setRole(PersonPerson::RolePrimary);
        $personPerson->setChild ($this);
        $personPerson->setParent($this);
        
        $persons[] = $personPerson;
        
        return  $persons; 
    }

    public function addPersonPerson(PersonPerson $personPerson)
    {
        $role    = $personPerson->getRole();
        $childId = $personPerson->getChild()->getId();
        
        foreach($this->persons as $personPersonx)
        {
            if (($role    == $personPersonx->getRole()) &&
                ($childId == $personPersonx->getChild()->getId()))
            {
                return null;
            }
        }
        
        // Loop and check role and pp.child
        $this->persons[] = $personPerson;
        $personPerson->setParent($this);
        $this->onPropertyChanged('persons');
    }
    public function getPersonPersonPrimary($autoCreate = true)
    {
        $role = PersonPerson::RolePrimary;
        foreach($this->persons as $personPerson)
        {
            if ($role == $personPerson->getRole())
            {
                // Should only be one primary
                return $personPerson;
            }
        }

        if (!$autoCreate) return null;
            
        $personPerson = $this->createPersonPerson();
        $personPerson->setParent($this);
        $personPerson->setChild ($this);
        $personPerson->setRole  (PersonPerson::RolePrimary);
            
        $this->addPersonPerson($personPerson);
            
        return $personPerson;
    }
    public function removePersonPerson($personPerson)
    {
        // TODO: Use a look or index by person id

        $this->persons->removeElement($personPerson);
        
        $personPerson->setParent(null);
        
        $this->onPropertyChanged('persons');
    }
    /* ========================================================
     * 08 June 2014
     * PersonTeam Relation
     */
    public function createPersonTeam($params = null) { return new PersonTeam($params); }
    
    public function getPersonTeams() 
    { 
        return $this->teams;
    }
    protected function isSameTeam($team1,$team2)
    {
       if ($team1->getTeamKey() != $team2->getTeamKey()) return false;
             
       return true;
    }
    public function addPersonTeam($personTeam)
    {       
        foreach($this->teams as $personTeamx)
        {
            if ($this->isSameTeam($personTeam,$personTeamx)) return;
        }
        // Add it
        $this->teams[$personTeam->getTeamKey()] = $personTeam;
        $personTeam->setPerson($this);
        $this->onPropertyChanged('teams');
    }
    public function hasPersonTeam($teamKey)
    {
        return isset($this->teams[$teamKey]) ? true : false;
    }
    public function removePersonTeam($personTeam)
    {
        
        $teamKey = $personTeam->getTeamKey();
        
        if (!isset($this->teams[$teamKey])) return;
        
        unset($this->teams[$teamKey]);
        
      //$this->teams->remove($teamKey);
        
        // This is critucal for bidirectional
        $personTeam->setPerson(null);
        
        $this->onPropertyChanged('teams');
    }
    
    
    /* ===========================================
     * Age with optional asOf date
     */
    public function getAge($asOf = null)
    {
        if (!$this->dob) return null;
        
        if (!$asOf) $asOf = new \DateTime();
            
        $interval = $asOf->diff($this->dob);
        
        $years = $interval->format('%y');
        
        return $years;
    }
    /* ==========================================
     * External link
     */
    protected $user;
    public function getUser()      { return  $this->user; }
    public function setUser($user) { $this->user = $user; }
}
?>
