<?php

/**
 * fineuploader extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2014, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-fineuploader
 */

/**
 * Class FineUploaderWidget
 *
 * Provide methods to handle input field "fine uploader".
 */
class FineUploaderWidget extends FineUploaderBase
{

    /**
     * Submit user input
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'fineuploader_backend';

    /**
     * Show files
     * @var boolean
     */
    protected $blnIsDownloads = false;

    /**
     * Gallery flag
     * @var boolean
     */
    protected $blnIsGallery = false;

    /**
     * Multiple flag
     * @var boolean
     */
    protected $blnIsMultiple = false;

    /**
     * Load the database object
     * @param array
     */
    public function __construct($arrAttributes=null)
    {
        parent::__construct($arrAttributes);

        $this->blnIsMultiple = $this->arrConfiguration['multiple'];
        $this->blnIsGallery = $this->arrConfiguration['isGallery'];
        $this->blnIsDownloads = $this->arrConfiguration['isDownloads'];

        // Include the assets
        $GLOBALS['TL_JAVASCRIPT']['fineuploader'] = 'system/modules/fineuploader/assets/fineuploader/fineuploader-5.0.2.min.js';
        $GLOBALS['TL_JAVASCRIPT']['fineuploader_handler'] = 'system/modules/fineuploader/assets/handler.min.js';
        $GLOBALS['TL_CSS']['fineuploader_handler'] = 'system/modules/fineuploader/assets/handler.min.css';
    }

    /**
     * Generate the widget and return it as string
     * @param array
     * @return string
     */
    public function parse($arrAttributes=null)
    {
        $arrSet = array();
        $arrValues = array();

        if (!empty($this->varValue)) { // Can be an array
            $arrUuids = array();
            $arrTemp = array();
            $this->varValue = (array) $this->varValue;

            foreach ($this->varValue as $varFile) {
                if (\Validator::isBinaryUuid($varFile)) {
                    $arrUuids[] = $varFile;
                } else {
                    $arrTemp[] = $varFile;
                }
            }

            $objFiles = \FilesModel::findMultipleByUuids($arrUuids);

            // Get the database files
            if ($objFiles !== null) {
                while ($objFiles->next()) {
                    $chunk = $this->generateFileItem($objFiles->path);

                    if (strlen($chunk)) {
                        $arrValues[$objFiles->uuid] = array
                        (
                            'id' => (in_array($objFiles->uuid, $arrTemp) ? $objFiles->uuid : \String::binToUuid($objFiles->uuid)),
                            'value' => $chunk
                        );

                        $arrSet[] = $objFiles->uuid;
                    }
                }
            }

            // Get the temporary files
            foreach ($arrTemp as $varFile) {
                $chunk = $this->generateFileItem($varFile);

                if (strlen($chunk)) {
                    $arrValues[$varFile] = array
                    (
                        'id' => (in_array($varFile, $arrTemp) ? $varFile : \String::binToUuid($varFile)),
                        'value' => $chunk
                    );

                    $arrSet[] = $varFile;
                }
            }
        }

        // Load the fonts for the drag hint (see #4838)
        $GLOBALS['TL_CONFIG']['loadGoogleFonts'] = true;

        // Parse the set array
        foreach ($arrSet as $k=>$v) {
            if (in_array($v, $arrTemp)) {
                $strSet[$k] = $v;
            } else {
                $arrSet[$k] = \String::binToUuid($v);
            }
        }

        $this->set = implode(',', $arrSet);
        $this->sortable = count($arrValues) > 1;
        $this->orderHint = $GLOBALS['TL_LANG']['MSC']['dragItemsHint'];
        $this->values = $arrValues;
        $this->ajax = \Environment::get('isAjaxRequest');
        $this->deleteTitle = specialchars($GLOBALS['TL_LANG']['MSC']['delete']);
        $this->extensions = json_encode(trimsplit(',', $this->arrConfiguration['extensions']));
        $this->limit = $this->arrConfiguration['uploaderLimit'] ? $this->arrConfiguration['uploaderLimit'] : 0;
        $this->sizeLimit = $this->arrConfiguration['maxlength'] ? $this->arrConfiguration['maxlength'] : 0;
        $this->chunkSize = $this->arrConfiguration['chunkSize'] ? $this->arrConfiguration['chunkSize'] : 0;
        $this->config = $this->arrConfiguration['uploaderConfig'];

        $this->texts = json_encode(array
        (
            'text' => array
            (
                'formatProgress' => $GLOBALS['TL_LANG']['MSC']['fineuploader_formatProgress'],
                'failUpload' => $GLOBALS['TL_LANG']['MSC']['fineuploader_failUpload'],
                'waitingForResponse' => $GLOBALS['TL_LANG']['MSC']['fineuploader_waitingForResponse'],
                'paused' => $GLOBALS['TL_LANG']['MSC']['fineuploader_paused'],
            ),
            'messages' => array
            (
                'tooManyFilesError' => $GLOBALS['TL_LANG']['MSC']['fineuploader_tooManyFilesError'],
                'unsupportedBrowser' => $GLOBALS['TL_LANG']['MSC']['fineuploader_unsupportedBrowser'],
            ),
            'retry' => array
            (
                'autoRetryNote' => $GLOBALS['TL_LANG']['MSC']['fineuploader_autoRetryNote'],
            ),
            'deleteFile' => array
            (
                'confirmMessage' => $GLOBALS['TL_LANG']['MSC']['fineuploader_confirmMessage'],
                'deletingStatusText' => $GLOBALS['TL_LANG']['MSC']['fineuploader_deletingStatusText'],
                'deletingFailedText' => $GLOBALS['TL_LANG']['MSC']['fineuploader_deletingFailedText'],
            ),
            'paste' => array
            (
                'namePromptMessage' => $GLOBALS['TL_LANG']['MSC']['fineuploader_namePromptMessage'],
            ),
        ));

        $this->labels = array
        (
            'drop' => $GLOBALS['TL_LANG']['MSC']['fineuploader_drop'],
            'upload' => $GLOBALS['TL_LANG']['MSC']['fineuploader_upload'],
            'processing' => $GLOBALS['TL_LANG']['MSC']['fineuploader_processing'],
        );

        return parent::parse($arrAttributes);
    }

    /**
     * Use the parse() method instead
     * @throw \BadMethodCallException
     */
    public function generate()
    {
        throw new \BadMethodCallException('Please use the parse() method instead!');
    }
}
