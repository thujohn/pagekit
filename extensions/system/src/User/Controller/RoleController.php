<?php

namespace Pagekit\User\Controller;

use Pagekit\Component\Database\ORM\Repository;
use Pagekit\Framework\Controller\Controller;
use Pagekit\User\Entity\Role;

/**
 * @Route("/system/user/role")
 * @Access("system: manage user permissions", admin=true)
 */
class RoleController extends Controller
{
    /**
     * @var Repository
     */
    protected $roles;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->roles = $this['users']->getRoleRepository();
    }

    /**
     * @Request({"id": "int"})
     * @Response("system/admin/user/role.razr")
     */
    public function indexAction($id = null)
    {
        $roles = $this->roles->query()->orderBy('priority')->get();

        if ($id === null && count($roles)) {
            $role = current($roles);
        } elseif ($id && isset($roles[$id])) {
            $role = $roles[$id];
        } else {
            $role = new Role;
            $role->setId(0);
        }

        $authrole = $this->roles->find(Role::ROLE_AUTHENTICATED);

        return array('head.title' => __('Roles'), 'role' => $role, 'roles' => $roles, 'authrole' => $authrole, 'permissions' => $this['permissions']);
    }

    /**
     * @Request({"id": "int", "name", "permissions": "array"})
     * @Response("json")
     * @Token
     */
    public function saveAction($id, $name = '', $permissions = array())
    {
        // is new ?
        if (!$role = $this->roles->find($id)) {
            $role = new Role;
        }

        if ($name !== '') {
            $role->setName($name);
        }

        $role->setPermissions($permissions);
        $this->roles->save($role);

        return  $this['request']->isXmlHttpRequest() ? ['message' =>__('Roles saved!')] : $this->redirect('@system/role', array('id' => isset($role) ? $role->getId() : 0));
    }

    /**
     * @Request({"id": "int"})
     * @Token
     */
    public function deleteAction($id = 0)
    {
        if ($role = $this->roles->find($id)) {
            $this->roles->delete($role);
        }

        return $this->redirect('@system/role');
    }

    /**
     * @Request({"order": "array"})
     * @Response("json")
     * @Token
     */
    public function priorityAction($order)
    {
        foreach ($order as $id => $priority) {

            $role = $this->roles->find($id);

            if ($role) {
                $this->roles->save($role, compact('priority'));
            }
        }

        return $order;
    }
}
