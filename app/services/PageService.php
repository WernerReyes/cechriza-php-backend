<?php
require_once "app/models/PageModel.php";
require_once "app/dtos/page/response/PageResponseDto.php";
require_once "app/dtos/page/request/GetAllPagesFilterRequestDto.php";
require_once "app/exceptions/DBExceptionHandler.php";
class PageService
{

    //* Public Methods
    public function getBySlug(string $slug)
    {
        $page = PageModel::with('sections.sectionItems', 'sections.menus', 'sections.menus.parent'
         
        )->where('slug', $slug)->first();
        if (empty($page)) {
            $principal = PageModel::with('sections.sectionItems', 'sections.menus')->get()->first();
            if ($principal) return $principal;
            throw AppException::validationError("La página seleccionada no existe");
        }
        return new PageResponseDto($page);
        // return $page;
    }


    //* Private Methods
    public function getAll(GetAllPagesFilterRequestDto $dto)
    {

        return PageModel::orderBy('updated_at', 'desc')->get();
    }


    public function getById(int $id)
    {
        $page = PageModel::with('sections.sectionItems', 'sections.menus', 'pivot:id_page,id_section,order_num,active,type')->find($id);
        if (empty($page)) {
            throw AppException::validationError("La página seleccionada no existe");
        }
        // return new PageResponseDto($page);
        return $page;
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

    public function delete(int $id)
    {
        try {
            $page = PageModel::find($id);
            if (empty($page)) {
                throw AppException::validationError("La página seleccionada no existe");
            }

            $page->delete();

        } catch (Exception $e) {
            if (get_class($e) === "AppException") {
                throw $e;
            }
            throw new DBExceptionHandler($e, [
                ["name" => "fk_links_pages", "message" => "No se puede eliminar la página porque está asociada a uno o más enlaces"],
                ["name" => "fk_sections_pages", "message" => "No se puede eliminar la página porque está asociada a uno o más secciones"]
            ]);
        }
    }
}
