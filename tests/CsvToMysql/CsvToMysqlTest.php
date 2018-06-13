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
    $this->assertSame(',',   $ld->getDelimiter());
    $this->assertSame('"',   $ld->getEnclosure());
    $this->assertSame('\\',  $ld->getEscape());
    $this->assertSame('\\n', $ld->getEOL());
    $this->assertSame(0,     $ld->getIgnoreLines());
  }

  public function testSetAndGetTableName() {
    $ld = new LoadDataInFile();
    $ld->setTableName(self::DB_NAME_AND_TABLE);
    $this->assertSame(self::DB_NAME_AND_TABLE, $ld->getTableName());
  }

  public function testSetAndGetEOL() {
    $ld = new LoadDataInFile();
    $ld->setEOL(";");
    $this->assertSame(";", $ld->getEOL());
  }

  public function testSetAndGetCsvFile() {
    $csvFile = $this->csvFile();
    $ld = new LoadDataInFile();
    $ld->setCsvFile($csvFile);
    $this->assertSame($csvFile, $ld->getCsvFile());
  }

  public function testColumnMap() {
    $ld = new LoadDataInFile();
    $map = ['firstname', 'lastname', null, null, 'age'];
    $ld->setColMap($map);
    $expected = 'firstname = @col1, lastname = @col2, age = @col5';
    $this->assertEquals(
        $this->normalizeWhitespace($expected),
        $this->normalizeWhitespace($ld->getSetExpression())
    );
    $this->assertEquals(
        ['@col1', '@col2', '@col3', '@col4', '@col5'],
        $ld->getColVars()
    );
    $this->assertEquals($map, $ld->getColMap());
  }

  public function testStaticColumns() {
    $ld = new LoadDataInFile();
    $binds = [
        'request_id' => 23,
        'cdate' => '2018-06-12'
    ];
    $ld->SetColumnBinds($binds);
    $this->assertSame($binds, $ld->getColumnBinds());
    $this->assertEquals(
        $this->normalizeWhitespace("request_id = :request_id, cdate = :cdate"),
        $this->normalizeWhitespace($ld->getSetExpression())
    );
    $this->assertEquals(
        [':request_id'=>23, ':cdate'=>'2018-06-12'],
        $ld->getBinds()
    );
  }

  public function testGetSetExpressionIncludesColMapAndColBinds() {
    $ld = new LoadDataInFile();
    $ld->setColMap(['firstname']);
    $ld->setColumnBinds(['request_id'=>23]);
    $expected = "firstname = @col1, request_id = :request_id";
    $this->assertEquals(
        $this->normalizeWhitespace($expected),
        $this->normalizeWhitespace($ld->getSetExpression())
    );
  }

  public function testSQL() {
    $file = $this->csvFile();

    $ld = new LoadDataInFile();
    $ld->setCsvFile($file);
    $ld->setTableName(self::DB_NAME_AND_TABLE);
    $ld->setIgnoreLines(3);
    $ld->setColMap([
        'firstname',
        'lastname',
        null,
        null,
        'age'
    ]);
    $ld->setColumnBinds([
        'request_id'=>23, 'cdate' => '2018-06-12'
    ]);

    $expectedSql  = "
      LOAD DATA LOCAL INFILE '{$file}'
      INTO TABLE ".self::DB_NAME_AND_TABLE."
      FIELDS TERMINATED BY ','
      OPTIONALLY ENCLOSED BY '\"'
      ESCAPED BY '\\\\'
      LINES TERMINATED BY '\\n'
      IGNORE 3 LINES
      ( @col1, @col2, @col3, @col4, @col5 )
      SET firstname = @col1, lastname = @col2, age = @col5,
      request_id = :request_id, cdate = :cdate
    ";

    $this->assertSame(
        $this->normalizeWhitespace($expectedSql),
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