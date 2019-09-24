# Introduction:

This bundle is a code generation tool. It draws heavily on the symfony/maker-bundle by adding several features and more flexibility.

It was designed to generate easily adaptable code respecting a SCRUD philosophy (search, create, read, update and delete) from a Doctrine entity.

The df:make:config command generates a configuration.yaml file based on a given entity located in App\Entity. This file will be used later to generate the code. The file must be customized to generate the expected code. It ends up in the config/scrud folder.

The df:make:scrud command generates an advanced controller from a configuration file located in config/scrud. This controller allows you to perform the five basic operations on a model.

* Search: List of all records, filter, pagination and multi-selection;
* Read: Display of a given record identified by its primary key;
* Create: Create a new record;
* Update: Edit one or more existing record(s);
* Delete: Delete one or more existing record(s);

## Features:
* Extraction of strings from views and generation of translation files.
* Ability to customize the translation file generated in the local language.
* Ability to replace skeletons templates to generate custom code.
* Ability to create multiple skeletons and choose from the configuration file that will be used to generate the code.
* Default skeletons use Bootstrap4 and JQuery in generated views to enhance the visual experience.
* Configuration of a subfolder to correctly separate the generated code (Example: Controller / Back or Controller / Front).
* Configuration of a sub-road to separate the different parts of the application (Example: admin / user / read).
* Possibility to generate a Vote to manage the access of each of the actions SCRUD according to the role of the user.
* Ability to choose the SCRUD actions that will be generated. Only the search action is required.
* Ability to generate a filter to search in each of the entity's strings or text attributes.
* Ability to generate a pagination in which the user can change the number of items per page directly in the search filter.
* Possibility to generate a form allowing to select several elements at the same time in order to launch multiple actions (Example: Deletion of several elements at once).
* Generation of an entity manager to better structure the generated code.
* Modification of the repository linked to the entity to create search methods for the filter.

## Notes :
You must add block content in base.html.twig
