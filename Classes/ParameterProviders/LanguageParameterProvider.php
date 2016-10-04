<?php
namespace MIA3\Mia3Search\ParameterProviders;

/*
 * This file is part of the mia3/mia3_search package.
 *
 * (c) Marc Neuhaus <marc@mia3.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Class LanguageParameterProvider
 * @package MIA3\Mia3Search\ParameterProviders
 */
class LanguageParameterProvider implements ParameterProviderInterface
{
    /**
     * @var DatabaseConnection
     */
    protected $database;

    public function __construct()
    {
        $this->database = $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return integer
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * @param array $parameterGroups
     * @return array
     */
    public function extendParameterGroups($parameterGroups)
    {
        $newParameterGroups = array();

        foreach ($parameterGroups as $key => $parameterGroup) {
            if (!isset($parameterGroup['L'])) {
                $parameterGroup['L'] = 0;
            }
            // readd default language group
            $newParameterGroups[] = $parameterGroup;

            // add groups if translations are present
            $languages = $this->getPageLanguages($parameterGroup['id']);
            if (is_array($languages)) {
                foreach ($languages as $language) {
                    $parameterGroup['L'] = $language['sys_language_uid'];
                    $parameterGroup['pageTitle'] = $language['title'];
                    $newParameterGroups[] = $parameterGroup;
                }
            }
        }

        return $newParameterGroups;
    }

    /**
     * Get all languages available on a specific page
     *
     * @param integer $pageUid
     * @return array
     */
    public function getPageLanguages($pageUid)
    {
        return $this->database->exec_SELECTgetRows(
            'sys_language_uid, title',
            'pages_language_overlay',
            'pid = ' . $pageUid . BackendUtility::BEenableFields('pages_language_overlay'),
            '',
            '',
            '',
            'sys_language_uid'
        );
    }
}