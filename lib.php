<?php
function local_mfa_user_check_extend_navigation_user($user) {
    global $DB, $USER, $PAGE;

    $plugin_enabled = get_config('local_mfa_user_check', 'enabled');

    if (!$plugin_enabled) {
        return;
    }

    if ($USER->id == 2) {
        return;
    }

    if (isloggedin() && !isguestuser()) {
        $redirect_url = get_config('local_mfa_user_check', 'redirect_url') ?: '/admin/tool/mfa/user_preferences.php';
        $current_url = $PAGE->url->out();

        $exclude_urls_config = get_config('local_mfa_user_check', 'exclude_urls');
        if (!empty($exclude_urls_config)) {
            $exclude_urls = array_map('trim', explode(',', $exclude_urls_config));
        } else {
            $exclude_urls = [
                $redirect_url,
                '/admin/tool/mfa/action.php',
                '/admin/tool/mfa/guide.php'
            ];
        }

		foreach ($exclude_urls as $url) {
			if (strpos($current_url, $url) !== false) {
				return;
			}
		}

        $result = $DB->get_record('tool_mfa', array('userid' => $USER->id, 'factor' => 'totp'));
        if (!$result || $result->revoked == 1) {
            redirect(new moodle_url($redirect_url));
        }
    }
}
