<?php
set_error_handler("exception_error_handler");
class CsvParser
{
	const ACCEPTED_OUTPUT = [
		'csv',
		'json'
	];
	const REQUIRED_FIELDS = [
		'brand_name',
		'model_name'
	];

	private $headings = [];

	private $inputFileName;
	private $outputFileName;

	public function __construct()
	{
		$options = getopt("", [
			'file:',
			'unique-combinations:'
		]);

		$this->inputFileName = $options['file'] ?? null;
		$this->outputFileName = $options['unique-combinations'] ?? null;
	}

	public function __invoke()
	{
		try {
			echo "Parsing Started\n";
			$this->validate();
			$uniqueCombinations = $this->readProducts();
			$this->output($uniqueCombinations);
			echo "Parsing Completed. File saved to current directory\n";
		} catch (Exception $th) {
			echo 'Caught exception: ' . $th->getMessage();
			exit();
		}
		exit();
	}

	private function validate()
	{
		if (is_null($this->inputFileName)) {
			throw new Exception("--file:Required");
		}
		if (is_null($this->outputFileName)) {
			throw new Exception("--unique-combinations Required");
		}

		$ext = pathinfo($this->outputFileName, PATHINFO_EXTENSION);
		if (!in_array($ext, self::ACCEPTED_OUTPUT)) {
			throw new Exception("--unique-combinations: Accepted formats are " . implode(', ', self::ACCEPTED_OUTPUT));
		}
		if ($this->inputFileName == $this->outputFileName) {
			throw new Exception("--unique-combinations: File name can not be same as --file");
		}
	}

	private function readProducts()
	{
		try {
			$products = array_map('str_getcsv', file($this->inputFileName));

			$this->headings = $headings = array_shift($products);

			array_walk(
				$products,
				function (&$row) use ($headings) {
					$row = array_combine($headings, $row);
				}
			);

			$uniqueCombinations = [];

			foreach ($products as $product) {
				$key = implode('_', $product);
				if (isset($uniqueCombinations[$key])) {
					$uniqueCombinations[$key]['count'] += 1;
				} else {
					$product['count'] = 1;
					$uniqueCombinations[$key] = $product;
				}
			}

			return $uniqueCombinations;
		} catch (Exception $th) {
			echo 'Caught exception: ' . $th->getMessage();
			exit();
		}
	}

	private function output($uniqueCombinations)
	{
		try {
			$ext = pathinfo($this->outputFileName, PATHINFO_EXTENSION);
			switch ($ext) {
				case 'csv':
					$outputFile = fopen($this->outputFileName, 'w');
					$headings = $this->headings;
					array_push($headings, 'count');
					fputcsv($outputFile, $headings);

					foreach ($uniqueCombinations as $uniqueCombination) {
						fputcsv($outputFile, $uniqueCombination);
					}
					fclose($outputFile);
					break;
				case 'json':
					file_put_contents($this->outputFileName, json_encode(array_values($uniqueCombinations)));
					break;
				default:
					throw new Exception('--unique-combinations: Not a valid file format!');
			}
		} catch (Exception $th) {
			echo 'Caught exception: ' . $th->getMessage();
			exit();
		}
	}
}

function exception_error_handler($errno, $errstr, $errfile, $errline)
{
	throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}

(new CsvParser)();
