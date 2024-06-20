<?php


namespace Helpers;

use App\Helpers\LanguageHelper;
use PHPUnit\Framework\TestCase;

class LanguageHelperTest extends TestCase
{
    public function testConvertMXToES(){
        $lang = "MX";

        $sut = new LanguageHelper();
        $result = $sut->Normalize($lang);

        $this->assertEquals("es", $result);
    }

    public function testConvertESToES(){
        $lang = "ES";

        $sut = new LanguageHelper();
        $result = $sut->Normalize($lang);

        $this->assertEquals("es", $result);
    }

    public function testConvertENToEN(){
        $lang = "EN";

        $sut = new LanguageHelper();
        $result = $sut->Normalize($lang);

        $this->assertEquals("en", $result);
    }

    public function testConvertUSToEN(){
        $lang = "US";

        $sut = new LanguageHelper();
        $result = $sut->Normalize($lang);

        $this->assertEquals("en", $result);
    }

    public function testConvertEmptyToEN(){
        $lang = "";

        $sut = new LanguageHelper();
        $result = $sut->Normalize($lang);

        $this->assertEquals("en", $result);
    }

    public function testConvertSpaceToEN(){
        $lang = " ";

        $sut = new LanguageHelper();
        $result = $sut->Normalize($lang);

        $this->assertEquals("en", $result);
    }
}