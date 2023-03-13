<?php

namespace App\Custom\Dhl24;

use App\Custom\Dhl24\Exceptions\DHL24Exception;

class Utils
{
    /**
     * Save labels to folder
     *
     * @param array $labels Array of ItemToPrintResponse
     * @param string $labelsFolder Server folder path
     *
     * @throws DHL24Exception
     *
     * @return array Array of labels name
     */
    public static function saveLabels(array $labels, string $labelsFolder): array
    {
        if (!isset($labels[0])) {
            return self::saveLabel($labels, $labelsFolder);
        }

        $files = [];

        foreach ($labels as $label) {
            $files = \array_merge($files , self::saveLabel($label, $labelsFolder));
        }

        return $files;
    }

    /**
     * Save label to folder
     *
     * @param array $labels ItemToPrintResponse
     * @param string $labelsFolder Server folder path
     *
     * @throws DHL24Exception
     *
     * @return array Saved files name
     */
    public static function saveLabel(array $label, string $labelsFolder): array
    {
        if (\file_exists($labelsFolder) === false) {
            throw new DHL24Exception('Folder does not exist');
        }

        $files = [];

        if(!empty($label['labelData'])){
            
            $fileName = self::labelFileName($label['labelType'], $label['shipmentId'], $label['labelMimeType'], 'label');
            
            if(self::saveFile($labelsFolder . $fileName, $label['labelData']) !== false){
                $files[] = $fileName;
            }
        }

        if(!empty($label['cn23Content'])){
            
            $fileName = self::labelFileName($label['labelType'], $label['shipmentId'], $label['cn23MimeType'], 'cn23');
            
            if(self::saveFile($labelsFolder . $fileName, $label['cn23Content']) !== false){
                $files[] = $fileName;
            }
        }

        if(!empty($label['fvProformaData'])){
            
            $fileName = self::labelFileName($label['labelType'], $label['shipmentId'], $label['fvProformaMimeType'], 'fvProforma');
            
            if(self::saveFile($labelsFolder . $fileName, $label['fvProformaData']) !== false){
                $files[] = $fileName;
            }
        }

        return $files;
    }

    /**
     * Save file to folder
     *
     * @param string $name
     * @param string $data
     *
     * @return bool 
    */
    public static function saveFile(string $name, string $data): bool
    {
        return \file_put_contents($name, \base64_decode($data));
    }

    /**
     * Configure label file name
     *
     * @param string $labelType
     * @param string $shipmentId
     * @param string $mimeType
     * @param string $fileType
     *
     * @return string
     */
    public static function labelFileName(string $labelType, string $shipmentId, string $mimeType, string $fileType): string
    {
        $fileName = $labelType . $shipmentId . $fileType;



        switch ($mimeType) {
            case 'application/pdf':
                $extention = 'pdf';
                break;

            default:
                $extention = 'pdf';
                break;
        }

        return 'label'.'.'.$extention;
        // return $fileName . '.' . $extention;
    }

    /**
     * Remove all symbols except numbers from string 
     *
     * @param string $text
     *
     * @return string 
    */
    public static function onlyNumbers(string $text): string
    {
        return \trim(\preg_replace('/[^0-9]/', '', $text));
    }
}
