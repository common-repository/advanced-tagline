<?php
/*
	Copyright 2008	Kevin Morey	(email : kevin@kmorey.net)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA	02110-1301	USA
*/
?>
<div id="advtag">
	<h2>Advanced Tagline <?php print ADVTAG_VERSION; ?></h2>
	
	<?php if (!empty($message)) { ?>
	<div id="message" class="<?php if($error) { print "error"; } else { print "updated"; }?>">
		<?php echo $message; ?>
	</div>
	<?php } ?>
	
	<h3>Display Mode</h3>
	<p>There are three ways you can display the taglines from Advanced Tagline.</p>
	<h4>Standard</h4>
	<p class="subhead">Replaces <code>bloginfo('description')</code> which is what WordPress uses by default to display the single tagline.</p>
	<br />
	
	<h4>Advanced</h4>
	<p class="subhead">Will not replace the single tagline, but may be called in your theme with <code>&lt;php bloginfo('advtag'); ?&gt;</code> or <code>&lt;php advtag_get_tagline(); ?&gt;</code></p>
	<br />
	
	<h4>Widget</h4>
	<p class="subhead">The widget is always active and can be added from Appearance > Widgets.</p>
	<br />
	
	<h3>Display Method</h3>
	<p>Currently, there are two methods for how the taglines should be displayed.</p>
	<h4>Sequential</h4>
	<p class="subhead">Each tagline will be displayed in order, with the next one displaying when the page refreshes</p>
	<br />
	
	<h4>Random</h4>
	<p class="subhead">A tagline will be randomly selected and displayed with each page refresh</p>
	<br />

	<form id="form-options" action="javascript:;">
		<input type="hidden" name="action" value="advtag_save_options" />
		<h3>Options</h3>
		<h4>Display Mode</h4>
		<label for="mode_default"><input id="mode_default" type="radio" name="advtag_mode" value="default" <? if($advtag_mode === 'default') { print " checked=\"checked\" "; } ?> />Standard</label>
		<label for="mode_standalone"><input id="mode_standalone" type="radio" name="advtag_mode" value="standalone" <? if($advtag_mode === 'standalone') { print " checked=\"checked\" "; } ?> />Advanced</label>
		<br />
		<h4>Display Method</h4>
		<label for="sequential"><input id="sequential" type="radio" name="advtag_type" value="sequential" <? if($advtag_type == "sequential") { print " checked=\"checked\" "; } ?> />Sequential</label>
		<label for="random"><input id="random" type="radio" name="advtag_type" value="random" <? if($advtag_type != "sequential") { print " checked=\"checked\" "; } ?> />Random</label>
		<br />
		<input class="button" type="submit" name="save" value="<?php _e('Save Changes', 'advtag') ?>" />
		<img class="throbber" src="ajax-loader.gif" />
		<span class="message" style="display:none;">Success!</span>
	</form>
	
	<input id='btn-add-tagline' alt="#TB_inline?height=200&width=450&inlineId=add-tagline" title="Add a New Tagline" class="button-primary thickbox" type="button" value="Add a New Tagline" />  
	
	<div id="add-tagline">
		<input type="hidden" id="add_index" value="-1" />
		<label class="add_label" for="add_text">Text</label>
		<input type="text" id="add_text" size="50" />
		<label class="add_label" for="add_link">Link</label>
		<input type="text" id="add_link" size="50" />
		<label class="add_label" for="add_target">Target</label>
		<input type="text" id="add_target" size="10" />
		<input id="btn-save-tag" class="button" type="button" value="Save" onclick="advtag_saveTagline();" />
		<img class="throbber" src="ajax-loader.gif" />
	</div>

	<h3>Active Taglines</h3>
	<p id="no-active-taglines">There are no active taglines.</p>
	<table id='active_taglines' cellpadding="0"></table>
	
	<h3>Import</h3>
<!-- 	<p>NOTE: Be sure to save your changes before doing a batch import. It will not save for you.</p> -->
	<form enctype="multipart/form-data" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
		<!-- MAX_FILE_SIZE must precede the file input field -->
		<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
	
		<label for="import_replace"><input id="import_replace" type="radio" name="import_type" value="replace" />Replace Existing</label>
		<label for="import_append"><input id="import_append" type="radio" name="import_type" value="append" checked="checked" />Append to Existing</label>
		<br />
		<!-- Name of input element determines name in $_FILES array -->
		<input name="importfile" type="file" /></label>
		<br />
		<input class="button" type="submit" name="import" value="Perform Import" />
	</form>
	
	<h3>Export</h3>
	<form action="/wp-admin/admin-ajax.php" method="post">
		<input type="hidden" name="action" value="advtag_export" />
<!-- 	<p>NOTE: Be sure to save your changes before doing a batch import. It will not save for you.</p> -->
		<label for="export_csv" value="CSV"><input id="export_csv" type="radio" name="export_type" value="csv" />CSV</label><br />
		
		<input class="button" type="submit" name="export" value="Export" />
	</form>

</div>
