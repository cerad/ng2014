<?php
namespace Cerad\Bundle\TournAdminBundle\FormType\PersonPlan\Update\AYSO;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/* ===================================================================
 * Try having some standard admin forms
 * Might end up flattening everything and just moving to controller
 */
class SafeHavenCertFormType extends AbstractType
{
    public function getName() { return 'cerad_tourn_admin_aysov_safe_haven_cert'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\PersonBundle\Model\PersonFedCert',
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('badge','cerad_person_aysov_safe_haven_badge');
    }
}
?>
