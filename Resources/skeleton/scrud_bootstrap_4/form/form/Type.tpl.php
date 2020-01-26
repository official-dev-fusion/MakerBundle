<?= "<?php\n" ?>

namespace <?= $namespace ?>;
<?php 
    include_once (__DIR__.'/../../scrud/functions.php');
    $forms = $config['fields'];
    $type_full_class_names = get_type_full_class_names ($forms);
?>

use <?= $entity_full_class_name ?>;
<?php foreach ($type_full_class_names as $type_full_class_name): ?>
use <?= $type_full_class_name ?>;
<?php endforeach; ?>
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class <?= $class_name ?> extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
<?php foreach ($forms as $form): ?>
            ->add('<?= $form['property'].'\', '.$form['type'] ?><?= $form['type'] ? '::class' : 'null' ?>, [
                'label' => '<?= $name_snake_case.'.label.'.$form['label_key_trans'] ?>',
<?php if ($form['type_options']): ?>
<?php foreach ($form['type_options'] as $key => $value): ?>
<?php if (is_bool($value)): ?>
<?php if ($value): ?>
                '<?= $key ?>' => true,
<?php else: ?>
                '<?= $key ?>' => false,
<?php endif; ?>
<?php elseif (is_scalar($value)): ?>
                '<?= $key ?>' => '<?= $value ?>',
<?php elseif (is_array($value)): ?>
<?php
    $code = var_export($value, true);
    $code = str_replace("\n", "\n                  ", $code);
    $code = preg_replace ('/^array \(/', '[', $code);
    $code = preg_replace ('/  \)$/', ']', $code);
?>
                '<?= $key ?>' => <?= $code ?>,
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
            ])<?php if (!next($forms)): ?>;<?php endif; ?>

<?php endforeach; ?>
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => <?= $entity_upper_camel_case ?>::class,
            'translation_domain' => '<?= $file_translation_name ?>',
        ]);
    }
}
