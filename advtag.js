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

var advtag_pluginDir = '/wp-content/plugins/advanced-tagline/';
var advtag_blogHome = '/';
var advtag_ajaxUrl = '/wp-admin/admin-ajax.php';

//var jQuery = jQuery.noConflict();

var STR_REACTIVATE_TAGLINE = "Re-activate Tagline";
var STR_EDIT_TAGLINE = "Edit Tagline";
var STR_REMOVE_TAGLINE = "Remove Tagline";

var ID_ACTIVE = '#active_taglines';

function advtag_saveTagline() {
	var index = jQuery('#add_index').val();
	var textElem = jQuery('#add_text');
	var linkElem = jQuery('#add_link');
	var targetElem = jQuery('#add_target');

	index = parseInt(index);
	
	jQuery('#add-tagline img.throbber').show();
	var div = null;
	if (index != -1) {
		div = jQuery(ID_ACTIVE).find('tr.tagline').eq(index);
	}

	jQuery.ajax({
		url: advtag_ajaxUrl,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'advtag_save',
			index: index,
			text: textElem.val(),
			link: linkElem.val(),
			target: targetElem.val()
		},
		success: function(data){
			console.log(data);
			if (index == -1) {
				var e = advtag_newTagline(textElem.val(), linkElem.val(), targetElem.val());
			}
			else {
				var e = advtag_buildTagDiv(textElem.val(), linkElem.val(), targetElem.val());
				div.replaceWith(e);
				advtag_updateButtons();
			}
			
			jQuery('#add_index').val(-1);
			textElem.val("");
			linkElem.val("");
			targetElem.val("");

			jQuery('div#add-tagline').hide();
			advtag_scrollTo(e);
		},
		complete: function(data) {
			jQuery('#add-tagline img').hide();
		}
	});
}

function advtag_editTag(button)
{
	var div = jQuery(button).parent().parent();

	var text, link, target;

	if (div.find('a').length == 0) {
		//text only
		text = div.find('p').eq(0).html();
	}
	else {
		var a = div.find('a').eq(0);
		text = a.html();
		link = a.attr('href');
		target = a.attr('target');
	}

	var index = advtag_getIndex(div);

	jQuery('#add_index').val(index);
	jQuery('#add_text').val(text);
	jQuery('#add_link').val(link);
	jQuery('#add_target').val(target);

/* 	jQuery('#btn-add-tagline').click(); */

	jQuery('div#add-tagline').slideDown();
	advtag_scrollTo('div#add-tagline');

	advtag_switchToEdit();
}

function advtag_scrollTo(e) {
	var destination = jQuery(e).offset().top;
	jQuery('html,body').animate(
		{scrollTop: destination},
		400
	);
}

function advtag_getIndex(div) {

	var index = -1;
	jQuery(ID_ACTIVE).find('tr.tagline').each(function(i, val) {
		if (jQuery(val).get(0) == div.get(0)) { index = i; return false; }
	});

	return index;
}

function advtag_switchToEdit() {
	jQuery('#TB_ajaxWindowTitle').html('Edit Tagline');
}

function advtag_newTagline(text, link, target) {

	var e = advtag_buildTagDiv(text,link,target);

	jQuery(ID_ACTIVE).append(e);

	advtag_updateButtons();
	
	return e;
}

function advtag_buildTagDiv(text, link, target) {

	//TODO: create a class for the tagline elements
	var e = jQuery('<tr class="tagline">'+
	'<td><input type="hidden" name="id" value="" /></td>'+
	'<td><input type="button" class="button" title="Move Up" value="Up" /></td>'+
	'<td><input type="button" class="button" title="Move Down" value="Down" /></td>'+
	'<td class="taglink">'+advtag_buildTagline(text, link, target)+'</td>'+
	'<td><input type="button" class="button" title="'+STR_EDIT_TAGLINE+'" value="Edit" /></td>'+
	'<td><input type="button" class="button" title="'+STR_REMOVE_TAGLINE+'" value="Remove" /></td>'+
	'<td><img class="throbber" src="'+advtag_pluginDir+'/ajax-loader.gif" /></td>'+
	'</tr>');

	//var newElem = jQuery(e);

	e.find('input[value=Up]').click(function(event) { advtag_moveUp(this); });
	e.find('input[value=Down]').click(function() { advtag_moveDown(this); });
	e.find('input[value=Edit]').click(function() { advtag_editTag(this); });
	e.find('input[value=Remove]').click(function() { advtag_removeTag(this); });

	return e;
}

function advtag_buildTagline(text, link, target) {
	var str = '';//<div class='tagline'>";

	if (link == "") {
		str += '<p>'+text+'</p>';
	}
	else {
		str += "<a href='"+link+"'";
		if (target != "") {
			str += " target='"+target+"'";
		}
		str += ">"+text+"</a>";
	}

/* 	str += "</div>"; */

	return str;
}

function advtag_removeTag(sender) {

	if (confirm("Removing a tagline cannot be undone!\n\nAre you sure you want to remove this tagline?") !== true)
	{
		return;
	}
	
	
	var div = jQuery(sender).parent().parent();
	var id = div.find('input[name=id]').val();

	div.find('img').show();

	jQuery.ajax({
		url: advtag_ajaxUrl,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'advtag_remove',
			index: id
		},
		success: function(data){
			div.fadeOut(500, function()
			{
				div.remove();
		
				advtag_updateButtons();
			});
		},
		complete: function() {
			div.find('img').hide();
		}
	});
}

function advtag_updateButtons() {
	var e = jQuery(ID_ACTIVE);
	e.find('tr.tagline').removeClass('odd').each(advtag_updateRowButtons);
	e.find('tr.tagline:odd').addClass('odd');
	
	var count = e.find('tr.tagline').length;
	if (count > 0)
	{
		jQuery('#no-active-taglines').hide();
	}
	else
	{
		jQuery('#no-active-taglines').show();
	}
}

function advtag_updateRowButtons(index, elem) {

	elem = jQuery(elem);

	//set sequence
	elem.find('input[name=id]').val(index);

	//TODO: find a better way to get the buttons
	var children = elem.find('input[type=button]');
	var moveUpBtn = children.get(0);
	var moveDownBtn = children.get(1);
	var removeBtn = children.get(3);

	if (elem.prev('tr.tagline').length == 0) {
		moveUpBtn.disabled = true;
	} else {
		moveUpBtn.disabled = false;
	}

	if (elem.next('tr.tagline').length == 0) {
		moveDownBtn.disabled = true;
	} else {
		moveDownBtn.disabled = false;
	}
}

function advtag_moveUp(sender) {

	var div = jQuery(sender).parent().parent();
	var id = div.find('input[name=id]').val();

	div.find('img').show();

	jQuery.ajax({
		url: advtag_ajaxUrl,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'advtag_moveUp',
			index: id
		},
		success: function(data){
			var elem = div;
			var prev = elem.prev();

			elem.after(prev);

			advtag_updateButtons();
		},
		complete: function() {
			div.find('img').hide();
		}
	});
}

function advtag_moveDown(sender) {

	var div = jQuery(sender).parent().parent();
	var id = div.find('input[name=id]').val();

	div.find('img').show();

	jQuery.ajax({
		url: advtag_ajaxUrl,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'advtag_moveDown',
			index: id
		},
		success: function(data){
			var elem = div;
			var next = elem.next();

			elem.before(next);

			advtag_updateButtons();
		},
		complete: function() {
			div.find('img').hide();
		}
	});
}

jQuery(function() {
	jQuery('#form-options').submit(function(){
	
		var form = jQuery(this);
		
		form.find('img').show();
		
		jQuery.ajax({
			url: advtag_ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: form.find(':input').serialize(),
			success: function() {
				
				form.find('img').hide();
				
				form.find('span.message').show();
				
				setTimeout('jQuery("#form-options span.message").fadeOut("slow")', 1000);
			}
		});
		
		return false;
	});
	
	// load taglines
	jQuery.ajax({
		url: advtag_ajaxUrl,
		type: 'POST',
		dataType: 'json',
		data: {action: 'advtag_fetch_taglines'},
		success: function(data) {
			for(var i = 0; i < data.length; i++)
			{
				var item = data[i];
				advtag_newTagline(item[0], item[1], item[2]);
			}
		}
	});
	
	jQuery('#btn-add-tagline').click(function() {
		jQuery('div#add-tagline')
			.find('input[type=text]').val('').end()
			.find('input[type=hidden]').val(-1).end()
			.slideDown();
		advtag_scrollTo('div#add-tagline');
	});
});

function advtag_focus_form()
{
	//need to pause to let the form be "focusable"? weird.
	setTimeout("jQuery('#add_text').focus()", 100);
}

jQuery(function() {
	//fix throbbers
	jQuery('.throbber').attr('src', advtag_pluginDir+'/ajax-loader.gif');
});
