<?php

class sfResourceSerializerXml extends sfResourceSerializer
{
  public function getContentType()
  {
    return 'application/xml';
  }

  public function serialize($array, $rootNodeName = 'data', $collection = true, $pluralRootNodeName = false)
  {
    $camelizedRootNodeName = $this->camelize($rootNodeName);

    if ($pluralRootNodeName)
    {
      $pluralRootNodeName = $this->camelize($pluralRootNodeName);
    }

    return $this->arrayToXml($array, $camelizedRootNodeName, 0, $collection, $pluralRootNodeName);
  }

  /**
   * Transform the payload into array assuming the payload is XML formatted.
   *
   * @param string $payload
   * @return array
   * @throw Exception
   */
  public function unserialize($payload)
  {
    libxml_use_internal_errors(true);
    $payload = trim($payload);

    if (empty($payload))
    {
      throw new sfException("Empty payload, can't unserialize it.");
    }

    // Remove all the XML comments
    // Because SimpleXml use them as SimpleXmlElement and there is NO way
    // to know if a node is a comment or an Element.
    $payload = preg_replace('~<!--.+?-->~sm', '', $payload);

    // Try to parse the XML
    $xml = @simplexml_load_string(
      $payload,
      'SimpleXMLElement',
      LIBXML_NONET
    );

    // If false, there is a parse error.
    if ($xml === false)
    {
      $errors = libxml_get_errors();
      $exception_message = '';

      foreach ($errors as $error)
      {
        $exception_message .= $this->formatXmlError($error);
      }

      libxml_clear_errors();
      throw new sfException("XML parsing error(s): \n".$exception_message);
    }

    $return = $this->unserializeToArray($xml);

    return $return;
  }

  /**
   * Return a formatted LibXml Error message
   * @see http://www.php.net/manual/en/function.libxml-get-errors.php
   * @param LibXMLError $error
   * @return string
   */
  protected function formatXmlError($error)
  {
    $return  = "\n\n";

    switch ($error->level)
    {
      case LIBXML_ERR_WARNING:
        $return .= "Warning $error->code: ";
        break;
      case LIBXML_ERR_ERROR:
        $return .= "Error $error->code: ";
        break;
      case LIBXML_ERR_FATAL:
        $return .= "Fatal Error $error->code: ";
        break;
    }

    $return .= trim($error->message) . "\n  Line: $error->line" .  "\n  Column: $error->column";

    if ($error->file)
    {
        $return .= "\n  File: $error->file";
    }

    return "$return\n\n--------------------------------------------\n\n";
  }

  protected function unserializeToArray($data)
  {
    if ($data instanceof SimpleXMLElement)
    {
      $data = (array) $data;
    }

    if (is_array($data))
    {
      foreach ($data as $name => $item)
      {
        // If the node is neither Array, nor any collection of data. Test also for empty or space only SimpleXMLElement
        if (
                (!is_array($item) && (!is_object($item))) ||
                ($item instanceof SimpleXMLElement &&
                        (count((array) $item) < 1 || (trim((string)$item) === '') ) &&
                        !(($tmp = (array) $item) && count($tmp) > 0 && !isset($tmp[0])) // Deep array with keys?
                )
           )
        {

          $item = trim((string)$item);
          unset($data[$name]);

          if ('' != $item)
          {
            $data[sfInflector::underscore($name)] = $this->unserializeToArray($item, true);
          }
          else
          {
            $data[sfInflector::underscore($name)] = null;
          }
        }
        else
        {
          $data[$name] = $this->unserializeToArray($item, true);
        }
      }
    }

    return $data;
  }

  protected function arrayToXml($array, $rootNodeName = 'Data', $level = 0, $collection = true, $pluralRootNodeName = false)
  {
    $xml = '';

    if (0 == $level)
    {
      if ($pluralRootNodeName)
      {
        $xml .= '<?xml version="1.0" encoding="utf-8"?><'.$pluralRootNodeName.'>';
      }
      else
      {
        $plural = (true === $collection) ? 's' : '';
        $xml .= '<?xml version="1.0" encoding="utf-8"?><'.$rootNodeName.$plural.'>';
      }
    }

    foreach ($array as $key => $value)
    {
      if (is_numeric($key))
      {
        $key = $rootNodeName;
      }
      else
      {
        $key = $this->camelize($key);
      }

      if (is_array($value))
      {
        $real_key = $key;

        if (!count($value) || isset($value[0]))
        {
          $real_key .= 's';
        }

        $xml .= '<'.$real_key.'>';
        $xml .= $this->arrayToXml($value, $key, $level + 1);
        $xml .= '</'.$real_key.'>';
      }
      else
      {
        $trimed_value = ($value !== false) ? trim($value) : '0';

        if ($trimed_value !== '')
        {
          if (htmlspecialchars($trimed_value) != $trimed_value)
          {
            $xml .= '<'.$key.'><![CDATA['.$trimed_value.']]></'.$key.'>';
          }
          else
          {
            $xml .= '<'.$key.'>'.$trimed_value.'</'.$key.'>';
          }
        }
      }
    }

    if (0 == $level)
    {
      if ($pluralRootNodeName)
      {
        $xml .= '</'.$pluralRootNodeName.'>';
      }
      else
      {
        $xml .= '</'.$rootNodeName.$plural.'>';
      }
    }

    return $xml;
  }
}