<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Library\Testing\StubPluginTrait;

class ValidatorTest extends MockeryTestCase
{
    use StubPluginTrait;

    public function testValidatorAcceptsOnlyInstancesOfCheckerInterface()
    {
        $this->setExpectedException('InvalidArgumentException');
        $checkers = [
            'regular' => $this->mock('Claroline\CoreBundle\Library\Installation\Plugin\CheckerInterface'),
            'wrong' => new \stdClass(),
        ];

        new Validator($checkers);
    }

    public function testValidatorCollectsValidationErrorsFromCheckers()
    {
        $firstChecker = $this->mock('Claroline\CoreBundle\Library\Installation\Plugin\CheckerInterface');
        $secondChecker = $this->mock('Claroline\CoreBundle\Library\Installation\Plugin\CheckerInterface');
        $thirdChecker = $this->mock('Claroline\CoreBundle\Library\Installation\Plugin\CheckerInterface');
        $plugin = $this->mock('Claroline\CoreBundle\Library\DistributionPluginBundle');

        $firstError = new ValidationError('foo');
        $secondError = new ValidationError('bar');
        $thirdError = new ValidationError('baz');

        $firstChecker->shouldReceive('check')
            ->once()
            ->with($plugin, false)
            ->andReturn([]);
        $secondChecker->shouldReceive('check')
            ->once()
            ->with($plugin, false)
            ->andReturn([$firstError]);
        $thirdChecker->shouldReceive('check')
            ->once()
            ->with($plugin, false)
            ->andReturn([$secondError, $thirdError]);

        $validator = new Validator([$firstChecker, $secondChecker, $thirdChecker]);

        $errors = $validator->validate($plugin);
        $this->assertEquals([$firstError, $secondError, $thirdError], $errors);
    }
}
