<?php

namespace Logikos\Csv\CsvToMysql;

class LoadDataInFile {

  private $delimiter   = ',';
  private $enclosure   = '"';
  private $escape      = '\\';
  private $ignoreLines = 0;
  private $csvFile;

  public function getDelimiter()           { return $this->delimiter;  }
  public function setDelimiter($delimiter) { $this->delimiter = $delimiter;  }

  public function getEnclosure()           { return $this->enclosure;  }
  public function setEnclosure($enclosure) { $this->enclosure = $enclosure;  }

  public function getEscape()              { return $this->escape;  }
  public function setEscape($escape)       { $this->escape = $escape;  }

  public function getIgnoreLines()         { return $this->ignoreLines; }
  public function setIgnoreLines($count)   { $this->ignoreLines = $count; }

  public function getCsvFile()             { return $this->csvFile; }
  public function setCsvFile($csvFile)     {
    if (!is_writable($csvFile)) throw new Exception("Could not read file {$csvFile}");
    $this->csvFile = $csvFile;
  }
}