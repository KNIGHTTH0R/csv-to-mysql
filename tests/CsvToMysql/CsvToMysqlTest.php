<?php

namespace LogikosTest\Csv\CsvToMysql;

use Logikos\Csv\CsvToMysql\LoadDataInFile;

class CsvToMysqlTest extends TestCase {
  const DB_NAME_AND_TABLE = 'staging.people';

  public function testLoadDataInFile() {
    $this->assertInstanceOf(LoadDataInFile::class, new LoadDataInFile());
  }

  /** @dataProvider delimiters */
  public function testSetAndGetDelimiter($delimiter) {
    $ld = new LoadDataInFile();
    $ld->setDelimiter($delimiter);
    $this->assertSame($delimiter, $ld->getDelimiter());
  }
  public function delimiters() { return [[','], ["\t"], ['|']]; }

  /** @dataProvider enclosures */
  public function testSetAndGetEnclosure($enclosure) {
    $ld = new LoadDataInFile();
    $ld->setEnclosure($enclosure);
    $this->assertSame($enclosure, $ld->getEnclosure());
  }
  public function enclosures() { return [['"'], ['\''], ['`']]; }

  /** @dataProvider escapes */
  public function testSetAndGetEscape($escape) {
    $ld = new LoadDataInFile();
    $ld->setEscape($escape);
    $this->assertSame($escape, $ld->getEscape());
  }
  public function escapes() { return [['\\'], ['`']]; }

  /** @dataProvider ignoreLines */
  public function testSetAndGetIgnoreLines($lines) {
    $ld = new LoadDataInFile();
    $ld->setIgnoreLines($lines);
    $this->assertSame($lines, $ld->getIgnoreLines());
  }
  public function ignoreLines() { return [[0], [1], [2]]; }

  public function testDefaults() {
    $ld = new LoadDataInFile();
    $this->assertSame(',',  $ld->getDelimiter());
    $this->assertSame('"',  $ld->getEnclosure());
    $this->assertSame('\\', $ld->getEscape());
    $this->assertSame(0,    $ld->getIgnoreLines());
  }

  public function testSetAndGetCsvFile() {
    $csvFile = $this->csvFile();
    $ld = new LoadDataInFile();
    $ld->setCsvFile($csvFile);
    $this->assertSame($csvFile, $ld->getCsvFile());
  }

  public function testCsvFileMustBeReadable() {
    $csvFile = realpath(__DIR__.'/this-file-does-not-exist.csv');
    $this->expectException(\Exception::class);
    $ld = new LoadDataInFile();
    $ld->setCsvFile($csvFile);
  }

  public function testSQL() {
    $file = $this->csvFile();
    $sql  = "
      LOAD DATA LOCAL INFILE {$file}
      INTO TABLE ".self::DB_NAME_AND_TABLE."
      FIELDS TERMINATED BY ','
      OPTIONALLY ENCLOSED BY '\"'
      ESCAPED BY '\\\\'
      LINES TERMINATED BY '\\n'
      IGNORE 2 LINES
    ";
    $ld = new LoadDataInFile();
    $ld->setIgnoreLines(2);
    $this->assertSame(
        $this->normalizeWhitespace($sql),
        $this->normalizeWhitespace($ld->getQuery())
    );
  }

  protected function normalizeWhitespace($string) {
    return trim(
        $this->forceSingleSpaces(
            $this->forceSingleLine($string)
        )
    );
  }
  protected function forceSingleSpaces($string) {
    return preg_replace('/ {2,}/', ' ', $string);
  }
  protected function forceSingleLine($string) {
    return str_replace(PHP_EOL, ' ', $string);
  }

  protected function csvFile() {
    $csvFile = $this->buildCsvFromArray([
        ['name', 'age'],
        ['adam', 30],
        ['bob',  31],
        ['cody', 32]
    ]);
    return $csvFile;
  }

}