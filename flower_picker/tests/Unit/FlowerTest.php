<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Flower;

class FlowerTest extends TestCase
{
    private static ValidatorInterface $validator;

    public static function setUpBeforeClass(): void
    {
        self::$validator =
            Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
    }

    public function testFlowerSrcCannotBeBlank()
    {
        $flower = new Flower();
        $flower->setSrc('');
        $flower->setAlt('Random image');
        $flower->setHash('random string');
        $violations = self::$validator->validate($flower);

        $this->assertCount(1, $violations);
        $this->assertEquals($violations[0]->getMessage(), 'This value should not be blank.');
    }

    public function testFlowerAltCannotBeBlank()
    {
        $flower = new Flower();
        $flower->setSrc('https://random-source/ranom-image.jpg');
        $flower->setAlt('');
        $flower->setHash('random string');
        $violations = self::$validator->validate($flower);

        $this->assertCount(1, $violations);
        $this->assertEquals($violations[0]->getMessage(), 'This value should not be blank.');
    }

    public function testFlowerHashCannotBeBlank()
    {
        $flower = new Flower();
        $flower->setSrc('https://random-source/ranom-image.jpg');
        $flower->setAlt('Random image');
        $flower->setHash('');
        $violations = self::$validator->validate($flower);

        $this->assertCount(1, $violations);
        $this->assertEquals($violations[0]->getMessage(), 'This value should not be blank.');
    }
}
