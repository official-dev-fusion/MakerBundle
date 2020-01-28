<?php

namespace DF\MakerBundle\Maker;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Validator\Validation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ScrudConfig extends AbstractMaker
{
    protected static $defaultName = 'ScrudConfig';
    
    /**
     *
     * @var ContainerInterface $container
     */
    private $container;

    /**
     *
     * @var DoctrineHelper
     */
    private $doctrineHelper;
    
    public function __construct(ContainerInterface $container, DoctrineHelper $doctrineHelper)
    {
        $this->container = $container;
        $this->doctrineHelper = $doctrineHelper;
    }

    public static function getCommandName(): string
    {
        return 'df:scrud:config';
    }
    
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Create a config file for scrud generator')
            ->addArgument(
                'entity-class',
                InputArgument::OPTIONAL,
                sprintf('The class name of the entity to create SCRUD configuration (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm()))
            )
            ->addOption('level',
                'l',
                InputOption::VALUE_REQUIRED,
                'Level to generate a scrud configuration file.',
                1
            );
        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        if (null === $input->getArgument('entity-class')) {
            $argument = $command->getDefinition()->getArgument('entity-class');
            $entities = $this->doctrineHelper->getEntitiesForAutocomplete();

            $question = new Question($argument->getDescription());
            $question->setAutocompleterValues($entities);

            $value = $io->askQuestion($question);

            $input->setArgument('entity-class', $value);
        }
    }
    
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $level = $input->getOption('level');
        $entityClassDetails = $generator->createClassNameDetails(
            Validator::entityExists($input->getArgument('entity-class'), $this->doctrineHelper->getEntitiesForAutocomplete()),
            'Entity\\'
        );
        
        $directoryName = $this->getAppRootDir().'/config/dev_fusion/';
        
        if (!is_dir($directoryName)) {
            mkdir($directoryName, 0755);
        }

        $directoryName .= 'scrud/';

        if (!is_dir($directoryName)) {
            mkdir($directoryName, 0755);
        }
        
        $entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());

        $config = [];
        $entity = &$config['entities'][$entityClassDetails->getShortName()];
         
        $entity['class'] = $entityClassDetails->getFullName();
        if ($level > 0) {    
            $fields = array_keys($entityDoctrineDetails->getDisplayFields());
            if ($level > 1) {
                $entity['skeleton'] = 'scrud_bootstrap_4';
            }
            $entity['prefix_directory'] = null;
            $entity['prefix_route'] = null;
            $entity['voter'] = false;
            $entity['fields'] = $fields;
            if ($level > 1) {
                $forms = array_keys($entityDoctrineDetails->getFormFields());
                $entity['forms'] = $forms;
            }
            $entity['search'] = [];
            if ($level > 1) {
                $entity['search']['dql_filter'] = '';
                $entity['search']['order'] = [
                    [
                        'by' => 'entity.'.$entityDoctrineDetails->getIdentifier(),
                        'direction' => 'DESC',
                    ]
                ];
            }
            $entity['search']['pagination'] = true;
            $entity['search']['multi_select'] = true;
            $entity['search']['filter_view']['activate'] = true;
            $entity['create'] = [];
            $entity['create']['activate'] = true;
            $entity['read'] = [];
            $entity['read']['activate'] = true;
            if ($level > 1) {
                $entity['read']['action_up'] = false;
                $entity['read']['action_down'] = false;
            }
            $entity['update'] = [];
            $entity['update']['activate'] = true;
            $entity['update']['multi_select'] = true;
            $entity['delete'] = [];
            $entity['delete']['activate'] = true;
            $entity['delete']['multi_select'] = true;
        }
        $yaml = Yaml::dump($config, 5);
        $pathFileConfig =  $directoryName . Str::asSnakeCase($entityClassDetails->getShortName()) . '.yaml';
        touch($pathFileConfig);
        file_put_contents($pathFileConfig, $yaml);
        $io->writeln('OK');
        $io->text(
            sprintf(
                'Next: Check your new SCRUD configuration file by going to <fg=yellow>%s/</>',
                $pathFileConfig
            )
        );
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
