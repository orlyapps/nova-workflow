<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orlyapps\NovaWorkflow\SupportStrategy;

use Symfony\Component\Workflow\SupportStrategy\WorkflowSupportStrategyInterface;
use Symfony\Component\Workflow\WorkflowInterface;


final class ClassSupportStrategy implements WorkflowSupportStrategyInterface
{
    private $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(WorkflowInterface $workflow, object $subject): bool
    {

        return get_class($subject) == $this->className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}
