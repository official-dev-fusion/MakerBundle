<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class <?= $class_name ?> extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('<?= $entity_snake_case_plural ?>', CollectionType::class, [
                'label' => false,
                'entry_type' => <?= $form_update_upper_camel_case ?>::class,
                'entry_options' => [
                    'label' => false,
                ],
                'data' => $options['<?= $entity_snake_case_plural ?>'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            '<?= $entity_snake_case_plural ?>' => null,
            'translation_domain' => '<?= $file_translation_name ?>',
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view['<?= $entity_snake_case_plural ?>']->children as $i => $childView) {
            $childView->vars['label'] = $options['<?= $entity_snake_case_plural ?>'][$i]->__toString();
        }
    }
}
