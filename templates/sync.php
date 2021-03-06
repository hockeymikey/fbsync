<?php
/**
 * fbsync
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author NOIJN <skjnldsv@protonmail.com>
 * @copyright NOIJN 2015
 */

script('fbsync', 'login');
script('fbsync', 'sync');
style('fbsync', 'fbsync');
?>

<div id="app" class="fbsync">
	
	<div id="app-navigation">
		<?php print_unescaped($this->inc('part.navigation')); ?>
		<?php print_unescaped($this->inc('part.settings')); ?>
	</div>
	
	<div id="app-content">
		<div id="controls">
			<div class="controls-left">
<!--				<div class="controls_item button last crumb"><h2>Sync profile pictures</h2></div>-->
				<button id="syncpic" class="tooltipped-bottom syncbutton"
						title="Will sync profile pictures for people matched with one of your facebook friend">
					Sync pictures
				</button>
				<span class="tooltipped-bottom joinedbuttons"
					  title="Will sync birthdays for the contacts who doesn't have one set or if the already
							 defined date is older than the date we find">
					<button id="syncbday" class="syncbutton">
						Sync birthdays&nbsp;&nbsp;&nbsp;1
					</button>
					<button id="syncbdayalt" class="syncbutton">
						2
					</button>
				</span>
				<button id="delpictures" class="tooltipped-bottom syncbutton"
						title="WARNING! Will remove all the profiles pictures on your selected addressbook(s)">
					Remove all pictures
				</button>
				<button id="delbdays" class="tooltipped-bottom syncbutton"
						title="WARNING! Will delete all the birthdays on your selected addressbook(s)">
					Remove all birthdays
				</button>
			</div>
			<div class="controls-right">
				<div class="controls_item button tooltipped-bottom" id="syncstatus"
					 title="Only the contacts previously matched will be used here">Loading...</div>
			</div>
		</div>
		<div id="loader" class="hidden">
			<div class="spinner">
				<div class="rect1"></div>
				<div class="rect2"></div>
				<div class="rect3"></div>
				<div class="rect4"></div>
				<div class="rect5"></div>
			</div>
			<div id="loading-status">Syncing...</div>
		</div>
		
		<!-- Fake contact div to load the contact svg before the contacts pictures -->
		<div class="sync-contact" style="display:none"></div>
		
		<div id="contacts-list-results" style="display:none">
			<div id="sync-success" class="clear sync-results-container"><h2>Success</h2><div class="sync-results"></div></div>
			<div id="sync-errors" class="clear sync-results-container"><h2>Errors</h2><div class="sync-results"></div></div>
			<div id="sync-ignored" class="clear sync-results-container"><h2>Ignored</h2><div class="sync-results"></div></div>
		</div>
		
	</div>
	
</div>
