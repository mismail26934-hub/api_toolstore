<?php

/**
 * Resolve role logis ke daftar tb_users.level.
 */
class UserLevelRoles
{
    /** @var array<string, list<string>> */
    private array $roles;

    public function __construct()
    {
        $this->roles = require dirname(__DIR__) .
            "/config/user_level_roles.php";
    }

    /**
     * @return list<string>
     */
    public function levelsForRole(string $role): array
    {
        $role_trim = trim($role);
        if ($role_trim === "") {
            return [];
        }

        if (!isset($this->roles[$role_trim])) {
            return [$role_trim];
        }

        $levels = [];
        foreach ($this->roles[$role_trim] as $level) {
            $level_trim = trim((string) $level);
            if ($level_trim !== "") {
                $levels[] = $level_trim;
            }
        }

        return array_values(array_unique($levels));
    }
}
