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
use Symfony\Component\Filesystem\Filesystem;
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
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Config\Definition\Processor;
use DF\MakerBundle\ScrudBag;
use DF\MakerBundle\ScrudConfiguration;

/**
 * @author Martin GILBERT <martin3129@gmail.com>
 */
final class MakeScrud extends AbstractMaker
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
     * @var EngineInterface
     */
    private $templating;
    
    /**
     * 
     * @var ScrudBag
     */
    private $bag;
    
    public function __construct(ContainerInterface $container, FileManager $fileManager, DoctrineHelper $doctrineHelper, EngineInterface $templating)
    {
        $this->container = $container;
        $this->fileManager = $fileManager;
        $this->doctrineHelper = $doctrineHelper;
        $this->templating = $templating;
    }
    
    public static function getCommandName(): string
    {
        return 'df:make:scrud';
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
        $configFilePath = $this->getAppRootDir().'\\config\\scrud\\'.$input->getArgument('config-file');
        $processor = new Processor();
        $config = Yaml::parseFile($configFilePath);
        $processor->processConfiguration(new ScrudConfiguration(), [ 'scrud_config' => $config ]);
        
        $entityClassDetails = $generator->createClassNameDetails(
            Validator::entityExists($config['entity'], $this->doctrineHelper->getEntitiesForAutocomplete()),
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
        }
        
        $this->bag = new ScrudBag($entityClassDetails, $this->doctrineHelper, $repositoryClassDetails, $config);
        $prefix = $config['prefix_directory'];
        
        /*echo ('<pre>');
        //var_dump($this->bag);
        echo ('</pre>');
        var_dump($config);
        die();
        */
        if (null !== $entityDoctrineDetails->getRepositoryClass()) {
            $reflectionClass = new \ReflectionClass($entityDoctrineDetails->getRepositoryClass());
            $this->bag->set('repository_path', $reflectionClass->getFileName());
        }             

        $directoryName = '\\';
        if ($prefix) {
            $directoryName .= ucfirst($prefix).'\\';
        }
        
        $controllerClassDetails = $generator->createClassNameDetails(
            $entityClassDetails->getRelativeNameWithoutSuffix(),
            'Controller'.$directoryName,
            'Controller'
        );
        if (class_exists($controllerClassDetails->getFullName())) {
            throw new LogicException(sprintf("The %s class already exists.", $controllerClassDetails->getFullName()));
        }
        
        if ($config['search']['filter'] 
            || $config['search']['multi_select']
            || $config['search']['pagination']
        ) {
            $managerClassDetails = $generator->createClassNameDetails(
                $entityClassDetails->getShortName().'Manager',
                'Manager'
            );
            if (class_exists($managerClassDetails->getFullName())) {
                throw new LogicException(sprintf("The %s class already exists.", $managerClassDetails->getFullName()));
            }
            $this->bag->setElement('manager', $managerClassDetails);
        }
        
        if ($config['create']['activate'] || $config['update']['activate']) {
            $formClassDetails = $generator->createClassNameDetails(
                $entityClassDetails->getRelativeNameWithoutSuffix(),
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
        }
        
        if ($config['voter']) {
            $voterClassDetails = $generator->createClassNameDetails(
                $entityClassDetails->getRelativeNameWithoutSuffix().'Voter',
                'Security\\Voter'
            );
            if (class_exists($voterClassDetails->getFullName())) {
                throw new LogicException(sprintf("The %s class already exists.", $voterClassDetails->getFullName()));
            }
        }
        
        $templates = [];
        if ($config['search']['filter']) {
            $formFilterClassDetails = $generator->createClassNameDetails(
                $entityClassDetails->getRelativeNameWithoutSuffix(),
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
            $formUpdateClassDetails = $generator->createClassNameDetails(
                $entityClassDetails->getRelativeNameWithoutSuffix(),
                'Form'.$directoryName,
                'UpdateType'
            );
            if (class_exists($formUpdateClassDetails->getFullName())) {
                throw new LogicException(sprintf("The %s class already exists.", $formUpdateClassDetails->getFullName()));
            }
            $this->bag->setElement('form_update', $formUpdateClassDetails);
        }
        
        if ($config['search']['multi_select']) {
            $formUpdateSearchClassDetails = $generator->createClassNameDetails(
                $entityClassDetails->getRelativeNameWithoutSuffix(),
                'Form'.$directoryName,
                'UpdateSearchType'
            );
            if (class_exists($formUpdateSearchClassDetails->getFullName())) {
                throw new LogicException(sprintf("The %s class already exists.", $formUpdateSearchClassDetails->getFullName()));
            }    
            $this->bag->setElement('form_update_search', $formUpdateSearchClassDetails);
        }
        
        if ($config['search']['filter'] 
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
                $formUpdateClassDetails->getFullName(),
                $this->getOverheadPath('form/UpdateType.tpl.php'),
                $this->bag->all()
            );
        }
        
        if ($config['search']['multi_select']) {
            $generator->generateClass(
                $formUpdateSearchClassDetails->getFullName(),
                $this->getOverheadPath('form/UpdateSearchType.tpl.php'),
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
        
        if ($config['search']['filter'] || $config['search']['pagination']) {
            $this->generateRepositoryMethods($io);
        }
        $this->generateTranslationFiles($io);
        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text(sprintf('Next: Check your new CRUD by going to <fg=yellow>%s/</>', 
            Str::asRoutePath($controllerClassDetails->getRelativeNameWithoutSuffix()))
        );
    }
    
    private function getOverheadPath(string $relativePath)
    {
        $path = $this->getAppRootDir().'/templates/bundles/DFMakerBundle/'.$relativePath;
        if (file_exists($path)) {
            return $path;
        }
        return __DIR__.'/../Resources/skeleton/scrud/'.$relativePath;
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
        $method = $manipulator->createMethodBuilder('search', null, false, [
                sprintf('@return %S[] Returns an array of %s objects', 
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
        if ($config['search']['filter']) {
            $method->addParam(
                (new \PhpParser\Builder\Param('data'))->setTypeHint('array')
                );
        }
        
        if ($config['search']['pagination']) {
            $method->addParam(
                (new \PhpParser\Builder\Param('page'))->setTypeHint('string')->makeByRef()
                );
            if (!$config['search']['filter']) {
                $method->addParam(
                    (new \PhpParser\Builder\Param('numberByPage'))->setTypeHint('int')
                    );
            }
        } 
        $methodBody = $this->templating->render(
            '@DFMaker/scrud/repository/_search_method_body.php.twig', 
            $this->bag->all()
        );
        
        $manipulator->addMethodBody($method, $methodBody);
        $manipulator->addMethodBuilder($method);
        
        $method = $manipulator->createMethodBuilder('getSearchQuery', null, false, [
                '@return \\Doctrine\\DBAL\\Query\\QueryBuilder',
            ]);
        
        if ($config['search']['filter']) {
            $method->addParam(
                    (new \PhpParser\Builder\Param('data'))->setTypeHint('array')
                );
        }
        $methodBody = $this->templating->render(
            '@DFMaker/scrud/repository/_get_search_query_method_body.php.twig',
            $this->bag->all()
        );
        
        $manipulator->addMethodBody($method, $methodBody);
        $manipulator->addMethodBuilder($method);
        
        $this->fileManager->dumpFile($this->bag->get('repository_path'), $manipulator->getSourceCode());
    }
    
    private function generateTranslationFiles(ConsoleStyle $io)
    {
        $config = $this->bag->get('config');
        $translation = [];
        $translation['entity_singular'] = $this->bag->get('entity_human_words_ucfirst');
        $translation['entity_plural'] = $this->bag->get('entity_human_words_ucfirst_plural');
        $translation['action']['delete'] = 'Delete';
        $translation['action']['update'] = 'Update';
        $translation['message']['no_data_found'] = 'No data found';
        if ($config['search']['multi_select']) {
            $translation['message']['no_element_selected'] = 'No element selected';
        }
        if ($config['create']['activate']) {
            $translation['message']['create'] = $this->bag->get('entity_human_words_ucfirst').' created (%identifier%).';
        }
        if ($config['update']['activate']) {
            $translation['message']['update'] = $this->bag->get('entity_human_words_ucfirst').' updated.';
        }
        if ($config['delete']['activate']) {
            $translation['message']['delete_confirm'] = 'Confirmation of deletion';
            if ($config['delete']['multi_select']) {
                $translation['message']['delete_list'] = $this->bag->get('entity_human_words_ucfirst').'(s) deleted.';
            } elseif ($config['delete']['multi_select']) {
                $translation['message']['delete'] = $this->bag->get('entity_human_words_ucfirst').' deleted.';
            }
        }
        if ($config['create']['activate'] || $config['read']['activate'] || $config['update']['activate'] || $config['delete']['multi_select']) {
            $translation['button']['back'] = 'Back';
        }
        if ($config['create']['activate'] || $config['update']['activate']) {
            $translation['button']['submit'] = 'Submit';
        }
        if ($config['create']['activate']) {
            $translation['button']['create_title'] = 'Add';
        }
        if ($config['read']['activate']) {
            $translation['button']['read_title'] = 'Read';
        }
        if ($config['update']['activate'] || $config['search']['multi_select']) {
            $translation['button']['update_title'] = 'Update';
        }
        if ($config['delete']['activate']) {
            $translation['button']['delete_title'] = 'Delete';
        }
        if ($config['search']['filter']) {
            $translation['button']['filter'] = 'Filter';
            $translation['form_labels']['search'] = 'Search';
            if ($config['search']['pagination']) {
                $translation['form_labels']['number_by_page'] = 'Number by page';
            }
        }
        if ($config['search']['multi_select']) {
            $translation['fields']['select_all'] = 'Select all';
        } elseif ($config['read']['activate'] || $config['update']['activate'] || $config['delete']['activate']) {
            $translation['fields']['action'] = 'Action(s)';
        }
        foreach ($this->bag->get('entity_form_fields') as $field) {
            $translation['form_labels'][$field['field_snake_case']] = ucfirst(str_replace('_', ' ', $field['field_snake_case']));
        }
        foreach ($this->bag->get('entity_fields') as $field) {
            $translation['fields'][$field['field_snake_case']] = ucfirst(str_replace('_', ' ', $field['field_snake_case']));
        }
        $translation['search']['title'] = 'Search - '.$this->bag->get('entity_human_words_ucfirst');
        $translation['search']['h1'] = 'Search - '.$this->bag->get('entity_human_words_ucfirst');
        $translation['search']['h2'] = 'Search';
        if ($config['create']['activate']) {
            $translation['create']['title'] = 'Create - '.$this->bag->get('entity_human_words_ucfirst');
            $translation['create']['h1'] = 'Create - '.$this->bag->get('entity_human_words_ucfirst');
            $translation['create']['h2'] = 'Create form';
        }
        if ($config['read']['activate']) {
            $translation['read']['title'] = 'Read - '.$this->bag->get('entity_human_words_ucfirst');
            $translation['read']['h1'] = '%identifier%';
            $translation['read']['h2'] = 'Details';
        }
        if ($config['update']['activate']) {
            $translation['update'] = [];
            $translation['update']['title'] = 'Update - '.$this->bag->get('entity_human_words_ucfirst');
            $translation['update']['h1'] = 'Update - '.$this->bag->get('entity_human_words_ucfirst');
            $translation['update']['h2'] = 'Update form';
        }
        if ($config['delete']['activate']) {
            $translation['delete']['modal_title'] = 'Delete - '.$this->bag->get('entity_human_words_ucfirst');
            $translation['delete']['modal_button'] = 'Delete';
            $translation['delete']['modal_button_cancel'] = 'Cancel';
            $translation['delete']['warning'] = 'The deletion will be final.';
        }
        if ($config['delete']['multi_select']) {
            $translation['delete']['h1'] = 'Delete - '.$this->bag->get('entity_human_words_ucfirst');
            $translation['delete']['h2'] = 'The following list will be deleted';
        }
        
        $yamlEnglish = Yaml::dump($translation);
        $directoryName = $this->getAppRootDir().'/translations/';
        if (!is_dir($directoryName)) {
            mkdir($directoryName, 0755);
        }
        $pathFileTranslation = $directoryName.$this->bag->get('entity_translation_name').'.en.yaml';
        touch($pathFileTranslation);
        file_put_contents($pathFileTranslation, $yamlEnglish);
        $local = $this->container->getParameter("kernel.default_locale");
        foreach ($translation as &$elements) {
            if (is_array($elements)) {
                foreach ($elements as &$element) {
                    $element = $this->transElement($element, $local);
                }
            } else {
                $elements = $this->transElement($elements, $local);
            }
        }
        $yamlLocal = Yaml::dump($translation);
        $pathFileTranslation = $directoryName.$this->bag->get('entity_translation_name').'.'.$local.'.yaml';
        touch($pathFileTranslation);
        file_put_contents($pathFileTranslation, $yamlLocal);
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
        $transcript = $translator->trans($element, [], 'crud', $local);
        if ($transcript !== $element) {
            return $transcript;
        }
        $words = explode(' ', $element);
        if (count($words) < 2) {
            return $element;
        }
        $transcript = '';
        foreach ($words as $word) {
            $transcript .= $translator->trans($word, [], 'crud', $local);
            if (next($words)) { $transcript .= ' '; }
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
