<?php

namespace DF\MakerBundle\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Common\Inflector\Inflector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Validator\Validation;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Bundle\MakerBundle\Util\ClassSourceManipulator;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Doctrine\EntityDetails;
use PhpParser\Builder\Method;
use PhpParser\Node\Param;
use PhpParser\Node\Expr\Variable;
use PhpParser\BuilderFactory;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use DF\MakerBundle\ScrudBag;
use DF\MakerBundle\FormConfiguration;
use DF\MakerBundle\TranslationTree;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * @author Martin GILBERT <martin.gilbert@dev-fusion.com>
 */
final class FormExec extends AbstractMaker
{
    
    /**
     *
     * @var ContainerInterface $container
     */
    private $container;

    /**
     *
     * @var FileManager
     */
    private $fileManager;
    
    /**
     *
     * @var DoctrineHelper
     */
    private $doctrineHelper;
    
    /**
     *
     * @var ScrudBag
     */
    private $bag;
    
    public function __construct(ContainerInterface $container, FileManager $fileManager, DoctrineHelper $doctrineHelper)
    {
        $this->container = $container;
        $this->fileManager = $fileManager;
        $this->doctrineHelper = $doctrineHelper;
    }
    
    public static function getCommandName(): string
    {
        return 'df:form:exec';
    }
    
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Generate Form')
            ->addArgument(
                'config-file',
                InputArgument::REQUIRED,
                sprintf('The yaml file configuration name to create Form.')
            );

        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }
    
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $configFilePath = $this->getAppRootDir().'\\config\\dev_fusion\\form\\'.$input->getArgument('config-file');
        $processor = new Processor();
        $config = Yaml::parseFile($configFilePath);
        $config = $processor->processConfiguration(new FormConfiguration($this->doctrineHelper, $generator), [ 'form_config' => $config ]);
        /*$configFilePath = $this->getAppRootDir().'\\config\\scrud\\test.yaml';
        $str = Yaml::dump($config, 5);
        file_put_contents($configFilePath, $str);
        die();
        */
        foreach ($config['forms'] as $key => $values) {
            $this->generateElement($key, $values, $input, $io, $generator);
        }
        $io->text(exec(sprintf("php %s/bin/console cache:clear", $this->getAppRootDir())));
    }

    private function generateElement(string $name, array $config, InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $entityClassExplode = explode('\\', $config['entity_class']);
        $entityClassName = array_pop($entityClassExplode);
        
        $entityClassDetails = $generator->createClassNameDetails(
            $entityClassName,
            'Entity\\'
        );
        
        $entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());
        
        $repositoryClassDetails = null;
        if (null !== $entityDoctrineDetails->getRepositoryClass()) {
            $repositoryClassDetails = $generator->createClassNameDetails(
                '\\'.$entityDoctrineDetails->getRepositoryClass(),
                'Repository\\',
                'Repository'
            );
        } else {
            throw new LogicException(sprintf("The %s entity class is not linked with a repository class.", $entityClassDetails->getFullName()));
        }
        $config['name'] = $name;
        $this->bag = new ScrudBag($entityClassDetails, $this->doctrineHelper, $repositoryClassDetails, $config);
        $prefix = $config['prefix_directory'];
        if (null !== $entityDoctrineDetails->getRepositoryClass()) {
            $reflectionClass = new \ReflectionClass($entityDoctrineDetails->getRepositoryClass());
            $this->bag->set('repository_path', $reflectionClass->getFileName());
        }

        $directoryName = '\\';
        if ($prefix) {
            $directoryName .= ucfirst($prefix).'\\';
        }
        
        $formClassDetails = $generator->createClassNameDetails(
            $this->bag->get('name_upper_camel_case'),
            'Form'.$directoryName,
            'Type'
        );
        if (class_exists($formClassDetails->getFullName())) {
            throw new LogicException(sprintf("The %s class already exists.", $formClassDetails->getFullName()));
        }
        
        $this->bag->setElement('form', $formClassDetails);
        $generator->generateClass(
            $formClassDetails->getFullName(),
            $this->getOverheadPath('form/Type.tpl.php'),
            $this->bag->all()
        );
        
        $this->generateTranslationFiles($io);
        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text(
            sprintf(
                'New form <fg=yellow>%s/</> generated',
                $formClassDetails->getFullName()
            )
        );
    }
    
    private function getOverheadPath(string $relativePath)
    {
        $skeleton = $this->bag->get('config')['skeleton'];
        $path = $this->getAppRootDir().'/templates/bundles/DFMakerBundle/'.$skeleton.'/form/'.$relativePath;
        if (file_exists($path)) {
            return $path;
        }
        $path = __DIR__.'/../Resources/skeleton/'.$skeleton.'/form/'.$relativePath;
        if (!file_exists($path)) {
            throw new \Exception(sprintf("The file %s was not found. Are you sure the skeleton is correctly configured ?", $path));
        }
        return $path;
    }
    
    private function generateTranslationFiles(ConsoleStyle $io)
    {
        $config = $this->bag->get('config');
        $bag = $this->bag;
        
        $directoryName = $this->getAppRootDir().'/translations/';
        $filesystem = new Filesystem();
        if (!$filesystem->exists($directoryName)) {
            $filesystem->mkdir($directoryName);
        }
        
        $pathFileTranslation = $directoryName.$this->bag->get('file_translation_name').'.en.yaml';
        if (!$filesystem->exists($pathFileTranslation)) {
            $filesystem->touch($pathFileTranslation);
        }
        try {
            $fileTranslation = Yaml::parseFile($pathFileTranslation);
        } catch (ParseException $exception) {
            printf('Unable to parse the YAML string: %s', $exception->getMessage());
        }
        if (!$fileTranslation) {
            $fileTranslation=[];
        }
        $tree = new TranslationTree($fileTranslation);
        include($this->getOverheadPath('translation/translate.php'));
        $yamlEnglish = Yaml::dump($tree->ksort()->all(), 4);
        
        file_put_contents($pathFileTranslation, $yamlEnglish);
        
        $locale = $this->container->getParameter("kernel.default_locale");
        $pathFileTranslation = $directoryName.$this->bag->get('file_translation_name').'.'.$locale.'.yaml';
        if (!$filesystem->exists($pathFileTranslation)) {
            $filesystem->touch($pathFileTranslation);
        }
        try {
            $fileTranslation = Yaml::parseFile($pathFileTranslation);
        } catch (ParseException $exception) {
            printf('Unable to parse the YAML string: %s', $exception->getMessage());
        }
        if (!$fileTranslation) {
            $fileTranslation=[];
        }
        $translationThreeLocale = new TranslationTree($fileTranslation);
        foreach ($tree->keys() as $key) {
            $value = $this->transElement($tree->get($key), $locale);
            $translationThreeLocale->set($key, $value);
        }
        
        $yamlLocale = Yaml::dump($translationThreeLocale->ksort()->all(), 4);
        file_put_contents($pathFileTranslation, $yamlLocale);
    }
    
    /**
     *
     * @param string $element
     * @param fr $local
     * @return string
     */
    private function transElement($element, $local)
    {
        $translator = $this->container->get('translator');
        $transcript = $translator->trans($element, [], 'scrud', $local);
        if ($transcript !== $element) {
            return $transcript;
        }
        $words = explode(' ', $element);
        if (count($words) < 2) {
            return $element;
        }
        $transcript = '';
        foreach ($words as $word) {
            $transcript .= $translator->trans($word, [], 'scrud', $local);
            if (next($words)) {
                $transcript .= ' ';
            }
        }
        return $transcript;
    }
    
    /**
     * This method returns the root directory of project.
     *
     * @return string
     */
    private function getAppRootDir()
    {
        return $this->container->get('app.parameter_bag')->get('kernel.project_dir');
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Route::class,
            'router'
        );

        $dependencies->addClassDependency(
            AbstractType::class,
            'form'
        );

        $dependencies->addClassDependency(
            Validation::class,
            'validator'
        );

        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm-pack'
        );

        $dependencies->addClassDependency(
            CsrfTokenManager::class,
            'security-csrf'
        );

        $dependencies->addClassDependency(
            ParamConverter::class,
            'annotations'
        );
    }
}
