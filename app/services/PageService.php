<?php
require_once "app/models/PageModel.php";
require_once "app/dtos/page/request/GetAllPagesFilterRequestDto.php";
require_once "app/exceptions/DBExceptionHandler.php";
class PageService
{


    public function getAll(GetAllPagesFilterRequestDto $dto)
    {

        return PageModel::orderBy('updated_at', 'desc')->get();
    }
    public function create(CreatePageRequestDto $dto)
    {
        try {
            $pageCreated = PageModel::create($dto->toInsertDB());
            return $pageCreated;
        } catch (Exception $e) {
            throw new DBExceptionHandler($e, [
                ["name" => "pages.slug", "message" => "Ya existe una página con este slug"]
            ]);
        }


    }

    public function update(UpdatePageRequestDto $dto)
    {
        try {
            $page = PageModel::find($dto->id);
            if (empty($page)) {
                throw AppException::validationError("La página seleccionada no existe");
            }

            $page->update($dto->toUpdateDB());
            return $page;
        } catch (Exception $e) {
            throw new DBExceptionHandler($e, [
                ["name" => "pages.slug", "message" => "Ya existe una página con este slug"]
            ]);
        }
    }
}
