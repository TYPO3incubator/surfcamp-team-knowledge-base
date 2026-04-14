<?php

declare(strict_types=1);

namespace TYPO3Incubator\KnowledgeBase\Tests\Functional;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class AbstractFunctionalTestBasis extends FunctionalTestCase
{

    protected array $coreExtensionsToLoad = [
        'install',
        'dashboard',
        'felogin',
        'beuser',
        'core',
    ];

    protected MockObject&ConfigurationManagerInterface $configurationManager;

    protected function setUp(): void
    {
        if (!getenv('typo3DatabasePassword')) {
            putenv('typo3DatabasePassword=root');
            putenv('typo3DatabaseUsername=root');
            putenv('typo3DatabaseHost=db');
            putenv('typo3DatabaseName=functionalSeminars');
        }
        parent::setUp();
    }
}
