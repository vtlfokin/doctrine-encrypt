# DoctrineEncrypt

Package encrypts and decrypts Doctrine fields through life cycle events. This version of the Doctrine Encrypt package
distinguishes itself with the following features:

- Superior Annotation parsing & caching using Doctrine's built in libraries for superior performance
- Totally transparent field encryption: the value will only be encrypted in the database, never in the value
- Unit testing

## Installation
Add `reprovinci/doctrine-encrypt` to your Composer manifest.

```js
{
    "require": {
        "reprovinci/doctrine-encrypt": "~3.0"
    }
}
```

## Configuration
### Using ZF2
Check out the doctrine-encrypt-module at https://github.com/51systems/doctrine-encrypt-module

### Manually
Add the event subscriber to your entity manager's event manager. Assuming `$em` is your configured entity manager:

```php
<?php

//You should pick your own hexadecimal secret
$secret = pack("H*", "dda8e5b978e05346f08b312a8c2eac03670bb5661097f8bc13212c31be66384c");

$subscriber = new DoctrineEncryptSubscriber(
    new \Doctrine\Common\Annotations\AnnotationReader,
    new \DoctrineEncrypt\Encryptors\AES256Encryptor($secret)
);

$eventManager = $em->getEventManager();
$eventManager->addEventSubscriber($encrypt_subscriber);
```

## Usage
```php
<?php

namespace Your\Namespace;

use Doctrine\ORM\Mapping as ORM;

use DoctrineEncrypt\Configuration\Encrypted;

/**
 * @ORM\Entity
 */
class Entity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     * @Encrypted
     * @var string
     */
    protected $secret_data;
}
```

## License

This bundle is under the MIT license. See the complete license in the bundle

## Versions

I'm using Semantic Versioning like described [here](http://semver.org).
