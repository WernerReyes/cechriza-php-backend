<?php
require_once "app/models/SectionItemModel.php";
require_once "app/models/LinkModel.php";
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/utils/FileUploader.php";
class SectionItemService
{

    private readonly FileUploader $fileUploader;

    public function __construct()
    {
        $this->fileUploader = new FileUploader();
    }

    public function create(CreateSectionItemRequestDto $dto)
    {
        if (!empty($dto->linkId)) {
            $link = LinkModel::find($dto->linkId);
            if (empty($link)) {
                throw AppException::validationError("El enlace seleccionado no existe");
            }
        }

        $imageUrl = $this->getImageToInsertDB($dto->imageUrl, $dto->fileImage);
        $backgroundImageUrl = $this->getImageToInsertDB($dto->backgroundImageUrl, $dto->backgroundFileImage);

        $sectionItem = SectionItemModel::create($dto->toInsertDB($imageUrl, $backgroundImageUrl));
        return $sectionItem;

    }


    public function update(UpdateSectionItemRequestDto $dto)
    {
        $sectionItem = $this->findById($dto->id);

        if (!empty($dto->linkId)) {
            $link = LinkModel::find($dto->linkId);
            if (empty($link)) {
                throw AppException::validationError("El enlace seleccionado no existe");
            }
        }

        // $imageUrl = $dto->imageUrl;

        // if (!empty($imageUrl)) {
        //     if ($sectionItem->image_url) {
        //         $this->fileUploader->deleteImage($sectionItem->image_url);
        //     }
        // }

        // if (!empty($dto->fileImage)) {
        //     if ($sectionItem->image_url) {
        //         $this->fileUploader->deleteImage($sectionItem->image_url);
        //     }

        //     $uploadResult = $this->fileUploader->uploadImage($dto->fileImage);

        //     if (is_string($uploadResult)) {
        //         throw AppException::validationError("Image upload failed: " . $uploadResult);
        //     }

        //     $imageUrl = $uploadResult['url'];
        // }

        $imageUrl = $this->getImageToUpdateDB($sectionItem->image, $dto->imageUrl, $dto->fileImage);
        $backgroundImageUrl = $this->getImageToUpdateDB($sectionItem->background_image, $dto->backgroundImageUrl, $dto->backgroundFileImage);

        $sectionItem->update($dto->toUpdateDB($imageUrl, $backgroundImageUrl));

        return $sectionItem;
    }


    public function delete(int $id): void
    {
        $sectionItem = $this->findById($id);

        if ($sectionItem->image) {
            $this->fileUploader->deleteImage($sectionItem->image);
        }

        $sectionItem->delete();
    }


    private function getImageToInsertDB($imageUrl, $fileImage)
    {
        $currentImageUrl = $imageUrl;
        if (!empty($fileImage)) {
            $uploadResult = $this->fileUploader->uploadImage($fileImage);

            if (is_string($uploadResult)) {
                throw AppException::validationError("Image upload failed: " . $uploadResult);
            }

            $currentImageUrl = $uploadResult['url'];
        }

        return $currentImageUrl;
    }

    private function getImageToUpdateDB($currentImageUrl, $newImageUrl, $fileImage)
    {
        if (!empty($newImageUrl)) {
            if ($currentImageUrl) {
                $this->fileUploader->deleteImage($currentImageUrl);
            }
            return $newImageUrl;
        }

        if (!empty($fileImage)) {
            if ($currentImageUrl) {
                $this->fileUploader->deleteImage($currentImageUrl);
            }

            $uploadResult = $this->fileUploader->uploadImage($fileImage);

            if (is_string($uploadResult)) {
                throw AppException::validationError("Image upload failed: " . $uploadResult);
            }

            return $uploadResult['url'];
        }

        return $currentImageUrl;
    }


    private function findById(int $id): SectionItemModel
    {
        $sectionItem = SectionItemModel::find($id);
        if (empty($sectionItem)) {
            throw AppException::validationError("El item de sección seleccionado no existe");
        }

        return $sectionItem;
    }
}
