<?php
require_once "app/exceptions/DBExceptionHandler.php";
require_once "app/models/MenuModel.php";
require_once "app/models/LinkModel.php";
use Illuminate\Database\Capsule\Manager as Capsule;
class MenuService
{

    public function getAll()
    {
        $menus = MenuModel::with([
            // 'children' => function ($query) {
            //     $query->orderBy('order_num');
            // },
            'children.children.parent',
            'children.children',
            'children.children.link' => function ($query) {
                $query->select('id_link', 'type', 'title', 'page_id');
            },

            'children.parent',
            'children.link' => function ($query) {
                $query->select('id_link', 'type', 'title', 'page_id');
            },
            'children.link.page' => function ($query) {
                $query->select('id_page', 'title', 'slug');
            },
            'link' => function ($query) {
                $query->select('id_link', 'type', 'title', 'page_id');
            },
            'link.page' => function ($query) {
                $query->select('id_page', 'title', 'slug');
            }
        ])
            ->whereNull('parent_id')
            // ->orderBy('order_num')
            ->get();
        return $menus;
    }



    public function create(CreateMenuRequestDto $dto)
    {
        if ($dto->linkId) {

            $existLink = LinkModel::find($dto->linkId);
            if (empty($existLink)) {
                throw AppException::badRequest("El enlace no existe");
            }
        }


        $parentMenu = null;
        if ($dto->parentId !== null) {
            $parentMenu = MenuModel::find($dto->parentId);
            if (empty($parentMenu)) {
                throw AppException::badRequest("El menú padre no existe");
            }

            $parentMenu->load('parent.parent');
            if (isset($parentMenu->parent->parent)) {
                throw AppException::badRequest("Un menú no puede tener más de dos niveles de profundidad.");
            }
        }

        $menuCreated = Capsule::connection()->transaction(function () use ($dto, $parentMenu) {

            $menuCreated = MenuModel::create(array_merge($dto->toInsertDB()));
            $menuCreated->load(['link:title,type,page_id,id_link']);

            // //* Si tiene padre, actualizar el menú padre para que sea dropdown
            // if ($parentMenu !== null && $parentMenu->link_id !== null) {
            //     MenuModel::where('id_menu', $parentMenu->id_menu)->update([
            //         'link_id' => null,
            //     ]);
            // }

             if ($menuCreated->children->count() > 0) {
                // $menu = MenuModel::find($dto->id);
                $menuCreated->load('children');
                
            } 
             if ($menuCreated->parent_id) {
                // $menu = MenuModel::find($dto->id);
                $menuCreated->load('parent');

            } 
            if ($menuCreated->link_id) {
                // $menu = MenuModel::find($dto->id);
                // $menu->load('children');
                $menuCreated->load('link:title,type,page_id,id_link');
            }

            return $menuCreated;
        });

        return $menuCreated;
    }



    public function updateOrder(UpdateMenuOrderRequestDto $dto)
    {
        $menu = MenuModel::whereIn('id_menu', array_column($dto->menuOrderArray, 'id'))->get();
        if (count($menu) !== count($dto->menuOrderArray)) {
            throw AppException::badRequest("Uno o más menús no existen con los IDs proporcionados.");
        }

        Capsule::connection()->transaction(function () use ($dto) {
            error_log(json_encode($dto->menuOrderArray) . " ---upda ");
            foreach ($dto->menuOrderArray as $item) {
                MenuModel::where('id_menu', $item['id'])->update(['order_num' => $item['order']]);
            }
        });

    }


    public function update(UpdateMenuRequestDto $dto)
    {
        $menu = MenuModel::with('children')->find($dto->id);
        if (empty($menu)) {
            throw AppException::notFound("No existe un menú con el ID proporcionado");
        }

        // if ($dto->linkId && $menu->children->count() > 0) {
        //     throw AppException::badRequest("Un menú que es padre no puede tener un enlace asignado.");
        // } else
        if ($dto->linkId) {
            $link = LinkModel::find($dto->linkId);
            if (empty($link)) {
                throw AppException::badRequest("El enlace no existe");
            }
        }


        if ($dto->parentId !== null && $dto->parentId === $menu->id_menu) {
            throw AppException::badRequest("Un menú no puede ser su propio padre.");
        }

        $parentMenu = null;
        if ($dto->parentId !== null) {
            $parentMenu = MenuModel::find($dto->parentId);
            if (empty($parentMenu)) {
                throw AppException::badRequest("El menú padre no existe");
            }

            $parentMenu->load('parent.parent');
            if (isset($parentMenu->parent->parent)) {
                throw AppException::badRequest("Un menú no puede tener más de dos niveles de profundidad.");
            }
        }

        if ($dto->parentId !== null && $dto->parentId !== $menu->parent_id &&  $menu->children->count() > 0) {
            throw AppException::badRequest("Un menú con submenús no puede ser hijo de otro menú.");
        }

        $menu = Capsule::connection()->transaction(function () use ($dto, $menu) {


            $menu->update($dto->toUpdateDB());



            if ($menu->children->count() > 0) {
                // $menu = MenuModel::find($dto->id);
                $menu->load('children');
                
            } 
             if ($menu->parent_id) {
                // $menu = MenuModel::find($dto->id);
                $menu->load('parent');

            } 
            if ($menu->link_id) {
                // $menu = MenuModel::find($dto->id);
                // $menu->load('children');
                $menu->load('link:title,type,page_id,id_link');
            }


            // //* Si tiene padre, actualizar el menú padre para que sea dropdown
            // if ($parentMenu !== null && $parentMenu->link_id !== null) {
            //     MenuModel::where('id_menu', $parentMenu->id_menu)->update([
            //         'link_id' => null,
            //     ]);
            // }

            return $menu;
        });



        return $menu;

    }




    public function delete(int $id): void
    {

        $menu = MenuModel::with(['children'])->find($id);
        if (empty($menu)) {
            throw AppException::notFound("No existe un menú con el ID proporcionado");
        }


        if (!empty($menu->children) && count($menu->children) > 0) {
            $menu->children->each(function ($child) {

            });
            MenuModel::where('parent_id', $menu->id_menu)->delete();
        }


        $menu->delete();

    }

}