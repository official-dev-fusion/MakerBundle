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

class FormConfig extends AbstractMaker
{
    protected static $defaultName = 'FormConfig';
    
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
        return 'df:form:config';
    }
    
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Create a config file for form generator')
            ->addArgument(
                'entity-class',
                InputArgument::OPTIONAL,
                sprintf('The class name of the entity to create Form configuration (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm()))
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
        
        $directoryName = $this->getAppRootDir().'/config/dev_fusion/';
        
        if (!is_dir($directoryName)) {
            mkdir($directoryName, 0755);
        }
        
        $directoryName .= 'form/';
        
        if (!is_dir($directoryName)) {
            mkdir($directoryName, 0755);
        }

        $entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());
        $defaultFields = $entityDoctrineDetails->getDisplayFields();
        $fields=[];
        foreach ($defaultFields as $key => $value) {
            $fields[] = $key;
        }

        $config = [];
        $entity = &$config['forms'][$entityClassDetails->getShortName()];
         
        $entity['entity_class'] = $entityClassDetails->getFullName();
        
        $entity['skeleton'] = 'scrud_bootstrap_4';
        $entity['prefix_directory'] = null;
        $entity['fields'] = $fields;
        
        $yaml = Yaml::dump($config, 5);
        $pathFileConfig =  $directoryName . Str::asSnakeCase($entityClassDetails->getShortName()) . '.yaml';
        touch($pathFileConfig);
        file_put_contents($pathFileConfig, $yaml);
        $io->writeln('OK');
        $io->text(
            sprintf(
                'Next: Check your new Form configuration file by going to <fg=yellow>%s/</>',
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
