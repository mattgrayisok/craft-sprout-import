<?php

namespace Craft;

class SproutImport_GenerateController extends BaseController
{
	public function actionGenerateRedirectJson()
	{
		$pastedCSV = craft()->request->getPost('pastedCSV');

		$importableJson = $this->convertToJson($pastedCSV);

		if (!empty($importableJson))
		{
			craft()->userSession->setNotice(Craft::t('Redirect JSON generated.'));

			craft()->urlManager->setRouteVariables(array(
				'importableJson' => $importableJson
			));
		}
		else
		{
			craft()->userSession->setError(Craft::t('Unable to convert data.'));

			craft()->urlManager->setRouteVariables(array(
				'errors' => array(
					0 => Craft::t("CSV data not provided or using incorrect format.")
				),
				'pastedCSV' => $pastedCSV
			));
		}
	}

	private function convertToJson($csv)
	{
		$json = '';

		$array = array_map("str_getcsv", explode("\n", trim($csv)));

		if (is_array($array))
		{
			$first = $array[0];
			$first = array_map('trim', $first);

			if ($this->isHeader($first) === true)
			{
				array_shift($array);
			}
		}

		$sproutSeoImportJson = array();

		foreach ($array as $key => $attributes)
		{
			$attributes = array_map('trim', $attributes);

			if (count($attributes) == 4)
			{
				$sproutSeoImportJson[$key]['@model'] = "SproutSeo_RedirectModel";
				$sproutSeoImportJson[$key]['attributes'] = array(
					"oldUrl" => $attributes[0],
					"newUrl" => $attributes[1],
					"method" => $attributes[2],
					"regex"  => $attributes[3]
				);
			}
		}

		if (!empty($sproutSeoImportJson))
		{
			$json = json_encode($sproutSeoImportJson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
		}

		return $json;
	}

	private function isHeader($header)
	{
		$result = false;

		if (count($header) != 4)
		{
			return false;
		}

		if (
			$header[0] == 'oldUrl' ||
			$header[1] == 'newUrl' ||
			$header[2] == 'method' ||
			$header[3] == 'regex'
		)
		{
			$result = true;
		}

		return $result;
	}
}