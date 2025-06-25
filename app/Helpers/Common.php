<?php

use App\Enums\ActionType;
use App\Enums\RoleCodes;
use App\Exceptions\InvalidAccessException;
use App\Models\Admin;
use App\Models\Technician;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Nullix\CryptoJsAes\CryptoJsAes;

if (!function_exists('authUser')) {
    function authUser()
    {
        $user = auth()->guard('api')->user();
        if (!$user) {
            throw new InvalidAccessException("INVALID_ACCESS");
        }
        $user = User::FindByUUID($user->uuid);
        if (!$user) {
            throw new InvalidAccessException("INVALID_ACCESS");
        }
        return $user;
    }
}

if (!function_exists('authVendor')) {
    function authVendor()
    {
        $user = auth()->guard('vendor')->user();
        if (!$user) {
            throw new InvalidAccessException("INVALID_ACCESS");
        }
        $user = Vendor::FindByUUID($user->uuid);
        if (!$user) {
            throw new InvalidAccessException("INVALID_ACCESS");
        }
        return $user;
    }
}

if (!function_exists('authTech')) {
    function authTech()
    {
        $user = auth()->guard('tech')->user();
        if (!$user) {
            throw new InvalidAccessException("INVALID_ACCESS");
        }
        $user = Technician::FindByUUID($user->uuid);
        if (!$user) {
            throw new InvalidAccessException("INVALID_ACCESS");
        }
        return $user;
    }
}

if (!function_exists('authAdmin')) {
    function authAdmin()
    {
        $user = auth()->guard('admin')->user();
        if (!$user) {
            throw new InvalidAccessException("INVALID_ACCESS");
        }
        $user = Admin::FindByUUID($user->uuid);
        if (!$user) {
            throw new InvalidAccessException("INVALID_ACCESS");
        }
        return $user;
    }
}

if (!function_exists('paginRespBuilder')) {
    function paginRespBuilder(FormRequest $request, Builder $conditionedQuery, $defPage = null, $defPerPage = null, $defOrderBy = null, $defOrder = null)
    {
        $dataQuery = $conditionedQuery;

        $defPage ??= 1;
        $defPerPage ??= 10;
        $defOrderBy ??= "id";
        $defOrder ??= "desc";

        $page = $request->page_no;
        $perPage = $request->limit;
        $orderBy = $request->sort_by;
        $order = $request->sort_order;

        $page ??= $defPage;
        $perPage ??= $defPerPage;
        $orderBy ??= $defOrderBy;
        $order ??= $defOrder;

        $page = ($page <= 0) ? 1 : $page;

        $dataQuery = $dataQuery->orderBy($orderBy, $order);
        $dataQuery = $dataQuery->forPage($page, $perPage);

        $repsonseQuery = clone $conditionedQuery;
        $paginator = $repsonseQuery->paginate($perPage, ['*'], 'page' . $page, $page);
        $repsonse = [
            'count' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'has_next_page' => $paginator->hasMorePages(),
            'has_previous_page' => $paginator->onFirstPage() ? false : true,
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
            'num_items' => $paginator->perPage(),
            'num_pages' => $paginator->lastPage(),
            'previous_page' => !$paginator->onFirstPage() ? $paginator->currentPage() - 1 : null,
        ];

        $data = [
            'query' => $dataQuery,
            'response' => $repsonse,
        ];
        return $data;
    }
}

if (!function_exists('isValidRequestField')) {
    function isValidRequestField(FormRequest $request, $field)
    {
        if (!$field) return false;
        return $request->has($field) && $request->get($field) != null && !empty($request->get($field));
    }
}

if (!function_exists('msg')) {
    function msg($message, $attributes = [])
    {
        if (strpos($message, 'lang.') !== false) {
            $message = str_replace('lang.', "", $message);
        }
        return trans("lang." . $message, $attributes);
    }
}

if (!function_exists('HasPermission')) {
    function HasPermission(RoleCodes $permissionCode, ActionType $action)
    {
        $admin = authAdmin();
        $authUser = Admin::where("id", $admin->id)->with(['role.accesses'])->first();
        try {
            $accesses = $authUser->role->accesses;
            for ($i = 0; $i < count($accesses); $i++) {
                $access = $accesses[$i];
                $menu = $access->menu;
                $mode = $action->value;
                if (strcmp($menu->code, $permissionCode->value) == 0) {
                    return ($access->$mode === 1);
                }
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('MyLeftMenus')) {
    function MyLeftMenus()
    {
        $admin = authAdmin();
        $menus = [];
        $authUser = Admin::where("id", $admin->id)->with(['role.accesses'])->first();
        try {
            $menus = formTree($authUser->role->accesses);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return $menus;
    }

    /**
     * Build a tree structure of menus based on the access collection.
     *
     * @param Collection $accessCollection
     * @return Collection
     */
    function formTree(Collection $accessCollection)
    {
        $filter = [];
        foreach ($accessCollection as $access) {
            $menu = $access->menu;
            $filter[$menu->ordering] = getAccess($access);
        }
        $menusTree = [];
        foreach ($accessCollection as $access) {
            if ($access) {
                $menu = $access->menu;
                if ($menu) {
                    if ($menu->parent_menu_id === 0) {
                        $menusTree[] = &$filter[$menu->ordering];
                    } else {
                        $filter[$menu->parent_menu_id]['childMenus'][] = &$filter[$menu->ordering];
                    }
                }
            }
        }
        return collect($menusTree);
    }

    /**
     * Helper method to build a tree structure based on parent_menu_id.
     *
     * @param Collection $menus
     * @return Collection
     */
    function getAccess($access)
    {
        if ($access) {
            $menu = $access->menu;
            if ($menu) {
                $temp = clone $access;
                unset($temp->menu);
                return [
                    'id' => $menu->id,
                    'parent_menu_id' => $menu->parent_menu_id,
                    'name' => $menu->menu,
                    'code' => $menu->code,
                    'url' => $menu->url,
                    'icon' => $menu->icon,
                    'ordering' => $menu->ordering,
                    "access" => [
                        "can_create" => $temp->can_create,
                        "can_read" => $temp->can_read,
                        "can_update" => $temp->can_update,
                        "can_delete" => $temp->can_delete,
                    ],
                    "childMenus" => [],
                ];
            }
        }
        return null;
    }
}

if (!function_exists('getValue')) {
    function getValue($value, $default = null)
    {
        if (!is_null($value) && !empty($value)) {
            return $value;
        }
        return $default;
    }
}

if (!function_exists('isValid')) {
    function isValid($value)
    {
        return (!is_null($value) && !empty($value));
    }
}

if (!function_exists('isValidSheetHeadings')) {
    function isValidSheetHeadings($requestFile, array $headings)
    {
        if (isValid($requestFile)) {
            $content = Excel::toArray([], $requestFile);
            if (count($content) > 0) {
                if (count($content[0])) {
                    $data = $content[0][0];
                    $i = 0;
                    foreach ($headings as $val) {
                        $val  = trim(strtolower($val));
                        if (count($data) > $i) {
                            $head = trim(strtolower($data[$i]));
                            if ($val !== $head) {
                                return false;
                            }
                        }
                        $i++;
                    }
                    return true;
                }
            }
        }
        return false;
    }
}

if (!function_exists('saltEncrypt')) {
    function saltEncrypt($data, $password)
    {
        return CryptoJsAes::encrypt($data, $password);
    }
}

if (!function_exists('saltDecrypt')) {
    function saltDecrypt($encryptData, $password)
    {
        return CryptoJsAes::decrypt($encryptData, $password);
    }
}
