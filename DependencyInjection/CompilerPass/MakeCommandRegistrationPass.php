<?php

namespace DF\MakerBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MakeCommandRegistrationPass implements CompilerPassInterface
{
    
    const MAKER_SCRUD_ALIAS = 'df.maker.make_scrud';

    public function process(ContainerBuilder $container)
    {
        
    }
}
