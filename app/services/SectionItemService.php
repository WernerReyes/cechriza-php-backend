<?php
require_once "app/models/SectionItemModel.php";
require_once "app/models/LinkModel.php";
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/utils/FileUploader.php";
class SectionItemService
{

    public function create(CreateSectionItemRequestDto $dto)
    {
        if (!empty($dto->linkId)) {
            $link = LinkModel::find($dto->linkId);
            if (empty($link)) {
                throw AppException::validationError("El enlace seleccionado no existe");
            }
        }

        $imageUrl = $dto->imageUrl;
        if (!empty($dto->fileImage)) {
            $fileUploader = new FileUploader();
            $uploadResult = $fileUploader->uploadImage($dto->fileImage);

            if (is_string($uploadResult)) {
                throw AppException::validationError("Image upload failed: " . $uploadResult);
            }

            $imageUrl = $uploadResult['url'];
        }

        $sectionItem = SectionItemModel::create($dto->toInsertDB($imageUrl));
        return $sectionItem;

    }
}