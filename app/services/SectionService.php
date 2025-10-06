<?php
require_once "app/models/SectionModel.php";
require_once "app/models/PageModel.php";
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/dtos/section/request/CreateSectionRequestDto.php";
class SectionService
{


    public function getAll()
    {
        return SectionModel::with('sectionItems')->orderBy('order_num', 'asc')->get();
    }

    public function create(CreateSectionRequestDto $dto)
    {
        try {
            $maxOrder = SectionModel::max('order_num') ?? 0;

            $section = SectionModel::create(array_merge($dto->toInsertDB(), ["order_num" => $maxOrder + 1]));
            return $section;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function update(UpdateSectionRequestDto $dto)
    {
        $section = SectionModel::find($dto->id);
        if (empty($section)) {
            throw AppException::validationError("La sección seleccionada no existe");
        }

        $section->update($dto->toUpdateDB());
        return $section;
    }
}