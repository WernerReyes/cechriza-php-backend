<?php
require_once "app/models/SectionModel.php";
require_once "app/models/PageModel.php";
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/dtos/section/request/CreateSectionRequestDto.php";
use Illuminate\Database\Capsule\Manager as Capsule;
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

    public function updateOrder(UpdateOrderRequestDto $dto)
    {

        $sections = SectionModel::whereIn('id_section', array_column($dto->orderArray, 'id'))->get();
        if (count($sections) !== count($dto->orderArray)) {
            throw AppException::badRequest("Una o más secciones no existen con los IDs proporcionados.");
        }

        Capsule::connection()->transaction(function () use ($dto) {
            foreach ($dto->orderArray as $item) {
                SectionModel::where('id_section', $item['id'])->update([
                    'order_num' => $item['order'],
                ]);
            }
        });

    }
}