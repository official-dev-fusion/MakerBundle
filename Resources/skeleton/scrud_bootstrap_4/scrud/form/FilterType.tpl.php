<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $manager_full_class_name ?>;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class <?= $class_name ?> extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'label.filter_search',
                ],
                'required' => false,
            ])<?php if (!$config['search']['pagination']): ?>;<?php endif ?>
<?php if ($config['search']['pagination']): ?>

            ->add('number_by_page', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'label.filter_number_by_page',
                ],
                'empty_data' => <?= $manager_upper_camel_case ?>::NUMBER_BY_PAGE,
            ]);
<?php endif ?>
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'GET',
            'translation_domain' => '<?= $file_translation_name ?>',
        ]);
    }
    
    public function getBlockPrefix()
    {
        return 'filter';
    }
}
