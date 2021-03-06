<?php

namespace Spyrit\LightCsv\Tests\Csv;

use Spyrit\LightCsv\Tests\Test\AbstractCsvTestCase;
use Spyrit\LightCsv\CsvWriter;

/**
 * CsvWriterTest
 *
 * @author Charles SANQUER - Spyrit Systemes <charles.sanquer@spyrit.net>
 */
class CsvWriterTest extends AbstractCsvTestCase
{
    /**
     *
     * @var Spyrit\LightCsv\CsvWriter
     */
    protected $writer;

    protected function setUp()
    {
        $this->writer = new CsvWriter();
    }

    public function testConstruct()
    {
        $this->assertEquals('wb', $this->getFileHandlerModeValue($this->writer));
        $this->assertEquals(';', $this->writer->getDelimiter());
        $this->assertEquals('"', $this->writer->getEnclosure());
        $this->assertEquals('CP1252', $this->writer->getEncoding());
        $this->assertEquals("\r\n", $this->writer->getLineEndings());
        $this->assertEquals("\\", $this->writer->getEscape());
        $this->assertEquals(false, $this->writer->getUseBom());
    }

    /**
     * @dataProvider providerWritingRow
     */
    public function testWritingRow($options, $filename, $row, $expectedCsv)
    {
        if (file_exists($filename)) {
            unlink($filename);
        }

        $this->writer = new CsvWriter($options[0], $options[1], $options[2], $options[3]);

        $this->assertInstanceOf('Spyrit\LightCsv\CsvWriter',$this->writer->open($filename));
        $this->assertInstanceOf('Spyrit\LightCsv\CsvWriter',$this->writer->writeRow($row));
        $this->assertEquals($expectedCsv, file_get_contents($filename));
        $this->assertInstanceOf('Spyrit\LightCsv\CsvWriter',$this->writer->close());

        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    public function providerWritingRow()
    {
        return array(
            array(
                array(',','"', 'UTF-8', "\n"),
                __DIR__.'/../Fixtures/testWrite.csv',
                array('Martin','Durand','28'),
                '"Martin","Durand","28"'."\n",
            ),
            array(
                array(';','"', 'CP1252', "r\n"),
                __DIR__.'/../Fixtures/testWrite2.csv',
                array('Gauthier','Aurélie','24'),
                mb_convert_encoding('"Gauthier";"Aurélie";"24"'."\r\n", 'CP1252', 'UTF-8'),
            ),
        );
    }

    /**
     * @dataProvider providerWritingRows
     */
    public function testWritingRows($options, $filename, $rows, $expectedCsv)
    {
        if (file_exists($filename)) {
            unlink($filename);
        }

        $this->writer = new CsvWriter($options[0], $options[1], $options[2], $options[3]);

        $this->assertInstanceOf('Spyrit\LightCsv\CsvWriter',$this->writer->setFilename($filename));
        $this->assertInstanceOf('Spyrit\LightCsv\CsvWriter',$this->writer->writeRows($rows));
        $this->assertEquals($expectedCsv, file_get_contents($filename));
        $this->assertInstanceOf('Spyrit\LightCsv\CsvWriter',$this->writer->close());

        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    public function providerWritingRows()
    {
        return array(
            array(
                array(',','"', 'UTF-8', "\n"),
                __DIR__.'/../Fixtures/testWrite.csv',
                array(
                    array('nom','prénom','age'),
                    array('Martin','Durand','28'),
                    array('Alain','Richard','36'),
                ),
                '"nom","prénom","age"'."\n".'"Martin","Durand","28"'."\n".'"Alain","Richard","36"'."\n",
            ),
        );
    }

    public function testGetHttpHeaders()
    {
        $this->assertEquals(array(
            'Content-Type' => 'application/csv',
            'Content-Disposition' => 'attachment;filename="test.csv"',
        ), $this->writer->getHttpHeaders('test.csv'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWritingLineNoFilename()
    {
        $this->writer->writeRow(array('nom','prénom','age'));
    }

    /**
     * @dataProvider providerWritingBom
     */
    public function testWritingBom($options, $filename, $expectedCsv)
    {
        if (file_exists($filename)) {
            unlink($filename);
        }

        $this->writer = new CsvWriter(',','"', $options[0], "\n", "\\",$options[1]);

        $this->assertInstanceOf('Spyrit\LightCsv\CsvWriter',$this->writer->open($filename));
        $this->assertEquals($expectedCsv, file_get_contents($filename));
        $this->assertInstanceOf('Spyrit\LightCsv\CsvWriter',$this->writer->close());

        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    public function providerWritingBom()
    {
        return array(
            array(
                array('UTF-8', true),
                __DIR__.'/../Fixtures/testWrite.csv',
                "\xEF\xBB\xBF",
            ),
            array(
                array('UTF-8', false),
                __DIR__.'/../Fixtures/testWrite.csv',
                '',
            ),
            array(
                array('CP1252', true),
                __DIR__.'/../Fixtures/testWrite.csv',
                '',
            ),
        );
    }

    /**
     * @dataProvider providerGetSetUseBom
     */
    public function testGetSetUseBom($input,$expected)
    {
        $this->assertInstanceOf('Spyrit\LightCsv\AbstractCsv',$this->writer->setUseBom($input));
        $this->assertEquals($expected,$this->writer->getUseBom());
    }

    public function providerGetSetUseBom()
    {
        return array(
            array(null,false),
            array(0,false),
            array('',false),
            array(false,false),
            array(1,true),
            array(true,true),
        );
    }
}
