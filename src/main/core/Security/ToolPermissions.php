<?php

namespace Claroline\CoreBundle\Security;

final class ToolPermissions
{
    public static function getPermission(string $toolName, string $permission): string
    {
        return strtoupper("TOOL_{$toolName}/{$permission}");
    }

    public static function isPermission(string $permission): bool
    {
        return str_starts_with(strtoupper($permission), 'TOOL_');
    }

    public static function parsePermission(string $permission): array
    {
        $permissionParts = explode('/', strtoupper($permission));

        return [
            // tool name
            str_replace('TOOL_', '', $permissionParts[0]),
            // tool permission
            $permissionParts[1],
        ];
    }
}
