  /**
   * Applies a set of validators to an array of parameters
   * The cleaned value replace
   *
   * @param array   $params      An array of parameters
   * @param array   $validators  An array of validators
   * @throw sfException
   */
  public function validate($params, $validators, $prefix = '')
  {
    if ($params === null && is_array($validators))
    {
      // The case of an empty collection
      return null;
    }
    
    $unused = array_keys($validators);

    foreach ($params as $name => $value)
    {
      if (!isset($validators[$name]))
      {
        throw new sfException(sprintf('Could not validate extra field "%s"', $prefix.$name));
      }
      else
      {
        if (is_array($validators[$name]))
        {
          if (is_array($value) && isset($value[0]))
          {
            // We are on a list of array, not a related object
            foreach ($value as $key => $val)
            {
              $params[$name][$key] = $this->validate($val, $validators[$name], $prefix.$name.'.');
            }
          }
          else
          {
            // validator for a related object
            $params[$name] = $this->validate($value, $validators[$name], $prefix.$name.'.');
          }
        }
        elseif (is_array($value) && isset($value[0]) && $validators[$name] instanceof sfValidatorBase)
        {
          // We are on a list of value
          foreach ($value as $key => $val)
          {
            $params[$name][$key] = $validators[$name]->clean($val);
          }
        }
        else
        {
          $params[$name] = $validators[$name]->clean($value);
        }

        unset($unused[array_search($name, $unused, true)]);
      }
    }

    // are non given values required?
    foreach ($unused as $name)
    {
      try
      {
        if (!is_array($validators[$name]))
        {
          $validators[$name]->clean(null);
        }
      }
      catch (Exception $e)
      {
        throw new sfException(sprintf('Could not validate field "%s": %s', $prefix.$name, $e->getMessage()));
      }
    }

    return $params;
  }