  /**
   * Applies the get validators to the constraint parameters passed to the
   * webservice
   *
   * @param   array   $params  An array of criterions used for the selection
   * @return  array   A cleaned array of criterions
   */
  public function validateIndex($params)
  {
    $validators = $this->getIndexValidators();
    $params = $this->validate($params, $validators);

    $postvalidators = $this->getIndexPostValidators();
    $this->postValidate($params, $postvalidators);

    return $params;
  }
