<?php
/**
 * COmanage Registry MLA Source Controller
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
 * @package       registry
 * @since         COmanage Registry v1.1.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @version       $Id$
 */

App::uses("SOISController", "Controller");

class MlaSourcesController extends SOISController {
  // Class name, used by Cake
  public $name = "MlaSources";

  // Establish pagination parameters for HTML views
  public $paginate = array(
    'limit' => 25,
    'order' => array(
      'apiroot' => 'asc'
    )
  );
  
  /**
   * Perform any dependency checks required prior to a write (add/edit) operation.
   * This method is intended to be overridden by model-specific controllers.
   *
   * @since  COmanage Registry v1.1.0
   * @param  Array Request data
   * @param  Array Current data
   * @return boolean true if dependency checks succeed, false otherwise.
   */

  function checkWriteDependencies($reqdata, $curdata = null) {
    // Make sure we can connect to the specified server
  
    try {
      $this->loadModel('MlaSource.MlaSourceBackend');
      
      $this->MlaSourceBackend->verifyMlaServer($reqdata['MlaSource']['apiroot'],
                                               $reqdata['MlaSource']['apikey'],
                                               $reqdata['MlaSource']['apisecret']);
    }
    catch(RuntimeException $e) {
      $this->Flash->set($e->getMessage(), array('key' => 'error'));
      return false;
    }
  
    return true;
  }

  /**
   * Authorization for this Controller, called by Auth component
   * - precondition: Session.Auth holds data used for authz decisions
   * - postcondition: $permissions set with calculated permissions
   *
   * @since  COmanage Registry v1.1.0
   * @return Array Permissions
   */

  function isAuthorized() {
    $roles = $this->Role->calculateCMRoles();

    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();

    // Determine what operations this user can perform

    $coadmin = false;

    if($roles['coadmin'] && !$this->CmpEnrollmentConfiguration->orgIdentitiesPooled()) {
      // CO Admins can only manage org identity sources if org identities are NOT pooled
      $coadmin = true;
    }

    // Delete an existing MLA Source?
    $p['delete'] = $roles['cmadmin'] || $coadmin;

    // Edit an existing MLA Source?
    $p['edit'] = $roles['cmadmin'] || $coadmin;

    // View all existing MLA Source?
    $p['index'] = $roles['cmadmin'] || $coadmin;

    // View an existing MLA Source?
    $p['view'] = $roles['cmadmin'] || $coadmin;

    $this->set('permissions', $p);
    return($p[$this->action]);
  }
}
