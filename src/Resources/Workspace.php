<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Resources;

use Verifycate\Sdk\Dto\WorkspaceInfo;
use Verifycate\Sdk\Operations;

final class Workspace extends Resource
{
    /**
     * Resolve the workspace bound to the configured API key.
     *
     * This does not need a workspace slug in Config, which lets a client
     * connect from an API key alone.
     */
    public function current(): WorkspaceInfo
    {
        return WorkspaceInfo::fromArray(
            $this->client->request(Operations::CURRENT_WORKSPACE)
        );
    }
}
