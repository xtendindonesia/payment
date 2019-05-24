<?php

declare(strict_types = 1);

namespace Xtend\Payment\VA\Adapter;

interface AdapterInterface
{
    public function setConfigs(array $confings);

    public function getConfigs();

    public function setClient($client);

    public function getClient();

    public function create(string $number, float $amount, string $desc, \DateTime $expired);

    public function delete(string $number);

    public function update(string $number, array $data);

    public function getDetail(string $number);
}
