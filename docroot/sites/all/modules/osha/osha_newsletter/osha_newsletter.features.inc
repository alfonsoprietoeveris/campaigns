<?php
/**
 * @file
 * osha_newsletter.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function osha_newsletter_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "entity_collection" && $api == "entity_collection_bundles_default") {
    return array("version" => "1");
  }
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function osha_newsletter_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}
