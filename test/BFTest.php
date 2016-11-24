<?php
namespace Domoran\PHPOok\Test;

use Domoran\PHPOok\BF;

class BFTest extends \PHPUnit_Framework_TestCase {
    public function testHelloWorld () {
        $bf = new BF ("++++++++++[>+++++++>++++++++++>+++>+<<<<-]>++.>+.+++++++..+++.>++.<<+++++++++++++++.>.+++.------.--------.>+.>.");
        
        $output = $bf->execute(null); 
        $this->assertEquals("Hello World!\n", $output); 
    }

    public function testHelloWorld2 () {
        $bf = new BF (">++++++++[-<+++++++++>]<.>>+>-[+]++>++>+++[>[->+++<<+++>]<<]>-----.>->+++..+++.>-.<<+[>[+>+]>>]<--------------.>>.+++.------.--------.>+.>+.");
        
        $output = $bf->execute(null); 
        $this->assertEquals("Hello World!\n", $output); 
    }
    
    public function testRot13 () {
        $bf = new BF (",[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>++++++++++++++<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>>+++++[<----->-]<<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>++++++++++++++<-[>+<-[>+<-[>+<-[>+<-[>+<-[>++++++++++++++<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>>+++++[<----->-]<<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>+<-[>++++++++++++++<-[>+<-]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]>.[-]<,]");
        $output = $bf->execute("Hallo");
        $this->assertEquals("Unyyb", $bf->getOutput());
    }
    
    public function testTrace () {
        $bf = new BF("+.");
        $bf->execute(null); 
        $strm = fopen("php://temp", 'wt'); 
        $bf->formatLog($strm);
        rewind($strm); 
        $data = stream_get_contents($strm);
        $lines = explode("\n", trim($data));
        $this->assertEquals(3, count($lines)); 
        fclose($strm); 
    }
    
    public function testRunLimitOk () {
        $bf = new BF("+[+]", 1, 512);
        $bf->execute(null);
    }

    /**
     * @expectedException \Exception
     */
    public function testRunLimitFail () {
        $bf = new BF("+[+]", 1, 511);
        $bf->execute(null);
        $this->fail("Should never happen!"); 
    }
    
    public function testMemoryLimitOk () {
        $bf = new BF(">", 1, 5);
        $bf->execute(null);
    }

    /**
     * @expectedException \Exception 
     */
    public function testMemoryLimitFail () {
        $bf = new BF(">>", 1, 5);
        $bf->execute(null);
    }
    
    
}