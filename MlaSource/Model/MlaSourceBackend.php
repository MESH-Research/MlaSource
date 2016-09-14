<?php
/**
 * COmanage Registry MLA OrgIdentitySource Backend Model
 *
 * This model requires HTTP_Request2
 * https://pear.php.net/package/HTTP_Request2/
 * Install with pear via "pear install HTTP_Request2"
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

App::uses("OrgIdentitySourceBackend", "Model");
require_once 'HTTP/Request2.php';

class MlaSourceBackend extends OrgIdentitySourceBackend {
  public $name = "MlaSourceBackend";
  
  protected $groupAttrs = array(
    'organizations' => 'Organizations'
  );
  
  /** 
   * Build a query URL for an MLA API call, based on the plugin's configuration.
   *
   * @since  COmanage Registry v1.1.0
   * @param  Array $attributes Array of attributes as passed to search()
   * @return String URL
   */
  
  protected function buildQueryUrl($attributes) {
    $url = "";
    $id = null;
    
    $attrs = $attributes;
    $attrs['key'] = $this->pluginCfg['apikey'];
    $attrs['timestamp'] = time();
    
    if(!empty($attrs['id'])) {
      $id = $attrs['id'];
      unset($attrs['id']);
    }
    
    $url = $this->pluginCfg['apiroot'] . "/members";
    if($id) { $url .= "/" . $id; }
    $url .= "?" . http_build_query($attrs);
    $url .= "&" . $this->calculateSignature($url, "GET");
    
    return $url;
  }
  
  /**
   * Calcule the URL signature for an MLA API call.
   *
   * @since  COmanage Registry v1.1.0
   * @param  String $url URL
   * @param  String $httpMethod HTTP method (eg: "GET")
   * @return String Signature, in query parameter format
   */
  
  protected function calculateSignature($url, $httpMethod) {
    $base_string = $httpMethod . '&' . rawurlencode($url);
    $api_signature = hash_hmac('sha256', $base_string, $this->pluginCfg['apisecret']);
    
    return "signature=" . $api_signature;
  }
  
  /**
   * Generate the set of attributes for the IdentitySource that can be used to map
   * to group memberships. The returned array should be of the form key => label,
   * where key is meaningful to the IdentitySource (eg: a number or a field name)
   * and label is the localized string to be displayed to the user. Backends should
   * only return a non-empty array if they wish to take advantage of the automatic
   * group mapping service.
   *
   * @since  COmanage Registry v1.1.0
   * @return Array As specified
   */
  
  public function groupableAttributes() {
    return $this->groupAttrs;
  }
  
  /**
   * Obtain all available records in the IdentitySource, as a list of unique keys
   * (ie: suitable for passing to retrieve()).
   *
   * @since  COmanage Registry v1.1.0
   * @return Array Array of unique keys
   * @throws DomainException If the backend does not support this type of requests
   */
  
  public function inventory() {
    // XXX implement based on MLA requirements
    throw new DomainException("NOT IMPLEMENTED");
  }
  
  /**
   * Execute a REST request.
   *
   * @since  COmanage Registry v1.1.0
   * @param  String $url URL (request endpoint)
   * @param  String $body Request body, or NULL
   * @param  String $httpMethod HTTP request method (eg: "GET")
   * @todo   Move to app/Lib
   */
  
  protected function makeRestRequest($url, $httpMethod="GET", $body=null) {
    $ret = array();
    
    // json_encode requires PHP >= 5.2
    $requestBody = json_encode($body);
    
    $request = new HTTP_Request2($url);
    
    try {
      $timeout = 30;
      
      $request->setConfig('connect_timeout', $timeout);
      $request->setConfig(array('timeout' => $timeout));
      
      switch($httpMethod) {
        case 'GET':
          $request->setMethod(HTTP_Request2::METHOD_GET);
          break;
        case 'POST':
          $request->setMethod(HTTP_Request2::METHOD_POST);
          break;
        case 'PUT':
          $request->setMethod(HTTP_Request2::METHOD_PUT);
          break;
      }
      $request->setHeader('Content-type: application/json');
      // Disable SSL cert verification for testing with "bad" certs
      // $request->setConfig(array('ssl_verify_peer' => false));
      $request->setBody($requestBody);
      $response = $request->send();
    }
    catch(HTTP_Request2_Exception $e) {
      throw new RuntimeException($e->getMessage());
    }
    catch(Exception $e) {
      throw new RuntimeException($e->getMessage());
    }
    
    $ret['status'] = $response->getStatus();
    $ret['body'] = $response->getBody();
    
    return $ret;
  }
  
  /**
   * Query the MLA API.
   *
   * @since  COmanage Registry v1.1.0
   * @param  Array $attributes Attributes to query (ie: searchableAttributes())
   * @return Array Search results
   * @throws RuntimeException
   */
  
  protected function queryMlaApi($attributes) {
    $url = $this->buildQueryUrl($attributes);
    $response = $this->makeRestRequest($url);
    
    if($response['status'] == 200) {
      return json_decode($response['body'], true);
    } else {
      throw new RuntimeException('Received ' . $response['status'] . ' response');
    }
  }

  /**
   * Convert a raw result, as from eg retrieve(), into an array of attributes that
   * can be used for group mapping.
   *
   * @since  COmanage Registry v1.1.0
   * @param  String $raw Raw record, as obtained via retrieve()
   * @return Array Array, where keys are attribute names and values are lists (arrays) of attributes
   */
  
  public function resultToGroups($raw) {
    $ret = array();
    
    // Convert the raw string back to an array
    $attrs = json_decode($raw, true);
    
    // We ignore $this->groupAttrs for now since the only supported
    // attribute is organizations
    
    // We probably need to look at position for Member vs Admin (#125)
    
    if(!empty($attrs['data'][0]['organizations'])) {
      foreach($attrs['data'][0]['organizations'] as $o) {
        // We can search on name
        // $ret['organizations'][] = $o['name'];
        // Or convention code
        $ret['organizations'][] = $o['convention_code'];
      }
    }
    
    return $ret;
  }
  
  /**
   * Convert a search result into an Org Identity.
   *
   * @since  COmanage Registry v1.1.0
   * @param  Array $result netFORUM Search Result
   * @return Array Org Identity and related models, in the usual format
   */
  
  protected function resultToOrgIdentity($result) {
    $orgdata = array();
    $orgdata['OrgIdentity'] = array();
    
    // Until we have some rules, everyone is a member
    $orgdata['OrgIdentity']['affiliation'] = AffiliationEnum::Member;
    
    // Some attributes only show up in the detailed view (get via ID).
    // A member can have multiple addresses with somewhat different
    // attributes, for now we just look at primary.
    
    if(!empty($result['addresses'])) {
      foreach($result['addresses'] as $ra) {
        if($ra['type'] == 'primary') {
          if(!empty($ra['affiliation'])) {
            $orgdata['OrgIdentity']['o'] = $ra['affiliation'];
          }
          
          // Unclear what if anything should map to ou...
          
          if(!empty($ra['rank'])) {
            $orgdata['OrgIdentity']['title'] = $ra['rank'];
          }
          
          break;
        }
      }
    }

    $orgdata['PrimaryName'] = array();
    
    if(!empty($result['general']['first_name']))
      $orgdata['PrimaryName']['given'] = $result['general']['first_name'];
    if(!empty($result['general']['last_name']))
      $orgdata['PrimaryName']['family'] = $result['general']['last_name'];
    $orgdata['PrimaryName']['primary_name'] = true;
    $orgdata['PrimaryName']['type'] = NameEnum::Official;
    
    $orgdata['EmailAddress'] = array();
    
    if(!empty($result['general']['email'])) {
      $orgdata['EmailAddress'][0]['mail'] = $result['general']['email'];
      $orgdata['EmailAddress'][0]['type'] = EmailAddressEnum::Official;
      $orgdata['EmailAddress'][0]['verified'] = false;
    }
    
    return $orgdata;
  }
  
  /**
   * Retrieve a single record from the IdentitySource. The return array consists
   * of two entries: 'raw', a string containing the raw record as returned by the
   * IdentitySource backend, and 'orgidentity', the data in OrgIdentity format.
   *
   * @since  COmanage Registry v1.1.0
   * @param  String $id Unique key to identify record
   * @return Array As specified
   * @throws InvalidArgumentException if not found
   * @throws OverflowException if more than one match
   */
  
  public function retrieve($id) {
    $ret = array();
    
    $results = $this->queryMlaApi(array('id' => $id));

    if($results['meta']['status'] != 'success'
       || empty($results['data'][0]['id'])) {
      throw new InvalidArgumentException(_txt('er.id.unk-a', array($id)));
    }
      
    $ret['raw'] = json_encode($results, JSON_PRETTY_PRINT);
    $ret['orgidentity'] = $this->resultToOrgIdentity($results['data'][0]);
    
    return $ret;
  }
  
  /**
   * Perform a search against the IdentitySource. The returned array should be of
   * the form uniqueId => attributes, where uniqueId is a persistent identifier
   * to obtain the same record and attributes represent an OrgIdentity, including
   * related models.
   *
   * @since  COmanage Registry v1.1.0
   * @param  Array $attributes Array in key/value format, where key is the same as returned by searchAttributes()
   * @return Array Array of search results, as specified
   */
    
  public function search($attributes) {
    $ret = array();
    
    $attrs = $attributes;

    // OIS infrastructure expects 'mail', but MLA API uses 'email'
    if(isset($attrs['mail'])) {
      $attrs['email'] = $attrs['mail'];
      unset($attrs['mail']);
    }
    
    // Need this to get MLA staff
    $attrs['membership_status'] = 'ALL';
    
    $results = $this->queryMlaApi($attrs);

    // Turn the results into an array
    
    if($results['meta']['status'] == 'success'
       && $results['data'][0]['total_num_results'] > 0) {
      foreach($results['data'][0]['search_results'] as $r) {
        // Use the record ID as the unique ID
        $ret[ $r['id'] ] = $this->resultToOrgIdentity($r);
      }
    }
    
    return $ret;
  }
  
  /**
   * Generate the set of searchable attributes for the IdentitySource.
   * The returned array should be of the form key => label, where key is meaningful
   * to the IdentitySource (eg: a number or a field name) and label is the localized
   * string to be displayed to the user.
   *
   * @since  COmanage Registry v1.1.0
   * @return Array As specified
   */
  
  public function searchableAttributes() {
    return array(
      'first_name' => _txt('fd.name.given'),
      'last_name'  => _txt('fd.name.family'),
      'email'      => _txt('fd.email_address.mail')
    );
  }

  /**
   * Test the MLA API to verify that the connection information is valid.
   *
   * @since  COmanage Registry v1.1.0
   * @param  String API Root
   * @param  String API Key
   * @param  String API Secret
   * @return Boolean True if parameters are valid
   * @throws RuntimeException
   */
  
  public function verifyMlaServer($apiRoot, $apiKey, $apiSecret) {
    $this->pluginCfg = array();
    $this->pluginCfg['apiroot'] = $apiRoot;
    $this->pluginCfg['apikey'] = $apiKey;
    $this->pluginCfg['apisecret'] = $apiSecret;
    
    // Based on similar code in CoLdapProvisionerTarget
    
    // 99298 = Kathleen Fitzpatrick
    $results = $this->queryMlaApi(array('id' => 99298));
    
    if(count($results) < 1) {
      throw new RuntimeException(_txt('er.mlasource.connect'));
    }
    
    return true;
  }
}
