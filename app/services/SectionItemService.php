<?php
require_once "app/models/SectionItemModel.php";
require_once "app/entities/SectionItemEntity.php";
require_once "app/exceptions/DBExceptionHandler.php";

class SectionItemService
{
    private SectionModel $sectionModel;
    private SectionItemModel $sectionItemModel;

    public function __construct()
    {
        $this->sectionModel = SectionModel::getInstance();
        $this->sectionItemModel = SectionItemModel::getInstance();
    }

    public function create(CreateSectionItemRequestDto $dto)
    {
        try {
            $model = $this->sectionModel->getByField(SectionSearchField::ID, $dto->sectionId);
            if (empty($model)) {
                throw AppException::notFound("La sección no existe");
            }

            $sectionItem = $this->sectionItemModel->create($dto->toInsertDB());
            return new SectionItemEntity($sectionItem);

        } catch (Exception $e) {
            if ($e instanceof AppException) {
                throw $e;
            }

            throw new DBExceptionHandler($e, [
                ["name" => "order_UNIQUE", "message" => "No puede haber dos items con el mismo orden en una sección"],
            ]);

        }

    }
}