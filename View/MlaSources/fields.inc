<!--
/**
 * COmanage Registry MLA Source Plugin Fields
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
-->
<?php
  // Determine if fields are editable
  $e = false;
  
  if(($this->action == "add" && $permissions['add']) || ($this->action == "edit" && $permissions['edit']))
    $e = true;
    
  // We shouldn't get here if we don't have at least read permission, but check just in case
  
  if(!$e && !$permissions['view'])
    return false;
  
  print $this->Form->hidden('org_identity_source_id', array('default' => $vv_oisid)) . "\n";

?>
<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> 
  <p>
    <span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
    <strong><?php print _txt('pl.mlasource.info'); ?></strong>
  </p>
</div>
<br />
<table id="<?php print $this->action; ?>_mla_source" class="ui-widget">
  <tbody>
    <tr class="line1">
      <td style="width:50%">
        <strong class="fieldTitle"><?php print _txt('pl.mlasource.apiroot'); ?></strong><font class="required">*</font><br />
        <font class="desc"><?php print _txt('pl.mlasource.apiroot.desc'); ?></font>
      </td>
      <td>
        <?php print ($e ? $this->Form->input('apiroot', array('size' => 50)) : Sanitize::html($mla_sources[0]['MlaSource']['apiroot'])); ?>
      </td>
    </tr>
    <tr class="line2">
      <td>
        <strong class="fieldTitle"><?php print _txt('pl.mlasource.apikey'); ?></strong><br />
        <font class="desc"><?php print _txt('pl.mlasource.apikey.desc'); ?></font>
      </td>
      <td>
        <?php print ($e ? $this->Form->input('apikey', array('size' => 50)) : Sanitize::html($mla_sources[0]['MlaSource']['apikey'])); ?>
      </td>
    </tr>
    <tr class="line1">
      <td>
        <strong class="fieldTitle"><?php print _txt('pl.mlasource.apisecret'); ?></strong><br />
        <font class="desc"><?php print _txt('pl.mlasource.apisecret.desc'); ?></font>
      </td>
      <td>
        <?php print ($e ? $this->Form->input('apisecret') : Sanitize::html($mla_sources[0]['MlaSource']['apisecret'])); ?>
      </td>
    </tr>
    <tr>
      <td>
        <i><font class="required"><?php print _txt('fd.req'); ?></font></i><br />
      </td>
      <td>
        <?php
          if($e) {
            print $this->Form->submit($submit_label);
            print $this->Form->button(_txt('op.reset'), 
                                      array('type'=>'reset'));
          }
        ?>
      </td>
    </tr>
  </tbody>
</table>
