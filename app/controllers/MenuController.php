<?php
require_once "app/AppController.php";
require_once "app/services/MenuService.php";
require_once "app/dtos/menu/request/CreateMenuRequestDto.php";
require_once "app/dtos/menu/request/UpdateMenuRequestDto.php";

class MenuController extends AppController
{
    private MenuService $menuService;
    public function __construct()
    {
        $this->menuService = new MenuService();
    }

    public function getAll()
    {
        return AppResponse::success($this->menuService->getAll());
    }

    public function create()
    {
        $body = $this->body();
        $dto = new CreateMenuRequestDto($body);
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success($this->menuService->create($dto));

    }

    public function edit($id)
    {
        $body = $this->body();
        $dto = new UpdateMenuRequestDto(array_merge($body, ['id' => $id]));
        $dto = $dto->validate();
        if (is_array($dto)) {
            throw AppException::validationError("Validation failed", $dto);
        }

        return AppResponse::success($this->menuService->update($dto));
    }
}
?>