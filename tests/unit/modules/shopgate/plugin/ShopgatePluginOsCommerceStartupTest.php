<?php
/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */


class ShopgatePluginOsCommerceStartupTest extends PHPUnit_Framework_TestCase
{
    /** @var ShopgateConfigOsCommerce|PHPUnit_Framework_MockObject_MockObject */
    private $shopgateConfigOsCommerceMock;

    /** @var ShopgatePluginOsCommerce|PHPUnit_Framework_MockObject_MockObject */
    private $subjectUnderTest;

    public function setUp()
    {
        parent::setUp();

        defined('TABLE_LANGUAGES') ? : define('TABLE_LANGUAGES', 'mock_lang');
        defined('TABLE_CURRENCIES') ? : define('TABLE_CURRENCIES', 'mock_currency');
        defined('TABLE_TAX_RATES') ? : define('TABLE_TAX_RATES', 'mock_tax_rate');

        $this->shopgateConfigOsCommerceMock = $this->getMock('ShopgateConfigOsCommerce');
        $this->subjectUnderTest             = $this->getMockBuilder('ShopgatePluginOsCommerce')
                                                   ->disableOriginalConstructor()
                                                   ->setMethods(
                                                       array(
                                                           'createNewOsCommerceConfig', 'wrapperPerformQuery',
                                                           'wrapperPerformFetchArray'
                                                       )
                                                   )
                                                   ->getMock()
        ;
        $this->subjectUnderTest->expects(static::once())
                               ->method('createNewOsCommerceConfig')
                               ->will(static::returnValue($this->shopgateConfigOsCommerceMock))
        ;
    }

    public function testWhenPluginIsStartedAndConfigLanguageCannotBeMappedStartupWillFail()
    {
        $this->shopgateConfigOsCommerceMock->expects(static::once())
                                           ->method('getLanguage')
                                           ->will(static::returnValue('JP'))
        ;

        $this->subjectUnderTest->expects(static::once())
                               ->method('wrapperPerformQuery')
                               ->will(static::returnValue(null))
        ;

        $this->setExpectedExceptionRegExp(
            'ShopgateLibraryException', '/Error selecting language/',
            ShopgateLibraryException::PLUGIN_DATABASE_ERROR
        );

        $this->subjectUnderTest->startup();
    }

    /**
     * @param array $language
     *
     * @dataProvider getInvalidLanguageResultFixtures
     */
    public function testWhenPluginIsStartedAndLanguageFromConfigurationIsFoundButIncompleteOrInvalidStartupWillFail(
        $language
    ) {
        $this->shopgateConfigOsCommerceMock->expects(static::once())
                                           ->method('getLanguage')
                                           ->will(static::returnValue('DE'))
        ;

        $resourceMock = true;

        $this->subjectUnderTest->expects(static::once())
                               ->method('wrapperPerformQuery')
                               ->will(static::returnValue($resourceMock))
        ;

        $this->subjectUnderTest->expects(static::once())
                               ->method('wrapperPerformFetchArray')
                               ->will(static::returnValue($language))
        ;

        $this->setExpectedExceptionRegExp(
            'ShopgateLibraryException', '/language code \[.*?\] does not exist/',
            ShopgateLibraryException::PLUGIN_DATABASE_ERROR
        );

        $this->subjectUnderTest->startup();
    }

    public function getInvalidLanguageResultFixtures()
    {
        return array(
            array(array()),
            array(array('languages_id' => null)),
            array(array('languages_id' => '')),
            array(array('languages_id' => 'DE', 'directory' => null)),
            array(array('languages_id' => 'DE', 'directory' => '')),
            array(array('directory' => '/home/')),
        );
    }

    public function testWhenPluginIsStartedAndShopNumberIsAvailableConfigIsLoadedByProvidedShopNumber()
    {
        $shopNumber = 11556;
        $this->subjectUnderTest->expects(static::once())
                               ->method('createNewOsCommerceConfig')
                               ->will(static::returnValue($this->shopgateConfigOsCommerceMock))
        ;

        $this->shopgateConfigOsCommerceMock->expects(static::once())
                                           ->method('loadByShopNumber')
                                           ->with($shopNumber)
        ;

        $_REQUEST['shop_number'] = $shopNumber;

        // expecting it to limit the scope of the test
        $this->setExpectedException('ShopgateLibraryException');

        $this->subjectUnderTest->startup();
    }
}
