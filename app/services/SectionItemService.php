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
        } elseif ($dto->sectionType == SectionType::WHY_US->value || $dto->sectionType == SectionType::CASH_PROCESSING_EQUIPMENT->value || $dto->sectionType == SectionType::CONTACT_TOP_BAR->value || $dto->sectionType == SectionType::SOLUTIONS_OVERVIEW->value) {
            $fileIconUrl = $this->getImageToInsertDB($dto->fileIconUrl, $dto->fileIcon);
        } elseif ($dto->sectionType == SectionType::CLIENT->value || $dto->sectionType == SectionType::MACHINE->value) {
            $imageUrl = $this->getImageToInsertDB($dto->imageUrl, $dto->fileImage);
        }



        // $fileIconUrl = $this->getImageToInsertDB($dto->fileIconUrl, $dto->fileIcon);

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
        } elseif ($dto->sectionType == SectionType::WHY_US->value || $dto->sectionType == SectionType::CASH_PROCESSING_EQUIPMENT->value || $dto->sectionType == SectionType::CONTACT_TOP_BAR->value || $dto->sectionType == SectionType::SOLUTIONS_OVERVIEW->value) {
            $fileIconUrl = $this->getImageToUpdateDB($sectionItem->icon, $dto->fileIconUrl, null, $dto->fileIcon);
        } elseif ($dto->sectionType == SectionType::CLIENT->value || $dto->sectionType == SectionType::MACHINE->value) {
            $imageUrl = $this->getImageToUpdateDB($sectionItem->image, $dto->currentImageUrl, $dto->imageUrl, $dto->fileImage);
        }



        $sectionItem->update($dto->toUpdateDB($imageUrl, $backgroundImageUrl, $fileIconUrl));

        return new SectionItemResponseDto($sectionItem);
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

            $currentImageUrl = $uploadResult['path'];
        }

        return $currentImageUrl;
    }

    private function getImageToUpdateDB($imageDB, $currentImageUrl, $newImageUrl, $fileImage)
    {

        if (empty($newImageUrl) && empty($fileImage) && empty($currentImageUrl)) {
            return null;
        } else if (empty($newImageUrl) && empty($fileImage) && !empty($currentImageUrl)) {
            return $currentImageUrl;
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
