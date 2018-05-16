<?php

namespace LogikosTest\Csv\CsvToMysql;

class TestCase extends \PHPUnit\Framework\TestCase {

  protected function buildCsvFromArray($data) {
    $tmpfname = tempnam(sys_get_temp_dir(), 'csvimport-');
    $handle = fopen($tmpfname, 'w');
    foreach ($data as $fields) fputcsv($handle, $fields);
    fclose($handle);
    return $tmpfname;
  }
}