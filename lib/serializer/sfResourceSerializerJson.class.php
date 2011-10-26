<?php
/**
 * Custom Json Serializer for sfDoctrineRestGeneratorPlugin
 *
 * @author dalexandre
 */
class sfResourceSerializerJson extends sfResourceSerializer
{
  public function getContentType()
  {
    return 'application/json';
  }

  public function serialize($array, $rootNodeName = 'data', $collection = true, $pluralRootNodeName = false)
  {
    if ($collection)
    {
      foreach ($array as $key => $result)
      {
        $array[$key] = array($rootNodeName => $result);
      }

      if ($pluralRootNodeName)
      {
        $array = array($pluralRootNodeName => $this->pluralizeCollections($array));
      }
      else
      {
        $array = array($rootNodeName.'s' => $this->pluralizeCollections($array));
      }
    }
    else
    {
      $array = array($rootNodeName => $this->pluralizeCollections($array));
    }

    return json_encode($array);
  }

  /**
   * Mimic the XML behavior for Json serialization
   *
   * @param array $array
   * @return array
   */
  public function pluralizeCollections($array)
  {
    foreach ($array as $key => $nodes)
    {
      if (is_array($nodes) && isset($nodes[0]))
      {
        foreach ($nodes as $noderesult)
        {
          $array[$key.'s'][] = array($key => $noderesult);
        }
        unset($array[$key]);
      }
      elseif (is_array($nodes))
      {
        $array[$key] = $this->pluralizeCollections($nodes);
      }
    }
    return $array;
  }

  public function unserialize($payload)
  {
    $array = json_decode($payload, true);
    if ($array)
    {
      $array = array_shift($array);
    }

    $array = $this->unPluralizeCollections($array);

    return $array;
  }

  /**
   * Deal with collections
   * 
   * @param array $array
   * @return array
   */
  public function unPluralizeCollections($array)
  {
    foreach ($array as $key => $nodes)
    {
      if (is_array($nodes) && isset($nodes[0]))
      {
        $group_name = key($nodes[0]);

        foreach ($nodes as $nodekey => $noderesult)
        {
          unset($array[$key][$nodekey]);
          $array[$key][$group_name][] = $noderesult[$group_name];
        }
      }
      elseif (is_array($nodes))
      {
        $array[$key] = $this->unPluralizeCollections($nodes);
      }
    }
    return $array;
  }
}