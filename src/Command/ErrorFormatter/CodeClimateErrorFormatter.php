<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Command\AnalysisResult;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;

class CodeClimateErrorFormatter implements ErrorFormatter
{

	public function formatErrors(AnalysisResult $analysisResult, OutputStyle $style): int
	{
		if (!$analysisResult->hasErrors()) {
			return 0;
		}

		foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
			$message = [
				'type' => 'issue',
				'check_name' => 'PHPStan',
				'description' => $fileSpecificError->getMessage(),
				'categories' => ['Bug Risk'],
				'location' => [
					'path' => RelativePathHelper::getRelativePath($analysisResult->getCurrentDirectory(), $fileSpecificError->getFile()),
					'lines' => [
						'begin' => $fileSpecificError->getLine(),
						'end' => $fileSpecificError->getLine(),
					],
				],
			];

			$style->write(json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\0", true, OutputInterface::OUTPUT_RAW);
		}

		return 0;
	}

}
