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

class MakeConfig extends AbstractMaker
{
    protected static $defaultName = 'MakeConfig';
    
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
        return 'df:make:config';
    }
    
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Create a config file for scrud generator')
            ->addArgument(
                'entity-class',
                InputArgument::OPTIONAL,
                sprintf('The class name of the entity to create SCRUD configuration (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm()))
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
        $entityClassDetails = $generator->createClassNameDetails(
            Validator::entityExists($input->getArgument('entity-class'), $this->doctrineHelper->getEntitiesForAutocomplete()),
            'Entity\\'
        );
        $directoryName = $this->getAppRootDir().'/config/scrud/';
        if (!is_dir($directoryName)) {
            mkdir($directoryName, 0755);
        }
        $config = [];
        $config['entity'] = $entityClassDetails->getShortName();
        $config['skeleton'] = 'scrud_bootstrap_4';
        $config['prefix_directory'] = null;
        $config['prefix_route'] = null;
        $config['voter'] = false;
        $config['search'] = [];
        $config['search']['filter'] = true; 
        $config['search']['pagination'] = true;
        $config['search']['multi_select'] = true;
        $config['create'] = [];
        $config['create']['activate'] = true;
        $config['read'] = [];
        $config['read']['activate'] = true;
        $config['update'] = [];
        $config['update']['activate'] = true;
        $config['update']['multi_select'] = true;
        $config['delete'] = [];
        $config['delete']['activate'] = true;
        $config['delete']['multi_select'] = true;
        
        $yaml = Yaml::dump($config);
        $pathFileConfig =  $directoryName . Str::asSnakeCase($entityClassDetails->getShortName()) . '.yaml';
        touch($pathFileConfig);
        file_put_contents($pathFileConfig, $yaml);
        $io->writeln('OK');
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
