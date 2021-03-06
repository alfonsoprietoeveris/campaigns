<?php

if (function_exists('drush_log')) {
  drush_log('Executing post-install tasks ...', 'ok');
}

require_once 'utils.php';

osha_change_field_size();
osha_configure_file_translator();
osha_newsletter_create_taxonomy();
osha_configure_search_autocomplete();
osha_configure_addtoany_social_share();
osha_disable_blocks();
osha_configure_permissions();
osha_config_development();
osha_configure_recaptcha();
osha_configure_on_the_web();
osha_configure_search_index();
osha_add_menu_position_rules();

variable_set('admin_theme', 'osha_admin');
variable_set('theme_default', 'osha_frontend');
variable_set('menu_position_active_link_display', 'parent');

// @todo: Workflow configuration - hook_enable throws errors.
variable_set('workbench_moderation_per_node_type', 1);
osha_workflow_create_roles();

module_disable(array('overlay'));

/**
 * Configure the index to exclude unpublished nodes.
 */
function osha_configure_search_index() {
  if ($index = search_api_index_load('default_multilingual_node_index')) {
    $index->options['data_alter_callbacks']['search_api_alter_node_status']['status'] = 1;
    $index->save();
    $index->reindex();
    drupal_set_message(t("The indexing workflow was successfully edited. All content was scheduled for re-indexing so the new settings can take effect."));
  }
}

/**
 * Configure permissions.
 *
 * @todo this is here because I cannot add it inside module due to SQL error:
 * SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'module' cannot
 * be null.
 *
 * {@inheritdoc}
 */
function osha_configure_permissions() {
  if ($role = user_role_load_by_name('administrator')) {
    $vocabularies = array(
      'activity',
      'article_types',
      'esener',
      'nace_codes',
      'section',
      'thesaurus',
      'wiki_categories',
      'workflow_status',

      'publication_types',
      'newsletter_sections',
    );
    $permissions = array();
    foreach ($vocabularies as $voc_name) {
      if ($voc = taxonomy_vocabulary_machine_name_load($voc_name)) {
        $permissions[] = 'add terms in ' . $voc_name;
        $permissions[] = 'edit terms in ' . $voc->vid;
        $permissions[] = 'delete terms in ' . $voc->vid;
      }
    }

    $permissions[] = 'access workbench access by role';

    $permissions[] = 'translate taxonomy_term entities';
    $permissions[] = 'edit any content in rejected';
    $permissions[] = 'edit any content in approved';
    $permissions[] = 'edit any content in final_draft';
    $permissions[] = 'edit any content in to_be_approved';

    // Workbench access permissions.

    $moderated_types = workbench_moderation_moderate_node_types();
    $transitions = workbench_moderation_transitions();
    foreach ($transitions as $transition) {
      $permissions[] = "moderate content from {$transition->from_name} to {$transition->to_name}";
      foreach ($moderated_types as $node_type) {
        //@todo: $permissions[] = "moderate $node_type state from {$transition->from_name} to {$transition->to_name}";
      }
    }

    $permissions[] = 'create moderators_group entity collections';
    $permissions[] = 'edit moderators_group entity collections';
    $permissions[] = 'view moderators_group entity collections';
    $permissions[] = 'delete moderators_group entity collections';
    $permissions[] = 'add content to moderators_group entity collections';
    $permissions[] = 'manage content in moderators_group entity collections';

    user_role_grant_permissions($role->rid, $permissions);
    user_role_revoke_permissions($role->rid, array('use workbench_moderation needs review tab'));
  }

  $roles = array(
    OSHA_WORKFLOW_ROLE_TRANSLATION_MANAGER,
    OSHA_WORKFLOW_ROLE_TRANSLATION_LIAISON,
    OSHA_WORKFLOW_ROLE_LAYOUT_VALIDATOR,
    OSHA_WORKFLOW_ROLE_CONTENT_VALIDATOR,
  );
  foreach ($roles as $role_name) {
    if ($role = user_role_load_by_name($role_name)) {
      user_role_grant_permissions($role->rid, array('access workbench'));
    }
  }
}

/**
 * Config file translator not available during osha_tmgmt installation.
 */
function osha_configure_file_translator() {
  /* @var TMGMTTranslator $file */
  $file = tmgmt_translator_load('file');
  if ($file) {
    $file->settings['export_format'] = 'xml';
    $file->settings['allow_override'] = FALSE;
    $file->save();
  }
}


/**
 * Populate initial terms into the newsletter_sections taxonomy.
 */
function osha_newsletter_create_taxonomy() {
  $voc = taxonomy_vocabulary_machine_name_load('newsletter_sections');
  $terms = taxonomy_get_tree($voc->vid);

  if (empty($terms)) {
    $new_terms = array(
      'highlight' => 'Highlights',
      '' => 'OSH matters',
      'publication' => 'Latest publications',
      'newsletter_article' => 'Coming soon',
      'blog' => 'Blog',
      'news' => 'News',
      'event' => 'Events',
    );
    $cont_type_term_map = array();
    $new_terms_ct = array_flip($new_terms);

    $weight = 0;

    foreach ($new_terms as $term_name) {
      $term = new stdClass();
      $term->name = $term_name;
      $term->language = 'en';
      $term->vid = $voc->vid;
      // Weight must be an integer.
      $term->weight = $weight++;
      taxonomy_term_save($term);
      if ($term->name == 'Coming soon') {
        variable_set('osha_newsletter_coming_soon_tid', $term->tid);
      }
      $cont_type_term_map[$new_terms_ct[$term->name]] = $term->tid;
    }
    variable_set('osha_newsletter_term_ct_map', $cont_type_term_map);
  }
}


/**
 * Set-up the search_autocomplete module.
 */
function osha_configure_search_autocomplete() {
  // Disable other search forms - we dont' use them.
  db_update('search_autocomplete_forms')
    ->fields(array(
      'enabled' => 0,
    ))
    ->condition('selector', '#edit-search-block-form--2', '<>')
    ->execute();
  // Configure the search form.
  $fid = db_select('search_autocomplete_forms', 'f')
    ->fields('f', array('fid'))
    ->condition('selector', '#edit-search-block-form--2')
    ->execute()->fetchField(0);
  if ($fid) {
    db_update('search_autocomplete_forms')
      ->fields(array(
        'data_view' => 'search_autocomplete',
        'theme' => 'basic-blue.css',
        'data_callback' => 'search_autocomplete/autocomplete/' . $fid . '/',
      ))
      ->condition('selector', '#edit-search-block-form--2')
      ->execute();
  }
  else {
    drupal_set_message('Failed to configure search_autocomplete form', 'error');
  }
}

/**
 * Add configuration to addtoany contrib module.
 */
function osha_configure_addtoany_social_share() {
  drupal_set_message('Configuring Addtoany contrib module ...');

  variable_set('addtoany_buttons_size', 16);
  variable_set('addtoany_additional_html', '<a class="a2a_button_twitter"></a><a class="a2a_button_facebook"></a><a class="a2a_button_linkedin"></a><a class="a2a_button_print"></a>');
  variable_set('addtoany_additional_html_placement', 'after');
  variable_set('addtoany_display_in_nodecont', '0');
  variable_set('addtoany_display_in_nodelink', '1');
  variable_set('addtoany_display_in_teasers', '0');
  variable_set('addtoany_link_text', 'Share this on:');
  variable_set('addtoany_image', 'text');
  variable_set('addtoany_custom_image', '');
  variable_set('addtoany_image_attributes', 'Share');

  variable_set('addtoany_nodetypes', array(
    'news' => 'news',
    'article' => 0,
    'page' => 0,
    'blog' => 'blog',
    'calls' => 0,
    'highlight' => 'highlight',
    'job_vacancies' => 0,
    'newsletter_article' => 0,
    'press_release' => 'press_release',
    'publication' => 0,
    'wiki_page' => 0,
    )
  );
}

/**
 * Set specific configuration for development environment.
 */
function osha_config_development() {
  $config = osha_get_config();
  if (in_array($config['variables']['environment'], array('production'))) {
    return;
  }

  // Enables email rerouting.
  if (module_exists('reroute_email') && isset($config['variables']['site_mail'])) {
    variable_set(REROUTE_EMAIL_ENABLE, 1);
    variable_set(REROUTE_EMAIL_ADDRESS, $config['variables']['site_mail']);
    variable_set(REROUTE_EMAIL_ENABLE_MESSAGE, 1);
  }
}

/*
 * Add configuration for recaptcha contrib module.
 */
function osha_configure_recaptcha() {
  drupal_set_message('Configuring reCaptcha contrib module ...');

  variable_set('captcha_default_challenge', 'recaptcha/reCAPTCHA');
  variable_set('captcha_default_validation', 1);
  variable_set('recaptcha_theme', 'custom');
}

/**
 * Add configuration for on_the_web contrib module.
 */
function osha_configure_on_the_web() {
  drupal_set_message('Configuring on_the_web contrib module ...');

  variable_set('on_the_web_sitename', 0);
  variable_set('on_the_web_facebook_page', 'http://www.facebook.com/EuropeanAgencyforSafetyandHealthatWork');
  variable_set('on_the_web_flickr_page', 'http://www.flickr.com/photos/euosha/');
  variable_set('on_the_web_twitter_page', 'http://twitter.com/eu_osha');
}

/**
 * Disable drupal default blocks
 */
function osha_disable_blocks(){
  drupal_set_message('Disable navigation block on osha_frontend theme');

  db_update('block')
  ->fields(array('status' => 0))
  ->condition('module', 'system')
  ->condition('delta', 'navigation')
  ->condition('theme', 'osha_frontend')
  ->execute();

  db_update('block')
  ->fields(array('status' => 0))
  ->condition('module', 'user')
  ->condition('delta', 'login')
  ->condition('theme', 'osha_frontend')
  ->execute();

  // we could also use drush
  // block-configure --module=user --delta=login --region=-1 --theme=osha_frontend

  // Flush cache.
  cache_clear_all();
}

/**
 * Add menu position rules.
 */
function osha_add_menu_position_rules() {
  if (module_exists('osha') && module_load_include('inc', 'osha', 'osha.utils')) {
    // Menu position rule for Press Release content type.
    $parent_menu = '------ Press room';
    $condition = array('content_type' => array('press_release' => 'press_release'));
    osha_add_menu_position_rule('Press room Menu Rule', $parent_menu, $condition);
    
    // Menu position rule for See all Press Releases Menu Rule.
    $condition = array('pages' => 'press-releases');
    osha_add_menu_position_rule('See all Press Releases Menu Rule', $parent_menu, $condition);

    // Menu position rule for Publication content type
    $condition = array('content_type' => array('publication' => 'publication'));
    osha_add_menu_position_rule('Publications Menu Rule', '------ Publications', $condition);

    // Menu position rule for Seminar content type
    $condition = array('content_type' => array('seminar' => 'seminar'));
    osha_add_menu_position_rule('Seminar Menu Rule', '------ Our seminars', $condition);

    // Menu position rule for Calls content type
    $condition = array('content_type' => array('calls' => 'calls'));
    osha_add_menu_position_rule('Calls Menu Rule', '------ Procurement', $condition);

    // Menu position rule for Job vacancies content type
    $condition = array('content_type' => array('job_vacancies' => 'job_vacancies'));
    osha_add_menu_position_rule('Job vacancies Menu Rule', '------ Careers', $condition);
  }
}
