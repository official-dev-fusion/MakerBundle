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
use Twig\Environment;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use DF\MakerBundle\ScrudBag;
use DF\MakerBundle\ScrudConfiguration;
use DF\MakerBundle\TranslationTree;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * @author Martin GILBERT <martin.gilbert@dev-fusion.com>
 */
final class ScrudExec extends AbstractMaker
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
     * @var Environment
     */
    private $templating;
    
    /**
     *
     * @var ScrudBag
     */
    private $bag;
    
    public function __construct(ContainerInterface $container, FileManager $fileManager, DoctrineHelper $doctrineHelper, Environment $templating)
    {
        $this->container = $container;
        $this->fileManager = $fileManager;
        $this->doctrineHelper = $doctrineHelper;
        $this->templating = $templating;
    }
    
    public static function getCommandName(): string
    {
        return 'df:scrud:exec';
    }
    
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Creates advanced SCRUD for Doctrine entity class with personalized search, Bootstrap 4, JQuery and more.')
            ->addArgument(
                'config-file',
                InputArgument::REQUIRED,
                sprintf('The yaml file name to create SCRUD.')
            );

        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }
    
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $configFilePath = $this->getAppRootDir().'\\config\\dev_fusion\\scrud\\'.$input->getArgument('config-file');
        $processor = new Processor();
        $config = Yaml::parseFile($configFilePath);
        $config = $processor->processConfiguration(new ScrudConfiguration($this->doctrineHelper, $generator), [ 'scrud_config' => $config ]);
        
        foreach ($config['entities'] as $key => $values) {
            $this->generateElement($key, $values, $input, $io, $generator);
        }
        $io->text(exec(sprintf("php %s/bin/console cache:clear", $this->getAppRootDir())));
    }

    private function generateElement(string $name, array $config, InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $entityClassExplode = explode('\\', $config['class']);
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
        
        $controllerClassDetails = $generator->createClassNameDetails(
            $this->bag->get('name_upper_camel_case'),
            'Controller'.$directoryName,
            'Controller'
        );
        if (class_exists($controllerClassDetails->getFullName())) {
            throw new LogicException(sprintf("The %s class already exists.", $controllerClassDetails->getFullName()));
        }
        
        if ($config['search']['filter_view']['activate']
            || $config['search']['multi_select']
            || $config['search']['pagination']
        ) {
            $managerClassDetails = $generator->createClassNameDetails(
                $this->bag->get('name_upper_camel_case').'Manager',
                'Manager'.$directoryName
            );
            if (class_exists($managerClassDetails->getFullName())) {
                throw new LogicException(sprintf("The %s class already exists.", $managerClassDetails->getFullName()));
            }
            $this->bag->setElement('manager', $managerClassDetails);
        }
        
        if ($config['create']['activate'] || $config['update']['activate']) {
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
            if ($config['update']['activate']) {
                if ('UpdateType' === $this->bag->get('config')['update']['form_type']) {
                    $formUpdateClassDetails = $generator->createClassNameDetails(
                        $this->bag->get('name_upper_camel_case'),
                        'Form'.$directoryName,
                        'UpdateType'
                    );
                    if (class_exists($formUpdateClassDetails->getFullName())) {
                        throw new LogicException(sprintf("The %s class already exists.", $formUpdateClassDetails->getFullName()));
                    }
                    
                    $this->bag->setElement('form_update', $formUpdateClassDetails);
                    $generator->generateClass(
                        $formUpdateClassDetails->getFullName(),
                        $this->getOverheadPath('form/UpdateType.tpl.php'),
                        $this->bag->all()
                    );
                } else {
                    $this->bag->setElement('form_update', $formClassDetails);
                }
            }
        }
        
        if ($config['voter']) {
            $voterClassDetails = $generator->createClassNameDetails(
                $this->bag->get('name_upper_camel_case'),
                'Security\\Voter'.$directoryName,
                'Voter'
            );
            if (class_exists($voterClassDetails->getFullName())) {
                throw new LogicException(sprintf("The %s class already exists.", $voterClassDetails->getFullName()));
            }
        }
        
        $templates = [];
        if ($config['search']['filter_view']['activate']) {
            $formFilterClassDetails = $generator->createClassNameDetails(
                $this->bag->get('name_upper_camel_case'),
                'Form'.$directoryName,
                'FilterType'
            );
            if (class_exists($formFilterClassDetails->getFullName())) {
                throw new LogicException(sprintf("The %s class already exists.", $formFilterClassDetails->getFullName()));
            }
            $this->bag->setElement('form_filter', $formFilterClassDetails);
            
            $generator->generateClass(
                $formFilterClassDetails->getFullName(),
                $this->getOverheadPath('form/FilterType.tpl.php'),
                $this->bag->all()
            );
            $templates['search/_filter'] = $this->bag->all();
        }
        
        if ($config['update']['multi_select']) {
            $formCollectionUpdateClassDetails = $generator->createClassNameDetails(
                $this->bag->get('name_upper_camel_case'),
                'Form'.$directoryName,
                'CollectionUpdateType'
            );
            if (class_exists($formCollectionUpdateClassDetails->getFullName())) {
                throw new LogicException(sprintf("The %s class already exists.", $formCollectionUpdateClassDetails->getFullName()));
            }
            $this->bag->setElement('form_collection_update', $formCollectionUpdateClassDetails);
        }
        
        if ($config['search']['multi_select']) {
            $formBatchClassDetails = $generator->createClassNameDetails(
                $this->bag->get('name_upper_camel_case'),
                'Form'.$directoryName,
                'BatchType'
            );
            if (class_exists($formBatchClassDetails->getFullName())) {
                throw new LogicException(sprintf("The %s class already exists.", $formBatchClassDetails->getFullName()));
            }
            $this->bag->setElement('form_batch', $formBatchClassDetails);
        }
        
        if ($config['search']['filter_view']['activate']
            || $config['search']['multi_select']
            || $config['search']['pagination']
        ) {
            $generator->generateClass(
                $managerClassDetails->getFullName(),
                $this->getOverheadPath('manager/Manager.tpl.php'),
                $this->bag->all()
            );
        }
        
        if ($config['update']['multi_select']) {
            $generator->generateClass(
                $formCollectionUpdateClassDetails->getFullName(),
                $this->getOverheadPath('form/CollectionUpdateType.tpl.php'),
                $this->bag->all()
            );
        }
        
        if ($config['search']['multi_select']) {
            $generator->generateClass(
                $formBatchClassDetails->getFullName(),
                $this->getOverheadPath('form/BatchType.tpl.php'),
                $this->bag->all()
            );
        }
        
        $templates['search/index'] = $this->bag->all();
        $templates['search/_list'] = $this->bag->all();
        
        if ($config['search']['pagination']) {
            $templates['search/_pagination'] = $this->bag->all();
        }
        
        $generator->generateController(
            $controllerClassDetails->getFullName(),
            $this->getOverheadPath('controller/Controller.tpl.php'),
            $this->bag->all()
        );

        if ($config['voter']) {
            $generator->generateClass(
                $voterClassDetails->getFullName(),
                $this->getOverheadPath('security/voter/Voter.tpl.php'),
                $this->bag->all()
            );
        }
        
        if ($config['create']['activate']) {
            $templates['create'] = $this->bag->all();
        }
        if ($config['read']['activate']) {
            $templates['read'] = $this->bag->all();
        }
        if ($config['update']['activate']) {
            $templates['update'] = $this->bag->all();
        }
        if ($config['delete']['multi_select']) {
            $templates['delete'] = $this->bag->all();
        }
        
        foreach ($templates as $template => $variables) {
            $generator->generateFile(
                'templates/'.$this->bag->get('templates_path').'/'.$template.'.html.twig',
                $this->getOverheadPath('templates/'.$template.'.tpl.php'),
                $variables
            );
        }
        
        
        $this->generateRepositoryMethods($io);
        
        $this->generateTranslationFiles($io);
        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text(
            sprintf(
                'Next: Check your new SCRUD by going to <fg=yellow>%s/</>',
                $this->bag->get('route_path')."/search"
            )
        );
    }
    
    private function getOverheadPath(string $relativePath)
    {
        $skeleton = $this->bag->get('config')['skeleton'];
        $path = $this->getAppRootDir().'/templates/bundles/DFMakerBundle/'.$skeleton.'/scrud/'.$relativePath;
        if (file_exists($path)) {
            return $path;
        }
        $path = __DIR__.'/../Resources/skeleton/'.$skeleton.'/scrud/'.$relativePath;
        if (!file_exists($path)) {
            throw new \Exception(sprintf("The file %s was not found. Are you sure the skeleton is correctly configured ?", $path));
        }
        return $path;
    }

    private function generateRepositoryMethods(ConsoleStyle $io)
    {
        if (null === $this->bag->get('repository_path')) {
            return;
        }
        $config = $this->bag->get('config');
        $manipulator = new ClassSourceManipulator($this->fileManager->getFileContents($this->bag->get('repository_path')));
        $manipulator->setIo($io);
        
        if ($config['search']['pagination']) {
            $manipulator->addUseStatementIfNecessary('\Doctrine\ORM\Tools\Pagination\Paginator');
            $manipulator->addUseStatementIfNecessary('\Symfony\Component\HttpFoundation\Request');
            $manipulator->addUseStatementIfNecessary('\Symfony\Component\HttpFoundation\Session\Session');
            $manipulator->addUseStatementIfNecessary('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        }
        $method = $manipulator->createMethodBuilder($this->bag->get('search_method'), null, false, [
                sprintf(
                    '@return %S[] Returns an array of %s objects',
                    $this->bag->get('entity_upper_camel_case'),
                    $this->bag->get('entity_upper_camel_case')
                )
            ]);
        if ($config['search']['pagination']) {
            $method->addParam(
                (new \PhpParser\Builder\Param('request'))->setTypeHint('Request')
            );
            $method->addParam(
                (new \PhpParser\Builder\Param('session'))->setTypeHint('Session')
            );
        }
        if ($config['search']['filter_view']['activate']) {
            $method->addParam(
                (new \PhpParser\Builder\Param('data'))->setTypeHint('array')
            );
        }
        
        if ($config['search']['pagination']) {
            $method->addParam(
                (new \PhpParser\Builder\Param('page'))->setTypeHint('string')->makeByRef()
            );
            if (!$config['search']['filter_view']['activate']) {
                $method->addParam(
                    (new \PhpParser\Builder\Param('numberByPage'))->setTypeHint('int')
                );
            }
        }
        $methodBody = $this->templating->render(
            '@DFMaker/'.$config['skeleton'].'/scrud/repository/_search_method_body.php.twig',
            $this->bag->all()
        );
        
        $manipulator->addMethodBody($method, $methodBody);
        $manipulator->addMethodBuilder($method);
        
        $method = $manipulator->createMethodBuilder($this->bag->get('search_query_method'), null, false, [
                '@return \\Doctrine\\DBAL\\Query\\QueryBuilder',
            ]);
        
        if ($config['search']['filter_view']['activate']) {
            $method->addParam(
                (new \PhpParser\Builder\Param('data'))->setTypeHint('array')
            );
        }
        $methodBody = $this->templating->render(
            '@DFMaker/'.$config['skeleton'].'/scrud/repository/_get_search_query_method_body.php.twig',
            $this->bag->all()
        );
        
        $manipulator->addMethodBody($method, $methodBody);
        $manipulator->addMethodBuilder($method);
        
        $this->fileManager->dumpFile($this->bag->get('repository_path'), $manipulator->getSourceCode());
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
            $message = 'created: ' . 'translations/' . $this->bag->get('file_translation_name') . '.en.yaml';
        } else {
            $message = 'updated: ' . 'translations/' . $this->bag->get('file_translation_name') . '.en.yaml';
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
        
        $io->text($message);
        
        $locale = $this->container->getParameter("kernel.default_locale");
        
        if ('en' === $locale) {
            return;
        }
        
        $pathFileTranslation = $directoryName.$this->bag->get('file_translation_name').'.'.$locale.'.yaml';
        
        if (!$filesystem->exists($pathFileTranslation)) {
            $filesystem->touch($pathFileTranslation);
            $message = 'updated: ' . 'translations/' . $this->bag->get('file_translation_name') . '.' . $locale . '.yaml';
        } else {
            $message = 'updated: ' . 'translations/' . $this->bag->get('file_translation_name') . '.' . $locale . '.yaml';
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
        $io->text($message);
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
            TwigBundle::class,
            'twig-bundle'
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
