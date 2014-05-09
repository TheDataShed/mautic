<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;

//Routing
$container->setDefinition ('mautic.route_loader',
    new Definition(
        'Mautic\CoreBundle\Routing\RouteLoader',
        array(
            new Reference('service_container')
        )
    )
)
    ->addTag('routing.loader');

//Core permissions class
$container->setDefinition ('mautic.security',
    new Definition(
        'Mautic\CoreBundle\Security\Permissions\CorePermissions',
        array(
            new Reference('service_container'),
            new Reference('doctrine.orm.entity_manager'),
            '%mautic.bundles%',
            new Reference('security.context')
        )
    )
);
