<?php
return function($params){
extract($params);

$modelVar = '$'.$modelVarName;
$modelInstance = $modelName.' '.$modelVar;
$filtName = $modelVarName.'ImagesFilt';

return '
    public function loadImages($request, '.$modelInstance.')
    {
        $oldImgIDs = $request->input(\'altImages\');

        $filters = config(\'imageFilters.filter.'.$filtName.'.filters\');

        /* New images will be saved to storage */
        $imgs = $request->file(\'images.*.file\');
        $crops = $request->input(\'images.*.crops\');

        if ($imgs) {
            $imgsArr = $this->saveImageToStorage($imgs, $filters, $crops);
        } else {
            $imgsArr = null;
        }

        /* Images will be deleted */
        $oldImages = '.$modelVar.'->images
                            ->whereNotIn(\'img_id\', $oldImgIDs);

        if ($oldImages->isNotEmpty()) {
            $this->deleteImageFromStorage($oldImages, $filters);
        }

        /* New images will be saved to databse */
        if ($imgsArr) {
            '.$modelVar.'->images()->saveMany($imgsArr);
        }
    }
';
};