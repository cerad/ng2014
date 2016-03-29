<?php
namespace Cerad\Bundle\TournBundle\FormType\Schedule\Official;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;

class AssignSlotSubscriber implements EventSubscriberInterface
{
    private $factory;
    private $formType;
    
    public function __construct(FormFactoryInterface $factory, $formType)
    {
        $this->factory  = $factory;
        $this->formType = $formType;
    }
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    public function preSetData(FormEvent $event)
    {
        $gameOfficial = $event->getData();
        $form         = $event->getForm();

        if (!$gameOfficial) return;
        
        $form->add($this->factory->createNamed('personNameFull','text', null, array(
            'label'           => 'Name',
            'required'        => false,
            'auto_initialize' => false,
        )));
        
        $form->add($this->factory->createNamed('personGuid','choice', null, array(
            'required'        => false,
            'empty_value'     => 'Select Official',
            'empty_data'      => null,
            'auto_initialize' => false,
            'choices'         => $this->formType->gameOfficials,
        )));
        
        $assignSlotWorkflow = $this->formType->assignSlotWorkflow;
        $states = $assignSlotWorkflow->getStateOptionsForAssignorWorkflow($gameOfficial->getAssignState());
        
        $form->add($this->factory->createNamed('state','choice', null, array(
            'required'        => false,
            'empty_value'     => 'Assignment Status',
            'empty_data'      => null,
            'auto_initialize' => false,
            'choices'         => $states,
        )));
         
        return;
        
        // guid
        $personId = $gamePerson->getPerson();
        
        $statusPickList = array
        (
            'RequestAssignment'   => 'Request Assignment',
            'RequestRemoval'      => 'Request Removal',
            'AssignmentRequested' => 'Assignment Requested',
            'AssignmentApproved'  => 'Assignment Approved',
        );
        $officialsPickList = array();
        
        if ($personId) $emptyValue = null;
        else 
        {
            $emptyValue = 'Select Your Name';
            $statusPickList = array('RequestAssignment' => 'Request Assignment');
        }
        $matched = false;
        foreach($this->officials as $official)
        {
            $officialsPickList[$official->getId()] = $official->getName();
            if ($official->getId() == $personId) $matched = true;
        }
        if ($personId && !$matched)
        {
            // Someone not in officials is currently assigned
            $officialsPickList = array($personId => $gamePerson->getName());
            $emptyValue = false;
            $status = $gamePerson->getStatus();
            
            // Because of error in batch update
            if (!$status) $status = 'AssignmentRequested';
            
            if (isset($statusPickList[$status])) $statusDesc = $statusPickList[$status];
            else                                 $statusDesc = $status;
            
            $statusPickList = array($status => $statusDesc);
        }
        if ($personId && $matched)
        {
          //$officialsPickList = array($personId => $gamePerson->getName());
            $emptyValue = false;
            
            $statusPickList = array
            (
                'RequestRemoval'      => 'Request Removal',
                'AssignmentRequested' => 'Assignment Requested',
                'AssignmentApproved'  => 'Assignment Approved',
            );
        }
        $form->add($this->factory->createNamed('personx','choice', null, array(
            'label'         => 'Person',
            'required'      => false,
            'empty_value'   => $emptyValue,
            'empty_data'    => false,
            'auto_initialize' => false,
            'choices'       => $officialsPickList,
        )));
        
        // Mess with state
        $status = $gamePerson->getStatus();
        if (!$status) $status = 'RequestAssignment';
        $form->add($this->factory->createNamed('statusx','choice', null, array(
            'label'         => 'Status',
            'required'      => false,
            'empty_value'   => false,
            'empty_data'    => false,
            'choices'       => $statusPickList,
            'auto_initialize' => false,
        )));
        
        // Done
        return;
    }
}
?>
