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
  private $columnMap;
  private $columnBinds = [];

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

  public function getColMap()              { return $this->columnMap; }
  public function setColMap($columnMap)    { $this->columnMap = $columnMap; }

  public function getColumnBinds()         { return $this->columnBinds; }
  public function SetColumnBinds($binds)   { $this->columnBinds = $binds; }

  public function getCsvFile()             { return $this->csvFile; }
  public function setCsvFile($csvFile)     { $this->csvFile = $csvFile; }

  public function getQuery() {
    $sql  = "
      LOAD DATA LOCAL INFILE '{$this->getCsvFile()}'
      INTO TABLE {$this->getTableName()}
      FIELDS TERMINATED BY '{$this->getDelimiter()}'
      OPTIONALLY ENCLOSED BY '{$this->getEnclosure()}'
      ESCAPED BY '{$this->escape($this->getEscape())}'
      LINES TERMINATED BY '{$this->getEOL()}'
      IGNORE {$this->getIgnoreLines()} LINES
      ( {$this->getColVarsExpression()} )
      SET {$this->getSetExpression()}
    ";
    return $sql;
  }

  public function getBinds() {
    $binds = [];
    foreach ($this->columnBinds as $k=>$v) $binds[":{$k}"] = $v;
    return $binds;
  }

  public function getColVars() {
    for ($vars = [], $i=1; $i<=count($this->columnMap); $i++)
      array_push($vars, "@col{$i}");
    return $vars;
  }

  public function getSetExpression() {
    $expressions = [];
    if ($this->columnMap) array_push($expressions, $this->getColMapExpression());
    if ($this->columnBinds) array_push($expressions, $this->getBoundSetExpression());
    return $this->arrayToStringList($expressions);
  }

  protected function getColMapExpression() {
    $pieces = [];
    $colNum = 1;
    foreach ($this->columnMap as $colName) {
      if (!is_null($colName))
        array_push($pieces, "{$colName} = @col{$colNum}");
      $colNum++;
    }
    return implode(",\n", $pieces);
  }

  protected function getColVarsExpression() {
    return $this->arrayToStringList($this->getColVars());
  }

  protected function getBoundSetExpression() {
    $binds = [];
    foreach ($this->columnBinds as $k=>$v) {
      array_push($binds, "{$k} = :{$k}");
    }
    return $this->arrayToStringList($binds);
  }

  protected function arrayToStringList(array $items) {
    return implode(",\n", $items);
  }

  private function escape($string)      { return addslashes($string); }
}