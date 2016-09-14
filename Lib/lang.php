<?php
/**
 * COmanage Registry MLA Source Plugin Language File
 *
 * Copyright (C) 2016 Modern Language Association
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright     Copyright (C) 2016 Modern Language Association
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v1.1.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @version       $Id$
 */

global $cm_lang, $cm_texts;

// When localizing, the number in format specifications (eg: %1$s) indicates the argument
// position as passed to _txt.  This can be used to process the arguments in
// a different order than they were passed.

$cm_mla_source_texts['en_US'] = array(
  // Titles, per-controller
  'ct.mla_sources.1'  => 'MLA Organizational Identity Source',
  'ct.mla_sources.pl' => 'MLA Organizational Identity Sources',

  // Error messages
  'er.mlasource.connect'        => 'Failed to connect to MLA API',

  // Plugin texts
  'pl.mlasource.info'           => 'The MLA API server must be available and the specified credentials must be valid before this configuration can be saved.',
  'pl.mlasource.apikey'         => 'API Key',
  'pl.mlasource.apikey.desc'    => 'MLA API Key',
  'pl.mlasource.apiroot'        => 'API Root',
  'pl.mlasource.apiroot.desc'   => 'URL prefix for the API, including schema and host (eg: https://apidev.mla.org/1)',
  'pl.mlasource.apisecret'      => 'API Secret',
  'pl.mlasource.apisecret.desc' => 'MLA API Secret',
);
