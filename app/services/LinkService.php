<?php
require_once "app/models/LinkModel.php";
require_once "app/models/PageModel.php";
require_once "app/dtos/link/response/LinkResponseDto.php";
class LinkService
{
    private readonly FileUploader $fileUploader;

    public function __construct()
    {
        $this->fileUploader = new FileUploader();
    }

    public function getAll()
    {
        $links = LinkModel::with('page:id_page,title,slug')->orderBy('updated_at', 'desc')->get();
        return $links->map(fn($link) => new LinkResponseDto($link));
    }

    public function create(CreateLinkRequestDto $dto)
    {
        if ($dto->type == LinkType::PAGE->value) {
            $page = PageModel::find($dto->pageId);
            if (empty($page)) {
                throw AppException::validationError("La página seleccionada no existe");
            }
        }


        $filePath = null;
        if ($dto->type == LinkType::FILE->value && !empty($dto->file)) {
            $filePath = $this->getFileToInsertDB($dto->file);
        }


        $link = LinkModel::create($dto->toInsertDB($filePath));
        // $link = LinkModel::with('page:id_page,title,slug')->find($link->id_link);
        $link->load('page:id_page,title,slug');
        return new LinkResponseDto($link);
    }


    public function update(UpdateLinkRequestDto $dto)
    {
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

        $filePath = null;
        if ($dto->type == LinkType::FILE->value) {
            $filePath = $this->getFileToUpdateDB($link->file_path, $dto->file);
        } else {
            if ($link->type == LinkType::FILE->value && !empty($link->file_path)) {
                $this->fileUploader->deleteFile($link->file_path);
            }
        }
        // error_log(json_encode($dto->toUpdateDB()));

        $link->update($dto->toUpdateDB($filePath));
        // $link = LinkModel::with('page:id_page,title,slug')->find($link->id_link);
        $link->load('page:id_page,title,slug');

        return new LinkResponseDto($link);
    }

    public function delete(int $id)
    {
        try {

            $link = LinkModel::find($id);
            if (empty($link)) {
                throw AppException::validationError("El enlace seleccionado no existe");
            }

            if ($link->type == LinkType::FILE->value && !empty($link->file_path)) {
                $this->fileUploader->deleteFile($link->file_path);
            }

            $link->delete();
        } catch (Exception $e) {
            if (get_class($e) === "AppException") {
                throw $e;
            }
            throw new DBExceptionHandler($e, [
                ["name" => "fk_section_items_link", "message" => "No se puede eliminar el enlace porque está asociado a uno o más ítems de sección"],
                ["name" => "fk_menu_links", "message" => "No se puede eliminar el enlace porque está asociado a uno o más menús"]
            ]);
        }
    }


    private function getFileToInsertDB($file)
    {
        $currentFileUrl = null;
        if (!empty($file)) {
            $uploadResult = $this->fileUploader->uploadFile($file);

            if (is_string($uploadResult)) {
                throw AppException::badRequest("File upload failed: " . $uploadResult);
            }

            $currentFileUrl = $uploadResult['path'];
        }

        return $currentFileUrl;
    }

    private function getFileToUpdateDB($currentFileUrl, $file)
    {
        $finalFileUrl = $currentFileUrl;
        if (!empty($file)) {
            if ($currentFileUrl) {

                $this->fileUploader->deleteFile($currentFileUrl);
            }

            return $this->getFileToInsertDB($file);
        }

        return $finalFileUrl;
    }

}