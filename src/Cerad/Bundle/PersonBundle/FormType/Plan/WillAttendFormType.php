<?php
namespace Cerad\Bundle\PersonBundle\FormType\Plan;

use Symfony\Component\Form\AbstractType;
//  Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/* ==================================================================
 * Used by the admin person form.
 * TODO: Needs to accept a project as input or elase be replaced with a dynamic form
 */
class WillAttendFormType extends AbstractType
{   
    public function getParent() { return 'choice'; }
    public function getName()   { return 'cerad_person_plan_will_attend'; }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
          
            'label'    => 'Will Attend',
            'choices'  => $this->choices,
            'multiple' => false,
            'expanded' => false,
            
            'empty_value' => 'Will Attend?',
            'empty_data'  => null
            
        ));
    }    
    // Should inject project and pull from it
    protected $choices = array
    (
        'no'    => 'No',
        'yes'   => 'Yes',
        'yesx'  => 'Yesx',
        'maybe' => 'Maybe',
        
      // Need to feed a project to here
      //'we12' => 'Both weekends',
      //'we1'  => 'First Weekend',
      //'we2'  => 'Second Weekend',
                
      //'SeeNotes' => 'See Notes',
   );    
}

?>
