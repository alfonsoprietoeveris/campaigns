<?php
/**
 * @file
 * osha_calls.features.fe_block_settings.inc
 */

/**
 * Implements hook_default_fe_block_settings().
 */
function osha_calls_default_fe_block_settings() {
  $export = array();

  $export['version'] = '2.0';

  $export['block-calls_link_to_archive'] = array(
    'cache' => -1,
    'custom' => 0,
    'i18n_block_language' => array(),
    'i18n_mode' => 0,
    'machine_name' => 'calls_link_to_archive',
    'module' => 'block',
    'node_types' => array(),
    'pages' => 'articles/procurement',
    'roles' => array(),
    'themes' => array(
      'bartik' => array(
        'region' => '',
        'status' => 0,
        'theme' => 'bartik',
        'weight' => 0,
      ),
      'osha_admin' => array(
        'region' => '',
        'status' => 0,
        'theme' => 'osha_admin',
        'weight' => 0,
      ),
      'osha_frontend' => array(
        'region' => 'content',
        'status' => 1,
        'theme' => 'osha_frontend',
        'weight' => 3,
      ),
      'seven' => array(
        'region' => '',
        'status' => 0,
        'theme' => 'seven',
        'weight' => 0,
      ),
    ),
    'title' => '',
    'visibility' => 1,
  );

  $export['views-calls-block'] = array(
    'cache' => -1,
    'custom' => 0,
    'delta' => 'calls-block',
    'i18n_block_language' => array(),
    'i18n_mode' => 0,
    'module' => 'views',
    'node_types' => array(),
    'pages' => 'articles/procurement',
    'roles' => array(),
    'themes' => array(
      'bartik' => array(
        'region' => '',
        'status' => 0,
        'theme' => 'bartik',
        'weight' => 0,
      ),
      'osha_admin' => array(
        'region' => '',
        'status' => 0,
        'theme' => 'osha_admin',
        'weight' => 0,
      ),
      'osha_frontend' => array(
        'region' => 'content',
        'status' => 1,
        'theme' => 'osha_frontend',
        'weight' => 1,
      ),
      'seven' => array(
        'region' => '',
        'status' => 0,
        'theme' => 'seven',
        'weight' => 0,
      ),
    ),
    'title' => '',
    'visibility' => 1,
  );

  $export['views-calls-block_1'] = array(
    'cache' => -1,
    'custom' => 0,
    'delta' => 'calls-block_1',
    'i18n_block_language' => array(),
    'i18n_mode' => 0,
    'module' => 'views',
    'node_types' => array(),
    'pages' => 'articles/procurement',
    'roles' => array(),
    'themes' => array(
      'bartik' => array(
        'region' => '',
        'status' => 0,
        'theme' => 'bartik',
        'weight' => 0,
      ),
      'osha_admin' => array(
        'region' => '',
        'status' => 0,
        'theme' => 'osha_admin',
        'weight' => 0,
      ),
      'osha_frontend' => array(
        'region' => 'content',
        'status' => 1,
        'theme' => 'osha_frontend',
        'weight' => 2,
      ),
      'seven' => array(
        'region' => '',
        'status' => 0,
        'theme' => 'seven',
        'weight' => 0,
      ),
    ),
    'title' => '',
    'visibility' => 1,
  );

  return $export;
}