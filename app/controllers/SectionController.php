<?php
require_once "app/utils/FileUploader.php";
require_once "app/services/SectionService.php";
class SectionController extends AppController
{
    private SectionService $sectionService;

    public function __construct()
    {
        $this->sectionService = new SectionService();
    }

    public function create()
    {
        $image = $_FILES['image'];

        $fileUploader = new FileUploader();
        $uploadResult = $fileUploader->uploadImage($image);

        if (is_string($uploadResult)) {
            throw AppException::validationError("Image upload failed: " . $uploadResult);
        }

        return AppResponse::success($uploadResult);


        // $body = $this->body();
        // $dto = new CreateSectionRequestDto($body, $image);
        // $dto = $dto->validate();
        // if (is_array($dto)) {
        //     throw AppException::validationError("Validation failed", $dto);
        // }

        // return AppResponse::success($this->sectionService->create($dto));
    }
}
?>