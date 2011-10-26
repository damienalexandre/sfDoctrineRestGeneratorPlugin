  /**
   * Applies the update validators to the payload posted to the service
   *
   * @param   array   $params  A parsed payload array
   * @return  array   $params  A cleaned params array
   */
  public function validateUpdate($params)
  {
    $validators = $this->getUpdateValidators();
    $params = $this->validate($params, $validators);

    $postvalidators = $this->getUpdatePostValidators();
    $this->postValidate($params, $postvalidators);

    return $params;
  }
