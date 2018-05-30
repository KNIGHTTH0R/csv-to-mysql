<?php

namespace Logikos\Csv\CsvToMysql;

class LoadDataInFile {

  private $delimiter   = ',';
  private $enclosure   = '"';
  private $eol         = "\\n";
  private $escape      = '\\';
  private $ignoreLines = 0;
  private $csvFile;
  private $tableName;

  public function getDelimiter()           { return $this->delimiter;  }
  public function setDelimiter($delimiter) { $this->delimiter = $delimiter;  }

  public function getEnclosure()           { return $this->enclosure;  }
  public function setEnclosure($enclosure) { $this->enclosure = $enclosure;  }

  public function getEOL()                 { return $this->eol; }
  public function setEOL($eol)             { $this->eol = $eol; }

  public function getEscape()              { return $this->escape;  }
  public function setEscape($escape)       { $this->escape = $escape;  }

  public function getTableName()           { return $this->tableName;  }
  public function setTableName($name)      { $this->tableName = $name;  }

  public function getIgnoreLines()         { return $this->ignoreLines; }
  public function setIgnoreLines($count)   { $this->ignoreLines = $count; }

  public function getCsvFile()             { return $this->csvFile; }
  public function setCsvFile($csvFile)     {
    if (!is_writable($csvFile)) throw new Exception("Could not read file {$csvFile}");
    $this->csvFile = $csvFile;
  }

  public function getQuery() {
    $sql  = "
      LOAD DATA LOCAL INFILE {$this->getCsvFile()}
      INTO TABLE {$this->getTableName()}
      FIELDS TERMINATED BY '{$this->getDelimiter()}'
      OPTIONALLY ENCLOSED BY '{$this->getEnclosure()}'
      ESCAPED BY '{$this->escape($this->getEscape())}'
      LINES TERMINATED BY '{$this->getEOL()}'
      IGNORE 2 LINES
    ";
    return $sql;
  }

  private function escape($string) {
    return addslashes($string);
  }
}