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
include_once(DIR_FS_CATALOG . 'ext/modules/shopgate/base/shopgate_config.php');
$actionName = 'sg_option';

// determine configuration language: $_GET > $_SESSION > global
$sg_language_get = (!empty($_GET['sg_language'])
    ? '&sg_language=' . $_GET['sg_language']
    : ''
);

$sgLinks      = array(
    'info'   => FILENAME_SHOPGATE . '?' . $actionName . '=info' . $sg_language_get,
    'help'   => FILENAME_SHOPGATE . '?' . $actionName . '=help' . $sg_language_get,
    'config' => FILENAME_SHOPGATE . '?' . $actionName . '=config' . $sg_language_get,
);
$sgLinkTitles = array(
    'info'   => BOX_SHOPGATE_INFO,
    'help'   => BOX_SHOPGATE_HELP,
    'config' => BOX_SHOPGATE_CONFIG,
);

// FILE CONTENT DEPENDS ON VERSION!
if (defined('PROJECT_VERSION') && strpos(PROJECT_VERSION, '2.3') !== false):
    ?>

    <?php
    $cl_box_groups[] = array(
        'heading' => BOX_SHOPGATE,
        'apps'    => array(),
    );

    foreach ($sgLinks as $linkIdentifier => $shopgateLink) {
        $linkParts = array($shopgateLink, '');
        if (strpos($shopgateLink, '?') !== false) {
            $linkParts = explode('?', $shopgateLink);
        }
        $cl_box_groups[count($cl_box_groups) - 1]['apps'][] = array(
            'code'  => FILENAME_SHOPGATE,
            'title' => $sgLinkTitles[$linkIdentifier],
            'link'  => ShopgateWrapper::href_link($linkParts[0], $linkParts[1]),
        );
    }
    ?>
<?php else: ?>
    <!-- shopgate //-->
    <tr>
        <td>
            <?php
            $heading  = array();
            $contents = array();

            if (defined('PROJECT_VERSION')
                && (strpos(PROJECT_VERSION, '2.2-MS2-CVS') !== false
                    || strpos(PROJECT_VERSION, 'Preview Release 2.2-MS1') !== false
                    || strpos(PROJECT_VERSION, 'osC 2.2-MS2') !== false)
            ) {
                $additionalParams = ShopgateWrapper::get_all_get_params(array('selected_box'));
                $boxLink          = basename($PHP_SELF);
            } else {
                $additionalParams = '';
                $boxLink          = FILENAME_SHOPGATE;
            }

            $heading[] = array(
                'text' => BOX_SHOPGATE,
                'link' => ShopgateWrapper::href_link(
                    $boxLink, $additionalParams . 'selected_box=' . strtolower(BOX_SHOPGATE)
                )
            );

            if (strpos($selected_box, strtolower(BOX_SHOPGATE)) !== false) {
                $contents['text'] = '';

                foreach ($sgLinks as $linkIdentifier => $shopgateLink) {
                    $linkParts = array($shopgateLink, '');
                    if (strpos($shopgateLink, '?') !== false) {
                        $linkParts = explode('?', $shopgateLink);
                    }

                    if (strpos(PROJECT_VERSION, 'osC 2.2-MS2') !== false) {
                        $contents['text'] .= '<a href="' . tep_href_link($linkParts[0], $linkParts[1], 'NONSSL')
                            . '" class="menuBoxContentLink">' . $sgLinkTitles[$linkIdentifier] . '</a><br>';
                    } else {
                        $contents['text'] .= '<a href="' . ShopgateWrapper::href_link($linkParts[0], $linkParts[1])
                            . '" class="menuBoxContentLink">' . $sgLinkTitles[$linkIdentifier]
                            . '</a><br/>';
                    }
                }
                // cut last <br/>
                if (!empty($contents['text'])) {
                    $contents[0]['text'] = substr($contents['text'], 0, -5);
                }
            }

            $box = new box;
            echo $box->menuBox($heading, $contents);
            ?>
        </td>
    </tr>
    <!-- shopgate_eof //-->
<?php endif; ?>
