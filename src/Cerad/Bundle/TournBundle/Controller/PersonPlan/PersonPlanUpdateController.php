<?php
namespace Cerad\Bundle\TournBundle\Controller\PersonPlan;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

use Cerad\Bundle\TournBundle\FormType\DynamicFormType;

/* ========================================================
 * Person Plan Editor
 * Passing the person for now because might end up with people with no plans
 */
class PersonPlanUpdateController extends MyBaseController
{   
    public function updateAction(Request $request, $id = null)
    {
        // Security
        if (!$this->hasRoleUser()) return $this->redirect('cerad_tourn_welcome');

        // Document
        $personId = $id;
        $project = $this->getProject();
        
        // Simple model
        $model1 = $this->createModel($project,$personId);
                      
        // This could also be passed in
        $form = $this->createModelForm($model1);
        $form->handleRequest($request);

        if ($form->isValid()) 
        {             
            // Maybe dispatch something to adjust form
            $model2 = $form->getData();
            
            $model3 = $this->processModel($model2);
            
            $this->sendEmail($model3);
            
            return $this->redirect('cerad_tourn_home');
        }

        // Template stuff
        $tplData = array();
        $tplData['msg'    ] = null; // $msg; from flash bag
        $tplData['form'   ] = $form->createView();
        
        $tplData['plan'   ] = $model1['plan'];
        $tplData['person' ] = $model1['person'];
        $tplData['project'] = $model1['project'];

        return $this->render('@CeradTourn\PersonPlan\Update\PersonPlanUpdateIndex.html.twig',$tplData);        
    }
     
    protected function createModel($project,$personId)
    {   
        $personRepo = $this->get('cerad_person.person_repository');
        $person = null;
        
        // If passed a plan then use it
        if ($personId) $person = $personRepo->find($personId);
        else
        {
            $person = $this->getUserPerson(false);
        }
        if (!$person) throw new \Exception('Person not found in plan update');
        
        $plan = $person->getPlan($project->getId());
        $plan->mergeBasicProps($project->getBasic());
        
        // Pack it up
        $model = array();
        $model['plan'  ]  = $plan;
        $model['basic' ]  = $plan->getBasic();
        $model['notes' ]  = $plan->getNotes();
        $model['person']  = $person;
        $model['project'] = $project;
        
        return $model;
    }
    protected function createModelForm($model)
    {   
        $project = $model['project'];
        
        $basicType = new DynamicFormType('basic',$project->getBasic());
        
        $formOptions = array(
            'validation_groups'  => array('basic'),
            'cascade_validation' => true,
        );
                
        $builder = $this->createFormBuilder($model,$formOptions);
        
        $builder->add('basic',$basicType, array('label' => false));
        
/* ==============================
 * Does not quit work
        $builder->add('notes','textarea', array(
            'label' => false,
            'required' => false,
            'attr' => array('cols' => 50, 'rows' => 5)
        ));
        */
        return $builder->getForm();
    }
    /* ===============================================
     * Lot's of possible processing to do
     * All ends with a plan
     */
    protected function processModel($model)
    {
        $personRepo = $this->get('cerad_person.person_repository');
         
        // Unpack dto
        $plan   = $model['plan'];
        $basic  = $model['basic'];
        $person = $model['person'];
        
        $basic['notes'] = strip_tags($basic['notes']);
       
        if (!$plan->getPersonName()) $plan->setPersonName($person->getName()->full);
        
        $plan->setBasic($basic);
        $plan->setUpdatedOn();
                
        // And save
        $personRepo->save($person);
        $personRepo->commit();
       
        return $model;
    }
    /* ============================================
     * Should probably be moved to a listener
     */
    protected function sendEmail($model)
    {   
        $project = $model['project'];
        $person  = $model['person'];
        $plan    = $model['plan'];
        
        $personFed = $person->getFed($project->getFedRoleId());
        
        $prefix = $project->getPrefix(); // OpenCup2013
        
        $assignor = $project->getAssignor();
        
        $assignorName  = $assignor['name'];
        $assignorEmail = $assignor['email'];
        
        $adminName =  'Art Hundiak';
        $adminEmail = 'ahundiak@gmail.com';
        
        $personName = $person->getName();
        
        $refereeName  = $personName->full;
        $refereeEmail = $person->getEmail();
        
        /* =================================================
         * Use templates for email subject and body
         */
        $tplData = array();
        $tplData['plan']        = $plan;
        $tplData['person']      = $person;
        
        $tplData['fed']         = $personFed;
      //$tplData['org']         = $personFed->getOrgKey();
        $tplData['certReferee'] = $personFed->getCertReferee();
        
        $tplData['project']  = $project;
        $tplData['assignor'] = $assignor;
        
        $subject = $this->renderView('@CeradTourn\PersonPlan\Update\PersonPlanUpdateEmailSubject.html.twig',$tplData);       
        $body    = $this->renderView('@CeradTourn\PersonPlan\Update\PersonPlanUpdateEmailBody.html.twig',   $tplData);
       
        // die(nl2br($body));
        
        // This goes to the assignor
        $message1 = \Swift_Message::newInstance();
        $message1->setSubject($subject);
        $message1->setBody($body);
        $message1->setFrom(array('admin@zayso.org' => $prefix));
        $message1->setBcc (array($adminEmail => $adminName));
        
        $message1->setTo     (array($assignorEmail => $assignorName));
        $message1->setReplyTo(array($refereeEmail  => $refereeName));

        $this->get('mailer')->send($message1);
        
        // This goes to the referee
        $message2 = \Swift_Message::newInstance();
        $message2->setSubject($subject);
        $message2->setBody($body);
        $message2->setFrom(array('admin@zayso.org' => $prefix));
      
        $message2->setTo     (array($refereeEmail  => $refereeName));
        $message2->setReplyTo(array($assignorEmail => $assignorName));

        $this->get('mailer')->send($message2);
        
        return $model;
    }
}
?>
