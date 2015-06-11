<?php
/**
 * @file
 * hcw_homepage.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function hcw_homepage_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function hcw_homepage_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}

/**
 * Implements hook_node_info().
 */
function hcw_homepage_node_info() {
  $items = array(
    'frontpage_slider' => array(
      'name' => t('Frontpage Slider'),
      'base' => 'node_content',
      'description' => t('Frontpage slider content'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}