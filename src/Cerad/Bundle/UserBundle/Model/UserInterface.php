<?php
namespace Cerad\Bundle\UserBundle\Model;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

interface UserInterface extends AdvancedUserInterface
{
    // Basic account
  //public function getId();
  //public function setId($value); // For imports and merging
    
    public function getEmail();
    public function setEmail($value);

    public function getUsername();
    public function setUsername($value);
    
    public function getPassword();
    public function setPassword($value);
    
    public function getPlainPassword();
    public function setPlainPassword($value);
        
    // Account Stuff
    public function isEnabled();
    public function setAccountEnabled($bool);
    
    public function getAccountName();
    public function setAccountName($value);
    
    // Person Stuff
  //public function getPersonFedId();
  //public function setPersonFedId($value);

    public function getPersonGuid();
    public function setPersonGuid($value);
    
    public function getPersonStatus();
    public function setPersonStatus($value);
    
    public function getPersonVerified();
    public function setPersonVerified($value);
    
}

?>
