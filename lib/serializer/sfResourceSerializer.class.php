<?php

abstract class sfResourceSerializer
{
  private $camelize = true;
  
  abstract public function getContentType();

  /**
   * This preg-free version of the camelizer is two times faster than
   * sfInflector::camelize()
   *
   * @author CakePHP
   * @see http://book.cakephp.org/view/572/Class-methods
   * @param  string $string  The string to camelize
   * @return string with CamelCase or underscored depending on configuration
   */
  protected function camelize($string)
  {
    if ($this->camelize)
    {
      return str_replace(" ", "", ucwords(str_replace("_", " ", $string)));
    }
    else
    {
      return sfInflector::underscore($string);
    }
  }

  /**
   * Tell the serializer to camelize names or to let them flat
   * 
   * @param boolean $camelize
   */
  public function setCamelize($camelize)
  {
    $this->camelize = $camelize;
  }

  /**
   * Creates an instance of a serializer
   *
   * @param  string  $format   The serializer format (xml, json, etc.)
   * @param  boolean $camelize Tell the serializer to Camelize nodes
   * @return object  a serializer object
   * @throws sfException
   */
  public static function getInstance($format = 'xml', $camelize = true)
  {
    $classname = sprintf('sfResourceSerializer%s', ucfirst($format));

    if (!class_exists($classname))
    {
      throw new sfException(sprintf('Could not find seriaizer "%s"', $classname));
    }

    $serializer = new $classname;
    $serializer->setCamelize($camelize);
    return $serializer;
  }

  abstract public function serialize($array, $rootNodeName = 'data', $collection = true, $plural_root_name = false);
  abstract public function unserialize($payload);
}