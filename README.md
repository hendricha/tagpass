# TagPass and TargetedTagPass
Simple solution to add Symfony service references to another service definitions
by method calls, during container compile time.

Basically this library contains two configurable versions of the "Working with
Tagged Services" example code of Symfony.

## TagPass
This let's you collect all servcies with a certain tag and add them to a method
call to a certain service or services. See the example below:

```php
namespace AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use HendrichA\TagPassBundle\TagPass;

class AppBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);

    //This compiler pass will add all services tagged with foo to the
    //repository_of_all_things_foo service, by the addFoo method call.
    $fooPass = new TagPass('foo');
    $container->addCompilerPass(
      $fooPass->addServiceIdsTo('repository_of_all_things_foo', 'addFoo')
    );

    //You can add the tagged services to multiple service definitions too.
    $barPass = new TagPass('bar');
    $container->addCompilerPass(
      $barPass
        ->addServiceIdsTo('repository_of_all_things_bar', 'addBar')
        ->addServiceIdsTo('repository_of_all_the_things', 'addSomething')
      );
   }
}
```

## TargetedTagPass
This compiler pass can add tagged services to other service definitions that
are defined in the tag itself.

```php
//SomeBundle.php
//...
$fooPass = new TargetedTagPass('form_extension', addExtension);
$container->addCompilerPass(fooPass);
```

```yml
#services.yml
services:
  login_form:
    class: LoginForm

  #This service will be added to login_form, because the tag specifies it
  login_extension:
    class: LoginExtension
    tags:
      - { name: form_extension, service: login_form }


  foo_form:
    class: Form
    tags:
      - { name: very_extensible_form }

  bar_form:
    class: Form
    tags:
      - { name: very_extensible_form }

  #This service will be added to both of the above service, since they are both
  #have the "very_extensible_form" tag
  foobar_extension:
    class: VeryExtensibleFormExtension
    tags:
      - { name: form_extension, tag: very_extensible_form }
```

##Testing
This repository contains functional tests for the compiler passes, that can be
execute by phpunit. Because they require certain Symfony classes to be present,
*phpunit* should be called from your symfony application root.
```bash
$ phpunit -c app/phpunit.xml vendor/hendricha/tagpass-bundle/HendrichA/TagPassBundle/Tests
```
