<?php

namespace Component\Model;

interface ApiClientInterface
{
    public function getClientId();

    public function getSecret();
}
