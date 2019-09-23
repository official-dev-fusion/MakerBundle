<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use <?= $manager_full_class_name ?>;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;

class <?= $class_name ?> extends AbstractType
{
    
    /**
     * 
     * @var <?= $manager_upper_camel_case ?>
     */
    private $<?= $manager_lower_camel_case ?>;
    
    /**
     *
     * @param <?= $manager_upper_camel_case ?> $<?= $manager_lower_camel_case ?> 
     */
    public function __construct(<?= $manager_upper_camel_case ?> $<?= $manager_lower_camel_case ?>)
    {
        $this-><?= $manager_lower_camel_case ?> = $<?= $manager_lower_camel_case ?>;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('<?= $entity_snake_case_plural ?>', EntityType::class, [
                'label' => false,
                'choice_label' => false,
                'class' => <?= $entity_upper_camel_case ?>::class,
                'choices' => $options['<?= $entity_snake_case_plural ?>'],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('action', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Action',
                'choices' => [
<?php if ($config['update']['multi_select']): ?>
                    'action.update' => 'update',
<?php endif ?>
<?php if ($config['delete']['multi_select']): ?>
                    'action.delete' => 'delete',
<?php endif ?>
                ],
                'multiple' => false,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {            
                $result = $this-><?= $manager_lower_camel_case ?>->validationUpdateSearchForm($event->getForm());
                if (true !== $result) {
                    $event->getForm()->addError(new FormError($result));
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            '<?= $entity_snake_case_plural ?>' => null,
            'translation_domain' => '<?= $entity_translation_name ?>',
        ]);
    }
}
