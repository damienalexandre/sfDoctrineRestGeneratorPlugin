  /**
   * Applies the creation validators to the payload posted to the service
   *
   * @param   array   $params  A parsed payload array
   * @return  array   $params  A cleaned params array
   */
  public function validateCreate($params)
  {
    $validators = $this->getCreateValidators();
    $params = $this->validate($params, $validators);

    $postvalidators = $this->getCreatePostValidators();
    $this->postValidate($params, $postvalidators);

    return $params;
  }
