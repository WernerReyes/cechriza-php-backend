<?php
require_once "app/models/SectionItemModel.php";
require_once "app/models/LinkModel.php";
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/utils/FileUploader.php";
require_once "app/dtos/sectionItem/response/SectionItemResponseDto.php";
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
                throw AppException::badRequest("El enlace seleccionado no existe");
            }
        }


        $imageUrl = null;
        $backgroundImageUrl = null;
        $fileIconUrl = null;
        if ($dto->sectionType == SectionType::HERO->value) {
            $imageUrl = $this->getImageToInsertDB($dto->imageUrl, $dto->fileImage);
            $backgroundImageUrl = $this->getImageToInsertDB($dto->backgroundImageUrl, $dto->backgroundFileImage);
        }
        // elseif ($dto->sectionType == SectionType::WHY_US->value || $dto->sectionType == SectionType::CASH_PROCESSING_EQUIPMENT->value || $dto->sectionType == SectionType::CONTACT_TOP_BAR->value || $dto->sectionType == SectionType::SOLUTIONS_OVERVIEW->value) {
        elseif (
            $this->allowIconDeletion($dto->sectionType)
        ) {
            if ($dto->iconType == IconType::IMAGE->value) {
                $fileIconUrl = $this->getImageToInsertDB($dto->fileIconUrl, $dto->fileIcon);
            }
        } elseif ($dto->sectionType == SectionType::CLIENT->value || $dto->sectionType == SectionType::MACHINE->value || $dto->sectionType == SectionType::OPERATIONAL_BENEFITS->value) {
            $imageUrl = $this->getImageToInsertDB($dto->imageUrl, $dto->fileImage);
        }



        $sectionItem = SectionItemModel::create($dto->toInsertDB($imageUrl, $backgroundImageUrl, $fileIconUrl));


        return new SectionItemResponseDto($sectionItem);

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

        $imageUrl = null;
        $backgroundImageUrl = null;
        $fileIconUrl = null;
        if ($dto->sectionType == SectionType::HERO->value) {
            $imageUrl = $this->getImageToUpdateDB($sectionItem->image, $dto->currentImageUrl, $dto->imageUrl, $dto->fileImage);
            $backgroundImageUrl = $this->getImageToUpdateDB($sectionItem->background_image, $dto->currentBackgroundImageUrl, $dto->backgroundImageUrl, $dto->backgroundFileImage);
        }
        // elseif ($dto->sectionType == SectionType::WHY_US->value || $dto->sectionType == SectionType::CASH_PROCESSING_EQUIPMENT->value || $dto->sectionType == SectionType::CONTACT_TOP_BAR->value || $dto->sectionType == SectionType::SOLUTIONS_OVERVIEW->value) {
        elseif (
            $this->allowIconDeletion($dto->sectionType)
        ) {

            if ($dto->iconType == IconType::IMAGE->value) {
                $fileIconUrl = $this->getImageToUpdateDB($sectionItem->icon, $dto->fileIconUrl, null, $dto->fileIcon);
            } elseif ($dto->iconType == IconType::LIBRARY->value) {
                // If switching to library icon, delete existing image icon if any
                if ($sectionItem->icon_url) {
                    $this->fileUploader->deleteImage($sectionItem->icon_url);


                }

                $fileIconUrl = null;
            }
        } elseif ($dto->sectionType == SectionType::CLIENT->value || $dto->sectionType == SectionType::MACHINE->value || $dto->sectionType == SectionType::OPERATIONAL_BENEFITS->value) {
            $imageUrl = $this->getImageToUpdateDB($sectionItem->image, $dto->currentImageUrl, $dto->imageUrl, $dto->fileImage);
        }



        $sectionItem->update($dto->toUpdateDB($imageUrl, $backgroundImageUrl, $fileIconUrl));

        return new SectionItemResponseDto($sectionItem);
    }



    public function duplicate(int $id)
    {
        $sectionItem = $this->findById($id);

        $newSectionItem = $sectionItem->replicate();
        $newSectionItem->title = $sectionItem->title . " (Copia)";

        if ($sectionItem->image) {
            $newSectionItem->image = $this->fileUploader->duplicateImage($sectionItem->image);
        }

        if ($sectionItem->background_image) {
            $newSectionItem->background_image = $this->fileUploader->duplicateImage($sectionItem->background_image);
        }

        if ($sectionItem->icon_url) {
            $newSectionItem->icon_url = $this->fileUploader->duplicateImage($sectionItem->icon_url);
        }

        $newSectionItem->save();

        return new SectionItemResponseDto($newSectionItem);
    }


    public function delete(int $id): void
    {
        $sectionItem = $this->findById($id);

        if ($sectionItem->image) {
            $this->fileUploader->deleteImage($sectionItem->image);
        }

        $sectionItem->delete();
    }

    private function allowIconDeletion(string $sectionType): bool
    {
        $typesAllowingIconDeletion = [
            SectionType::WHY_US->value,
            SectionType::CASH_PROCESSING_EQUIPMENT->value,
            SectionType::CONTACT_TOP_BAR->value,
            SectionType::SOLUTIONS_OVERVIEW->value,
            SectionType::ADVANTAGES->value,
            SectionType::MACHINE->value,
            SectionType::SUPPORT_MAINTENANCE->value
        ];

        return in_array($sectionType, $typesAllowingIconDeletion);
    }


    private function getImageToInsertDB($imageUrl, $fileImage)
    {
        $currentImageUrl = null;
        if (!empty($fileImage)) {
            $uploadResult = $this->fileUploader->uploadImage($fileImage);

            if (is_string($uploadResult)) {
                throw AppException::validationError("Image upload failed: " . $uploadResult);
            }

            $currentImageUrl = $uploadResult['path'];
        }

        if (!empty($imageUrl)) {
            $uploadResult = $this->fileUploader->uploadImageFromUrl($imageUrl);
            if (is_string($uploadResult)) {
                throw AppException::validationError("Image upload from URL failed: " . $uploadResult);
            }

            $currentImageUrl = $uploadResult['path'];
        }

        return $currentImageUrl;
    }

    private function getImageToUpdateDB($imageDB, $currentImageUrl, $newImageUrl, $fileImage)
    {

        if (empty($newImageUrl) && empty($fileImage) && empty($currentImageUrl)) {
            if ($imageDB) {
                $this->fileUploader->deleteImage($imageDB);
            }
            return null;
        } else if (empty($newImageUrl) && empty($fileImage) && !empty($currentImageUrl)) {
            return $this->fileUploader->getPathFromUrl($currentImageUrl);
        }

        if ($fileImage && $imageDB) {
            $this->fileUploader->deleteImage($imageDB);
        }

        return $this->getImageToInsertDB($newImageUrl, $fileImage);

    }


    private function findById(int $id): SectionItemModel
    {
        $sectionItem = SectionItemModel::find($id);
        if (empty($sectionItem)) {
            throw AppException::badRequest("El item de sección seleccionado no existe");
        }

        return $sectionItem;
    }
}
