<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2022 Sxxxxx Bxxxxx <sxxxxx@xxxxx.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    switchuser/admin/setup.php
 * \ingroup switchuser
 * \brief   SwitchUser setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once '../lib/switchuser.lib.php';
//require_once "../class/myclass.class.php";

// Translations
$langs->loadLangs(array("admin", "switchuser@switchuser"));

// Access control
if (!$user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

/*
 * Actions
 */
$action = GETPOST('action');
if ($action === 'switch') {

// Check if user is superadmin
	if ($user->entity != 0) {
		setEventMessage($langs->trans('NotSuperAdmin'));
	} else {
		$u = new User($db);
		$res = $u->fetch(GETPOST('userid', 'int'));
		if ($res) {
			//Check if user is active
			if ($u->statut == 1) {
				$_SESSION["dol_login"] = $u->login;
				header('location:' . dol_buildpath('/', 1));
			} else {
				setEventMessage('ErrorUserNotActive');
			}
		} else {
			setEventMessage('ErrorFetchUser');
		}
	}
}

/*
 * View
 */

$form = new Form($db);

$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);

$page_name = "SwitchUserSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_switchuser@switchuser');

// Configuration header
$head = switchuserAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', '', -1, "switchuser@switchuser");

// Check if user is superadmin
if ($user->entity != 0) {
	setEventMessage($langs->trans('NotSuperAdmin'));
} else {
	?>
	<form action="?" name="f1">
		<fieldset>
			<legend><?php print $langs->trans('SwitchUserSelect') ?></legend>
			<input type="hidden" name="action" value="switch"/>
			<?php print $form->select_dolusers('', 'userid', 0, null, 0, '', '', '0', 0, 0, '', 0, '', '', 1); ?>
			<button class="button" type="submit" name="switch" value="Switch" ><?php print $langs->trans('nom du bouton') ?></button>
		</fieldset>

	</form>
	<?php
}

// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
