<?php
// +----------------------------------------------------------------------+
// | Anuko Time Tracker
// +----------------------------------------------------------------------+
// | Copyright (c) Anuko International Ltd. (https://www.anuko.com)
// +----------------------------------------------------------------------+
// | LIBERAL FREEWARE LICENSE: This source code document may be used
// | by anyone for any purpose, and freely redistributed alone or in
// | combination with other software, provided that the license is obeyed.
// |
// | There are only two ways to violate the license:
// |
// | 1. To redistribute this code in source form, with the copyright
// |    notice or license removed or altered. (Distributing in compiled
// |    forms without embedded copyright notices is permitted).
// |
// | 2. To redistribute modified versions of this code in *any* form
// |    that bears insufficient indications that the modifications are
// |    not the work of the original author(s).
// |
// | This license applies to this document only, not any other software
// | that it may be combined with.
// |
// +----------------------------------------------------------------------+
// | Contributors:
// | https://www.anuko.com/time_tracker/credits.htm
// +----------------------------------------------------------------------+

import('ttConfigHelper');
import('ttGroupHelper');

// ttGroup class is used to store attributes for a single group in Time Tracker.
// We use it in ttUser class to have acces to "on behalf" group properties.
class ttGroup {
  var $id = null;               // Group id.
  var $parent_id = null;        // Paerent group id.
  var $org_id = null;           // Organization id.
  var $name = null;             // Group name.
  var $lang = null;             // Language.
  var $decimal_mark = null;     // Decimal separator.
  var $date_format = null;      // Date format.
  var $time_format = null;      // Time format.
  var $week_start = 0;          // Week start day.
  var $tracking_mode = 0;       // Tracking mode.
  var $project_required = 0;    // Whether project selection is required on time entires.
  var $task_required = 0;       // Whether task selection is required on time entires.
  var $record_type = 0;         // Record type (duration vs start and finish, or both).
  var $punch_mode = 0;          // Whether punch mode is enabled for user.
  var $allow_overlap = 0;       // Whether to allow overlapping time entries.
  var $future_entries = 0;      // Whether to allow creating future entries.
  var $bcc_email = null;        // Bcc email.
  var $allow_ip = null;         // Specification from where user is allowed access.
  var $password_complexity = null; // Password complexity example.
  var $currency = null;         // Currency.
  var $plugins = null;          // Comma-separated list of enabled plugins.

  var $config = null;           // Comma-separated list of miscellaneous config options.
  var $configHelper = null;     // An instance of ttConfigHelper class.

  var $custom_logo = 0;         // Whether to use a custom logo for group.
  var $lock_spec = null;        // Cron specification for record locking.
  var $holidays = null;         // Holidays specification.
  var $workday_minutes = 480;   // Number of work minutes in a regular day.

  var $active_users = 0;        // Count of active users in group.
                                // We need a non-zero count to display some menus.

  // Constructor.
  // Note: org_id is needed because we construct an object in ttUser constructor,
  // when global $user object does not yet exist.
  function __construct($id, $org_id) {
    $mdb2 = getConnection();

    $sql = "select * from tt_groups where id = $id and org_id = $org_id";
    $res = $mdb2->query($sql);
    if (is_a($res, 'PEAR_Error')) {
      return;
    }

    $val = $res->fetchRow();
    if ($val['id'] > 0) {
      $this->id = $val['id'];
      $this->parent_id = $val['parent_id'];
      $this->org_id = $val['org_id'];
      $this->name = $val['name'];
      $this->lang = $val['lang'];
      $this->decimal_mark = $val['decimal_mark'];
      $this->date_format = $val['date_format'];
      $this->time_format = $val['time_format'];
      $this->week_start = $val['week_start'];
      $this->tracking_mode = $val['tracking_mode'];
      /* TODO: initialize other things here.
      $this->project_required = $val['project_required'];
      $this->task_required = $val['task_required'];
       */
      $this->record_type = $val['record_type'];
      /*
      $this->bcc_email = $val['bcc_email'];
      $this->allow_ip = $val['allow_ip'];
      $this->password_complexity = $val['password_complexity'];
      $this->group_name = $val['group_name'];
      */
      $this->currency = $val['currency'];
      $this->plugins = $val['plugins'];
      $this->lock_spec = $val['lock_spec'];
      $this->holidays = $val['holidays'];
      $this->workday_minutes = $val['workday_minutes'];
      /*
      $this->custom_logo = $val['custom_logo'];
      */

      // TODO: refactor this.
      $this->config = $val['config'];
      $this->configHelper = new ttConfigHelper($val['config']);
      // Set user config options.
      $this->punch_mode = $this->configHelper->getDefinedValue('punch_mode');
      $this->allow_overlap = $this->configHelper->getDefinedValue('allow_overlap');
      $this->future_entries = $this->configHelper->getDefinedValue('future_entries');
    }

    // Determine active user count in a separate query.
    // TODO: If performance becomes an issue, investigate combining 2 queries in one.
    // At this time we only need to know if at least 1 active user exists.
    $sql = "select count(*) as user_count from tt_users".
      "  where group_id = $id and org_id = $org_id and status = 1";
    $res = $mdb2->query($sql);
    if (is_a($res, 'PEAR_Error')) {
      return;
    }
    $val = $res->fetchRow();
    $this->active_users = $val['user_count'];
  }
}
