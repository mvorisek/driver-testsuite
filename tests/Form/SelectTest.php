<?php

namespace Behat\Mink\Tests\Driver\Form;

use Behat\Mink\Tests\Driver\TestCase;

final class SelectTest extends TestCase
{
    public function testMultiselect()
    {
        $this->getSession()->visit($this->pathTo('/multiselect_form.html'));
        $webAssert = $this->getAssertSession();
        $page = $this->getSession()->getPage();
        $this->assertEquals('Multiselect Test', $webAssert->elementExists('css', 'h1')->getText());

        $selectWithoutOption = $webAssert->fieldExists('select_without_option');
        $this->assertSame('', $selectWithoutOption->getValue());

        $selectWithNoOptionSelected = $webAssert->fieldExists('select_first_option_is_selected_by_default');
        $this->assertEquals('1', $selectWithNoOptionSelected->getValue());

        $select = $webAssert->fieldExists('select_number');
        $multiSelect = $webAssert->fieldExists('select_multiple_numbers[]');
        $secondMultiSelect = $webAssert->fieldExists('select_multiple_values[]');

        $this->assertEquals('20', $select->getValue());
        $this->assertSame(array(), $multiSelect->getValue());
        $this->assertSame(array('2', '3'), $secondMultiSelect->getValue());

        $select->selectOption('thirty');
        $this->assertEquals('30', $select->getValue());

        $multiSelect->selectOption('one', true);

        $this->assertSame(array('1'), $multiSelect->getValue());

        $multiSelect->selectOption('three', true);

        $this->assertEquals(array('1', '3'), $multiSelect->getValue());

        $secondMultiSelect->selectOption('two');
        $this->assertSame(array('2'), $secondMultiSelect->getValue());

        $button = $page->findButton('Register');
        $this->assertNotNull($button);
        $button->press();

        $out = <<<'OUT'
  agreement = `off`,
  select_first_option_is_selected_by_default = `1`,
  select_multiple_numbers = array(
    0 = `1`,
    1 = `3`,
  ),
  select_multiple_values = array(
    0 = `2`,
  ),
  select_number = `30`,
OUT;
        $this->assertStringContainsString($out, $page->getContent());
    }

    /**
     * @dataProvider elementSelectedStateCheckDataProvider
     */
    public function testElementSelectedStateCheck($selectName, $optionValue, $optionText)
    {
        $session = $this->getSession();
        $webAssert = $this->getAssertSession();
        $session->visit($this->pathTo('/multiselect_form.html'));
        $select = $webAssert->fieldExists($selectName);

        $option = $webAssert->elementExists('named', array('option', $optionValue), $select);

        $this->assertFalse($option->isSelected());
        $select->selectOption($optionText);
        $this->assertTrue($option->isSelected());
    }

    public static function elementSelectedStateCheckDataProvider()
    {
        return array(
            array('select_number', '30', 'thirty'),
            array('select_multiple_numbers[]', '2', 'two'),
        );
    }

    public function testSetValueSingleSelect()
    {
        $session = $this->getSession();
        $session->visit($this->pathTo('/multiselect_form.html'));
        $select = $this->getAssertSession()->fieldExists('select_number');

        $select->setValue('10');
        $this->assertEquals('10', $select->getValue());
    }

    public function testSetValueMultiSelect()
    {
        $session = $this->getSession();
        $session->visit($this->pathTo('/multiselect_form.html'));
        $select = $this->getAssertSession()->fieldExists('select_multiple_values[]');

        $select->setValue(array('1', '2'));
        $this->assertEquals(array('1', '2'), $select->getValue());
    }

    /**
     * @see https://github.com/Behat/Mink/issues/193
     */
    public function testOptionWithoutValue()
    {
        $session = $this->getSession();
        $session->visit($this->pathTo('/issue193.html'));

        $session->getPage()->selectFieldOption('options-without-values', 'Two');
        $this->assertEquals('Two', $this->findById('options-without-values')->getValue());

        $this->assertTrue($this->findById('two')->isSelected());
        $this->assertFalse($this->findById('one')->isSelected());

        $session->getPage()->selectFieldOption('options-with-values', 'two');
        $this->assertEquals('two', $this->findById('options-with-values')->getValue());
    }

    /**
     * @see https://github.com/Behat/Mink/issues/131
     */
    public function testAccentuatedOption()
    {
        $this->getSession()->visit($this->pathTo('/issue131.html'));
        $page = $this->getSession()->getPage();

        $page->selectFieldOption('foobar', 'Gimme some accentués characters');

        $this->assertEquals('1', $page->findField('foobar')->getValue());
    }
}
