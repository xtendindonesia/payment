<?php

declare(strict_types = 1);

namespace Xtend\Payment\VA\Adapter;

interface AdapterInterface
{
    public function setSandbox(bool $sandbox);

    public function getSandbox(): bool;

    public function getEndpoint(): string;

    public function setConfigs(array $confings);

    public function getConfigs(): ?array;

    public function create(string $number, float $amount, string $name, string $desc, \DateTime $expired): ?array;

    public function delete(string $number);

    public function update(string $number, array $data);

    public function getDetail(string $number);
}
