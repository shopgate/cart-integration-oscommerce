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


class ShopgatePluginOsCommerceInitialisationTest extends PHPUnit_Framework_TestCase
{
    public function testWhenPluginIsInitialisedLanguageIsSet()
    {
        /** @var ShopgatePluginOsCommerce|PHPUnit_Framework_MockObject_MockObject $subjectUnderTest */
        $subjectUnderTest = $this->getMockBuilder('ShopgatePluginOsCommerce')
                                 ->disableOriginalConstructor()
                                 ->setMethods(array('startup'))
                                 ->getMock()
        ;

        $assumedLanguageId = 9999;

        $subjectUnderTest->setLanguageId($assumedLanguageId);

        $diMock = $this->getMockBuilder('tad_DI52_Container')
                       ->disableOriginalConstructor()
                       ->getMock()
        ;

        $diMock->expects(static::once())
               ->method('setVar')
               ->with('language_id', $assumedLanguageId)
        ;

        $subjectUnderTest->init(array('di' => $diMock));
    }
}
