<?php

namespace Reprovinci\DoctrineEncrypt\Subscribers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;

use Reprovinci\DoctrineEncrypt\Encryptors\EncryptorInterface;

use ReflectionClass;

/**
 * Doctrine event subscriber which encrypt/decrypt entities
 */
class DoctrineEncryptSubscriber implements EventSubscriber
{
    /**
     * Encryptor interface namespace 
     */
    const ENCRYPTOR_INTERFACE_NS = 'Reprovinci\DoctrineEncrypt\Encryptors\EncryptorInterface';
    
    /**
     * Encrypted annotation full name
     */
    const ENCRYPTED_ANN_NAME = 'Reprovinci\DoctrineEncrypt\Configuration\Encrypted';

    /**
     * Encryptor
     * @var EncryptorInterface 
     */
    private $encryptor;

    /**
     * Annotation reader
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $annReader;
    
    /**
     * Registr to avoid multi decode operations for one entity
     * @var array
     */
    private $decodedRegistry = array();

    /**
     * Caches information on an entity's encrypted fields in an array keyed on
     * the entity's class name. The value will be a list of Reflected fields that are encrypted.
     *
     * @var array
     */
    private $encryptedFieldCache = array();

    /**
     * Initialization of subscriber
     * @param Reader $annReader
     * @param EncryptorInterface $encryptor
     */
    public function __construct(Reader $annReader, EncryptorInterface $encryptor)
    {
        $this->annReader = $annReader;
        $this->encryptor = $encryptor;
    }

    /**
     * Listen a preUpdate lifecycle event. Checking and encrypt entities fields
     * which have @Encrypted annotation. Using changesets to avoid preUpdate event
     * restrictions
     * @param LifecycleEventArgs $args 
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entity = $args->getEntity();

        $properties = $this->getEncryptedFields($entity, $em);
        foreach ($properties as $refProperty) {
            if ($this->annReader->getPropertyAnnotation($refProperty, self::ENCRYPTED_ANN_NAME)) {
                $propName = $refProperty->getName();
                $args->setNewValue($propName, $this->encryptor->encrypt($args->getNewValue($propName)));
            }
        }
    }
    
    /**
     * Listen a postLoad lifecycle event. Checking and decrypt entities
     * which have @Encrypted annotations
     * @param LifecycleEventArgs $args 
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if(!$this->hasInDecodedRegistry($entity, $args->getEntityManager())) {
            if($this->processFields($entity, $em, false)) {
                $this->addToDecodedRegistry($entity, $args->getEntityManager());
            }
        }
    }

    /**
     * Realization of EventSubscriber interface method.
     * @return Array Return all events which this subscriber is listening
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::preUpdate,
            Events::postLoad,
        );
    }
    
    /**
     * Capitalize string
     * @param string $word
     * @return string
     */
    public static function capitalize($word)
    {
        if(is_array($word)) {
            $word = $word[0];
        }
        
        return str_replace(' ', '', ucwords(str_replace(array('-', '_'), ' ', $word)));
    }

    /**
     * Process (encrypt/decrypt) entities fields
     * @param object $entity Some doctrine entity
     * @param \Doctrine\ORM\EntityManager $em
     * @param Boolean $isEncryptOperation If true - encrypt, false - decrypt entity
     * @return bool
     */
    private function processFields($entity, Entitymanager $em, $isEncryptOperation = true)
    {
        $properties = $this->getEncryptedFields($entity, $em);

        $withAnnotation = false;
        foreach ($properties as $refProperty) {
            if ($this->annReader->getPropertyAnnotation($refProperty, self::ENCRYPTED_ANN_NAME)) {
                $withAnnotation = true;
                // we have annotation and if it decrypt operation, we must avoid duble decryption
                $refProperty->setAccessible(true);
                $value = $refProperty->getValue($entity);

                $value = $isEncryptOperation?
                    $this->encryptor->encrypt($value) :
                    $this->encryptor->decrypt($value);

                $refProperty->setValue($entity, $value);
            }
        }
        
        return $withAnnotation;
    }
    
    /**
     * Check if we have entity in decoded registry
     * @param Object $entity Some doctrine entity
     * @param \Doctrine\ORM\EntityManager $em
     * @return boolean
     */
    private function hasInDecodedRegistry($entity, EntityManager $em)
    {
        $className = get_class($entity);
        $metadata = $em->getClassMetadata($className);
        $getter = 'get' . self::capitalize($metadata->getIdentifier());
        
        return isset($this->decodedRegistry[$className][$entity->$getter()]);
    }
    
    /**
     * Adds entity to decoded registry
     * @param object $entity Some doctrine entity
     * @param \Doctrine\ORM\EntityManager $em
     */
    private function addToDecodedRegistry($entity, EntityManager $em)
    {
        $className = get_class($entity);
        $metadata = $em->getClassMetadata($className);
        $getter = 'get' . self::capitalize($metadata->getIdentifier());
        $this->decodedRegistry[$className][$entity->$getter()] = true;
    }


    /**
     * @param $entity
     * @param EntityManager $em
     * @return \ReflectionProperty[]
     */
    private function getEncryptedFields($entity, EntityManager $em)
    {
        $className = get_class($entity);

        if (isset($this->encryptedFieldCache[$className]))
            return $this->encryptedFieldCache[$className];

        $meta = $em->getClassMetadata($className);

        $encryptedFields = array();
        foreach ($meta->getReflectionProperties() as $refProperty) {
            if ($this->annReader->getPropertyAnnotation($refProperty, self::ENCRYPTED_ANN_NAME)) {
                $encryptedFields[] = $refProperty;
            }
        }

        $this->encryptedFieldCache[$className] = $encryptedFields;

        return $encryptedFields;
    }
}
