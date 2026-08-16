<?php

declare(strict_types=1);

use PhpCsFixer\Config;

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules(['@PER-CS3x0' => true, 'declare_strict_types' => true])
    ->setFinder(PhpCsFixer\Finder::create()->in([__DIR__ . '/src', __DIR__ . '/tests']));