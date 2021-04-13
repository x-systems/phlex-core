<?php

declare(strict_types=1);

namespace atk4\core;

interface AppScopeInterface
{
    public function issetApp(): bool;

    public function getApp();

    public function setApp(object $app);
}
