<?php

namespace DF\MakerBundle\Maker;

use DF\MakerBundle\ScrudConfiguration;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Martin GILBERT <martin.gilbert@dev-fusion.com>
 */
final class ScrudConfigDebug extends AbstractMaker
{
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
        return 'df:scrud:config-debug';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Display default config.')
            ->addArgument(
                'config-file',
                InputArgument::REQUIRED,
                sprintf('The yaml file name to create SCRUD.')
            );

        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $configFilePath = $this->getAppRootDir() . '\\config\\dev_fusion\\scrud\\' . $input->getArgument('config-file');
        $processor = new Processor();
        $config = Yaml::parseFile($configFilePath);
        $config = $processor->processConfiguration(new ScrudConfiguration($this->doctrineHelper, $generator), ['scrud_config' => $config, ]);

        $io->text(Yaml::dump($config, 5));
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
            Validation::class,
            'validator'
        );
    }
}
