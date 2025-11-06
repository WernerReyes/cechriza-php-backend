<?php


require_once "app/models/PageModel.php";
require_once "app/dtos/page/response/PageResponseDto.php";
require_once "app/dtos/page/request/GetAllPagesFilterRequestDto.php";
require_once "app/exceptions/DBExceptionHandler.php";
class PageService
{

    //* Public Methods
    public function getBySlug2(string $slug)
    {
        $page = PageModel::with($this->withRelations())->where('slug', $slug)->first();
        if (empty($page)) {
            $principal = PageModel::with($this->withRelations())->where('is_main', true)->first();
            if (empty($principal)) {
                $firstPage = PageModel::with($this->withRelations())->first();
                if (empty($firstPage)) {
                    throw AppException::validationError("La página seleccionada no existe");
                }

                return new PageResponseDto($firstPage);
            }

            return new PageResponseDto($principal);

        }
        return new PageResponseDto($page);
        // return $page;
    }

    public function getBySlug(string $slug)
    {
        $page = PageModel::withAllRelations()->where('slug', $slug)->first();

        if ($page) {
            return new PageResponseDto($page);
        }

        $principal =
            PageModel::withAllRelations()->where('is_main', true)->first();

        if ($principal) {
            return new PageResponseDto($principal);
        }

        $firstPage = PageModel::withAllRelations()->first();
        if (empty($firstPage)) {
            throw AppException::validationError("La página seleccionada no existe");
        }

        return new PageResponseDto(data: $firstPage);
    }


    private function withRelations()
    {
        return [
            'sections.sectionItems',
            'sections.menus:id_menu,title,parent_id,link_id',
            'sections.menus.parent.parent',
            'sections.menus.link:id_link,page_id,new_tab',
            'sections.menus.link.page:id_page,title,slug',
            'sections.menus.parent.link:id_link,page_id,new_tab',
            'sections.menus.parent.link.page:id_page,title,slug',
            'sections.menus.parent.parent.link:id_link,page_id,new_tab',
            'sections.menus.parent.parent.link.page.section:id_section,title,slug',

        ];
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

    public function setMain(int $id)
    {
        $page = PageModel::find($id);
        if (empty($page)) {
            throw AppException::validationError("La página seleccionada no existe");
        }

        // Desmarcar la página principal actual
        PageModel::where('is_main', true)->update(['is_main' => null]);

        // Marcar la nueva página principal
        $page->update(['is_main' => true]);

        return $page;
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
                ["name" => "fk_section_pages_page", "message" => "No se puede eliminar la página porque está asociada a uno o más secciones"]
            ]);
        }
    }
}
