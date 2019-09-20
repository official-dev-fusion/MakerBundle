<?= "<?php\n" ?>

namespace <?= $namespace ?>;


use <?= $entity_full_class_name ?>;
<?php foreach ($field_type_full_class_names as $field_type_full_class_name): ?>
use <?= $field_type_full_class_name ?>;
<?php endforeach; ?>
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class <?= $class_name ?> extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
<?php foreach ($entity_form_fields as $field): ?>
            ->add('<?= $field['field_lower_camel_case'].'\', '.$field['field_type_class'] ?>, [
                'label' => '<?= 'form_labels.'.$field['field_snake_case'] ?>',
<?php if ('DateType::class' === $field['field_type_class'] || 'DateTimeType::class' === $field['field_type_class']): ?>
                // ex : 'years' => range(date('Y')-100, date('Y')+100),
<?php endif; ?>
            ])<?php if (!next($entity_form_fields)): ?>;<?php endif; ?>

<?php endforeach; ?>
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => <?= $entity_upper_camel_case ?>::class,
            'translation_domain' => '<?= $entity_translation_name ?>',
        ]);
    }
}
