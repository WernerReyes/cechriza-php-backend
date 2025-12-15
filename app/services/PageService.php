<?php


require_once "app/models/PageModel.php";
require_once "app/dtos/page/response/PageResponseDto.php";
require_once "app/dtos/page/request/GetAllPagesFilterRequestDto.php";
require_once "app/exceptions/DBExceptionHandler.php";
class PageService
{

    //* Public Methods
    public function getAllPageForSiteMap()
    {
        return PageModel::select('slug', 'updated_at')->get();
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




    //* Private Methods
    public function getAll(GetAllPagesFilterRequestDto $dto)
    {

        return PageModel::orderBy('updated_at', 'desc')->get();
    }


    public function getById(int $id)
    {
        $page = PageModel::select(
        'id_page',
        )->with([
            'sections.sectionItems',
            'sections.link:id_link,type,title,url,file_path,page_id',
            'sections.extraLink:id_link,type,title,url,file_path,page_id',
            'sections.sectionItems.link:id_link,type,title,url,file_path,page_id',
            // 'sections.pages:id_page',
            'sections.pageSections',

            'sections.machines:id_machine,name,images,description,category_id,long_description,technical_specifications,manual,link_id,text_button',
            'sections.machines.category:id_category,title,type',

            'sections.menus.parent.parent:id_menu,title',


        ])->find($id);


        if (empty($page)) {
            throw AppException::validationError("La página seleccionada no existe");
        }
        
        return new PageResponseDto($page);
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
