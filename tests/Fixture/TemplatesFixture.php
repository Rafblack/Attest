<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TemplatesFixture
 */
class TemplatesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'type' => 1,
                'multiple' => 1,
                'cv' => 1,
                'template' => 'Lorem ipsum dolor sit amet',
                'soldes' => 1,
            ],
        ];
        parent::init();
    }
}
