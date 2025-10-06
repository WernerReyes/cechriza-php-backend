<?php
require_once "app/models/LinkModel.php";
require_once "app/models/PageModel.php";
class LinkService
{
    public function getAll()
    {
        return LinkModel::with('page:id_page,title,slug')->orderBy('updated_at', 'desc')->get();
    }

    public function create(CreateLinkRequestDto $dto)
    {
        if ($dto->type == LinkType::PAGE->value) {
            $page = PageModel::find($dto->pageId);
            if (empty($page)) {
                throw AppException::validationError("La página seleccionada no existe");
            }
        }
        $link = LinkModel::create($dto->toInsertDB());
        $link = LinkModel::with('page:id_page,title,slug')->find($link->id_link);
        return $link;
    }


    public function update(UpdateLinkRequestDto $dto) {
        $link = LinkModel::find($dto->id);
        if (empty($link)) {
            throw AppException::validationError("El enlace seleccionado no existe");
        }
        if (empty($dto->pageId) && $dto->type == LinkType::PAGE->value) {
            $page = PageModel::find($dto->pageId);
            if (empty($page)) {
                throw AppException::validationError("La página seleccionada no existe");
            }
        }

        error_log(json_encode($dto->toUpdateDB()));

        $link->update($dto->toUpdateDB());
        $link = LinkModel::with('page:id_page,title,slug')->find($link->id_link);

        return $link;
    }

}