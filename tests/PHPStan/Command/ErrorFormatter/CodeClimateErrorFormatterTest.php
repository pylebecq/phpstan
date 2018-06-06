<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorsConsoleStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CodeClimateErrorFormatterTest extends \PHPStan\Testing\TestCase
{

	private const DIRECTORY_PATH = '/data/folder/with space/and unicode 😃/project';

	/** @var CodeClimateErrorFormatter */
	protected $formatter;

	protected function setUp(): void
	{
		$this->formatter = new CodeClimateErrorFormatter();
	}

	public function testFormatErrors(): void
	{
		$analysisResult = new AnalysisResult(
			[
				new Error('Foo', self::DIRECTORY_PATH . '/foo.php', 1),
				new Error('Bar', self::DIRECTORY_PATH . '/foo.php', 5),
				new Error('Bar', self::DIRECTORY_PATH . '/file name with "spaces" and unicode 😃.php', 2),
				new Error('Foo', self::DIRECTORY_PATH . '/file name with "spaces" and unicode 😃.php', 4),
			],
			[],
			false,
			self::DIRECTORY_PATH
		);

		$outputStream = new BufferedOutput();
		$style = new ErrorsConsoleStyle(new StringInput(''), $outputStream);

		$this->assertSame(0, $this->formatter->formatErrors($analysisResult, $style));

		$output = $outputStream->fetch();

		$expected = '{"type":"issue","check_name":"PHPStan","description":"Bar","categories":["Bug Risk"],"location":{"path":"file name with \"spaces\" and unicode 😃.php","lines":{"begin":2,"end":2}}}' . "\x00" . '
{"type":"issue","check_name":"PHPStan","description":"Foo","categories":["Bug Risk"],"location":{"path":"file name with \"spaces\" and unicode 😃.php","lines":{"begin":4,"end":4}}}' . "\x00" . '
{"type":"issue","check_name":"PHPStan","description":"Foo","categories":["Bug Risk"],"location":{"path":"foo.php","lines":{"begin":1,"end":1}}}' . "\x00" . '
{"type":"issue","check_name":"PHPStan","description":"Bar","categories":["Bug Risk"],"location":{"path":"foo.php","lines":{"begin":5,"end":5}}}' . "\x00" . '
';
		$this->assertSame($expected, $output);
	}

	public function testFormatErrorsEmpty(): void
	{
		$analysisResult = new AnalysisResult([], [], false, self::DIRECTORY_PATH);

		$outputStream = new BufferedOutput();
		$style = new ErrorsConsoleStyle(new StringInput(''), $outputStream);

		$this->assertSame(0, $this->formatter->formatErrors($analysisResult, $style));

		$output = $outputStream->fetch();

		$expected = '';
		$this->assertSame($expected, $output);
	}

}
