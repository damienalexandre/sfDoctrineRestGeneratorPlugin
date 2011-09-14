  protected function getSerializer()
  {
    if (!isset($this->serializer))
    {
      try
      {
        $this->serializer = sfResourceSerializer::getInstance($this->getFormat(), <?php echo $this->asPhp($this->configuration->getValue('default.camelize')) ?>);
      }
      catch (sfException $e)
      {
        $this->serializer = sfResourceSerializer::getInstance('<?php echo $this->configuration->getValue('get.default_format') ?>', <?php echo $this->asPhp($this->configuration->getValue('default.camelize')) ?>);
        throw new sfException($e->getMessage());
      }
    }

    return $this->serializer;
  }
