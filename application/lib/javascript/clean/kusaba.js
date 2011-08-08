var style_cookie;
var style_cookie_txt;
var style_cookie_site;
var kumod_set = false;
var quick_reply = false;
var ispage;
var lastid;

/* IE/Opera fix, because they need to go learn a book on how to use indexOf with arrays */
if (!Array.prototype.indexOf) {
  Array.prototype.indexOf = function(elt /*, from*/) {
	var len = this.length;

	var from = Number(arguments[1]) || 0;
	from = (from < 0)
		 ? Math.ceil(from)
		 : Math.floor(from);
	if (from < 0)
	  from += len;

	for (; from < len; from++) {
	  if (from in this &&
		  this[from] === elt)
		return from;
	}
	return -1;
  };
}

/**
*
*  UTF-8 data encode / decode
*  http://www.webtoolkit.info/
*
**/

var Utf8 = {

	// public method for url encoding
	encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	},

	// public method for url decoding
	decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;

		while ( i < utftext.length ) {

			c = utftext.charCodeAt(i);

			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}

		}

		return string;
	}

};

var gt = new Gettext({ 'domain' : 'kusaba' });
function _ (msgid) { return gt.gettext(msgid); }

function toggle(button, area) {
	$('#' + area).slideToggle("normal", function() {
		$(button).html( $(this).is(":hidden") ? '+':'&minus;');
		set_cookie('nav_show_'+area,$(this).is(":hidden")?'0':'1', 30);
	});
}
function removeframes() {
	$('a.boardlink').attr("target","_top");
	$("#removeframes").html(_("Frames removed")+".");
	return false;
}
function reloadmain() {
	if (parent.main) {
		parent.main.location.reload();
	}
}

function replaceAll( str, from, to ) {
	var idx = str.indexOf( from );
	while ( idx > -1 ) {
		str = str.replace( from, to );
		idx = str.indexOf( from );
	}
	return str;
}

function insert(text) {
	if(!ispage || quick_reply) {
		var textarea=document.forms.postform.message;
		if(textarea) {
			if(textarea.createTextRange && textarea.caretPos) { // IE 
				var caretPos=textarea.caretPos;
				caretPos.text=caretPos.text.charAt(caretPos.text.length-1)==" "?text+" ":text;
			} else if(textarea.setSelectionRange) { // Firefox 
				var start=textarea.selectionStart;
				var end=textarea.selectionEnd;
				textarea.value=textarea.value.substr(0,start)+text+textarea.value.substr(end);
				textarea.setSelectionRange(start+text.length,start+text.length);
			} else {
				textarea.value+=text+" ";
			}
			textarea.focus();

			return false;
		}
	}
	return true;
}

function checkhighlight() {
	var match;

	if(match=/#i([0-9]+)/.exec(document.location.toString()))
	if(!document.forms.postform.message.value)
	insert(">>" + match[1] + "\n");

	if(match=/#([0-9]+)/.exec(document.location.toString()))
	highlight(match[1]);
}

function highlight(post, checknopage) {

	if ((checknopage && ispage) || ispage) {
		// Uncomment the following line to always send the user to the thread if the link was clicked on the board page.
		//return;
	}

	$("td.highlight").removeClass().addClass("reply");

	var reply = $("#reply" + post);
	var replytable = reply.parents("table");
	if((reply.length ||  $('#postform [name=replythread]').val() == post) && replytable.parent().attr("class") != "reflinkpreview") {
		if(reply.length) {
			reply.removeClass();
			reply.addClass("highlight");
		}
		var match = /^([^#]*)/.exec(document.location.toString());
		document.location = match[1] + "#" + post;
		return false;
	}
	
	return true;
}

function get_password(name) {
	var pass = getCookie(name);
	if(pass) return pass;

	var chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	var pass='';

	for(var i=0;i<8;i++) {
		var rnd = Math.floor(Math.random()*chars.length);
		pass += chars.substring(rnd, rnd+1);
	}
	set_cookie(name, pass, 365);
	return(pass);
}

function togglePassword() {
	$("#passwordbox").html('<td class="postblock">Mod</td><td><input type="text" name="modpassword" size="28" maxlength="75">&nbsp;<acronym title="Display staff status (Mod/Admin)">D</acronym>:&nbsp;<input type="checkbox" name="displaystaffstatus" checked>&nbsp;<acronym title="Lock">L</acronym>:&nbsp;<input type="checkbox" name="lockonpost">&nbsp;&nbsp;<acronym title="Sticky">S</acronym>:&nbsp;<input type="checkbox" name="stickyonpost">&nbsp;&nbsp;<acronym title="Raw HTML">RH</acronym>:&nbsp;<input type="checkbox" name="rawhtml">&nbsp;&nbsp;<acronym title="Name">N</acronym>:&nbsp;<input type="checkbox" name="usestaffname"></td>');
	return false;
}

function toggleOptions(threadid, formid, board) {
	
	if ($('#opt' + threadid).length) {
		if (!$('#opt' + threadid).is(":hidden")) {
			$('#opt' + threadid).toggle();
			$('#opt' + threadid).html();
		} else {
			var newhtml = '<td class="label"><label for="formatting">Formatting:</label></td><td colspan="3"><select name="formatting"><option value="" onclick="$(\'#formattinginfo' + threadid + '\').html(_(\'All formatting is performed by the user.\'));">Normal</option><option value="aa" onclick="$(\'#formattinginfo' + threadid + '\').html(_(\'[aa] and [/aa] will surround your message.\'));"';
			if (getCookie('kuformatting') == 'aa') {
				newhtml += ' selected';
			}
			newhtml += '>Text Art</option></select> <input type="checkbox" name="rememberformatting"><label for="rememberformatting">Remember</label> <span id="formattinginfo' + threadid + '">';
			if (getCookie('kuformatting') == 'aa') {
				newhtml += '[aa] and [/aa] will surround your message.';
			} else {
				newhtml += 'All formatting is performed by the user.';
			}
			newhtml += '</span></td><td><input type="button" value="Preview" class="submit" onclick="javascript:postpreview(\'preview' + threadid + '\', \'' + board + '\', \'' + threadid + '\', document.' + formid + '.message.value);"></td>';

			$('#opt' + threadid).html(newhtml);
			$('#opt' + threadid).toggle();
		}
	}
}

function getCookie(name) {
	with(document.cookie) {
		var regexp=new RegExp("(^|;\\s+)"+name+"=(.*?)(;|$)");
		var hit=regexp.exec(document.cookie);
		if(hit&&hit.length>2) return Utf8.decode(unescape(replaceAll(hit[2],'+','%20')));
		else return '';
	}
}

function set_cookie(name,value,days) {
	if(days) {
		var date=new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires="; expires="+date.toGMTString();
	} else expires="";
	document.cookie=name+"="+value+expires+"; path=/";
}
function del_cookie(name) {
	document.cookie = name +'=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/';
} 

function set_stylesheet(styletitle, txt, site) {
	if (txt) {
		if (styletitle == get_default_stylesheet())
			del_cookie("kustyle_txt");
		else
			set_cookie("kustyle_txt",styletitle,365);
	} else if (site) {
		if (styletitle == get_default_stylesheet())
			del_cookie("kustyle_site");
		else
			set_cookie("kustyle_site",styletitle,365);
	} else {
		if (styletitle == get_default_stylesheet())
			del_cookie("kustyle");
		else
			set_cookie("kustyle",styletitle,365);
	}
	var found = false;
	$('link[rel$=stylesheet][title]').each(function(i)
	{
		this.disabled = true;
		if (this.getAttribute('title') == styletitle) { this.disabled = false; found = true;}
	});

	if(!found) set_preferred_stylesheet();
}

function set_preferred_stylesheet() {
	$('link[rel=stylesheet][title]')[0].disabled = false;
}

function get_active_stylesheet() {
	styles = $('link[rel$=stylesheet][title]');
	for (var i = 0, len = styles.length; i < len; i++){
		if (!styles[i].disabled) return styles[i].title;
	}
	return null;
}

function get_default_stylesheet() {
	style = $('link[rel=stylesheet][title]');
	if (style.length) {
		return style.attr('title');
	}
	return null;
}

function delandbanlinks(context) {
	if (!kumod_set) return;
	if (!context){
		togglePassword();
		var bottombox = $("#fileonly");
		bottombox.length && bottombox.parent().html('[<input type="checkbox" name="fileonly" id="fileonly" value="on" /><label for="fileonly">File Only</label>] <input name="moddelete" onclick="return confirm(_(\'Are you sure you want to delete these posts?\'))" value="'+_('Delete')+'" type="submit" /> <input name="modban" value="'+_('Ban')+'" onclick="this.form.action=\''+ ku_cgipath + '/manage_page.php?action=bans\';" type="submit" />');
	}
	var newhtml;
	var dnbelements = $("span[id^='dnb']", context).each(
		function (i) {
			dnbinfo = $(this).attr("id").split('-');
			$.get(ku_boardspath + "/manage_page.php?action=getip&boarddir=" + dnbinfo[1] + "&id=" + dnbinfo[2],{},
				function (responseText, textStatus) {
					ipaddr = responseText.split("=") || "what are you doing get out you don't even fit";
					$("#"+ipaddr[0]).prepend("[IP: "+ipaddr[1]+" <a href=\"" + ku_boardspath + "/manage_page.php?action=deletepostsbyip&ip="+ipaddr[1]+"\" title=\"" + _('Delete all posts by this IP') + "\">D</a> / <a href=\"" + ku_boardspath + "/manage_page.php?action=ipsearch&ip="+ipaddr[1]+"\" title=\"" + _('Search for posts with this IP') + "\">S</a>] ");
				});
			newhtml = '&#91;<a href="' + ku_cgipath + '/manage_page.php?action=delposts&boarddir=' + dnbinfo[1] + '&del';
			if (dnbinfo[3] == 'y') {
				newhtml += 'thread';
			} else {
				newhtml += 'post';
			}
			newhtml += 'id=' + dnbinfo[2] + '" title="' + _('Delete') + '" onclick="return confirm(_(\'Are you sure you want to delete this post/thread?\'));">D<\/a>&nbsp;<a href="' + ku_cgipath + '/manage_page.php?action=delposts&boarddir=' + dnbinfo[1] + '&del';
			if (dnbinfo[3] == 'y') {
				newhtml += 'thread';
			} else {
				newhtml += 'post';
			}
			newhtml += 'id=' + dnbinfo[2] + '&postid=' + dnbinfo[2] + '" title="' + _('Delete &amp; Ban') + '" onclick="return confirm(_(\'Are you sure you want to delete and ban the poster of this post/thread?\'));">&amp;<\/a>&nbsp;<a href="' + ku_cgipath + '/manage_page.php?action=bans&banboard=' + dnbinfo[1] + '&banpost=' + dnbinfo[2] + '" title="' + _('Ban') + '">B<\/a>&#93;&nbsp;&#91;<a href="' + ku_cgipath + '/manage_page.php?action=bans&banboard=' + dnbinfo[1] + '&banpost=' + dnbinfo[2] + '&instant=y" title="' +  _('Instant Permanent Ban') + '" onclick="instantban(\'' + dnbinfo[1] + '\',' + dnbinfo[2] + '); return false;">P<\/a>&#93;&nbsp;&#91;<a href="' + ku_cgipath + '/manage_page.php?action=delposts&boarddir=' + dnbinfo[1] + '&del';
			if (dnbinfo[3] == 'y') {
				newhtml += 'thread';
			} else {
			newhtml += 'post';
			}
			newhtml += 'id=' + dnbinfo[2] + '&postid=' + dnbinfo[2] + '&cp=y" title="' + _('Child Pornography') + '" onclick="return confirm(_(\'Are you sure that this is child pornography?\'));">CP<\/a>&#93;';		
			$(this).html(newhtml);
		});			
}

function instantban(boardid, postid) {
	var reason = prompt(_('Are you sure you want to permenently ban the poster of this post/thread?\nIf so enter a ban message or click OK to use the default ban reason. To cancel this operation, click "Cancel".'));
	if (typeof reason === 'string') {
		var url = ku_cgipath + '/manage_page.php?action=bans&banboard=' + boardid + '&banpost=' + postid + '&instant=y';
		if (reason != '') {
			url += '&reason=' + reason;
		}
		$.get(url,{},
			function(ban, status) {
				if (ban == "success")
					alert(_("Ban was sucessful."));
				else
					alert(_("Ban failed!"));
				if (status != "success")
					alert(_("Ban failed!"));
			});
	}
	else {
		alert(_("OK, no action taken."));
	}	
}

function togglethread(threadid) {
	if (hiddenthreads.toString().indexOf(threadid)!==-1) {
		$('#unhidethread' + threadid).toggle();
		$('#thread' + threadid).toggle("thread");
		hiddenthreads.splice(hiddenthreads.indexOf(threadid),1);
		set_cookie('hiddenthreads',hiddenthreads.join('!'),30);
	} else {
		$('#thread' + threadid).toggle();
		$('#unhidethread' + threadid).toggle();
		hiddenthreads.push(threadid);
		set_cookie('hiddenthreads',hiddenthreads.join('!'),30);
	}
	return false;
}

function toggleblotter(save) {
	$(".blotterentry").slideToggle("normal", 
	function(){
		if(save) {
			set_cookie('ku_showblotter',$(this).is(":hidden")?'0':'1', 365);
		}
	});
}

function expandthread(threadid, board) {
	var repliesblock = $("#replies" + threadid + board);
	if (repliesblock.length) {
		repliesblock.prepend(_('Expanding thread') + '...<br /><br />');
		repliesblock.load(ku_boardspath+'/ajax.php?act=expand&board=' + board + '&thread=' + threadid,{},
			function (responseText, textStatus) {
				if (!responseText) {
					repliesblock.html(_("something went wrong (blank response)"));
				}
				if (textStatus == "error") {
					alert(_('Something went wrong...'));
				}
				delandbanlinks(this);
				addpreviewevents(this);
			}
		);
	}
	return false;
}

function quickreply(threadid) {
	if (threadid == 0) {
		quick_reply = false;
		$('#posttypeindicator').html('new thread');
	} else {
		quick_reply = true;
		$('#posttypeindicator').html('reply to ' + threadid + ' [<a href="#postbox" onclick="javascript:quickreply(\'0\');" title="Cancel">x</a>]');
	}
	$('#postform [name=replythread]').val(threadid);
}

function startPostSpyTimeout(threadid, board, thelastid) {
        var postspy = getCookie('postspy');
        if (postspy == '1') {
                if ($('#thread' + threadid + board).length) {
                        lastid = thelastid;
                        setTimeout('postSpy(' + threadid + ', "' + board + '");', 10000);
                }
        }
}

function postSpy(threadid, board) {
	var threadblock = $("#thread" + threadid + board);
	if (threadblock.length) {
		$.get(ku_boardspath+'/ajax.php?act=spy&board=' + board + '&thread=' + threadid + '&pastid=' + lastid,{},
			function (responseText, textStatus) {
				if(textStatus != "success") {
					alert(_('Something went wrong...'));
				}
				else {
					var response_split = responseText.split('|');
					newlastid = response_split[0];
					if (newlastid != '') {
							responseText = responseText.substr((newlastid.length + 1));
							oldpost = $("#spy"+lastid);
							if (!oldpost.length) {
								oldpost = threadblock.children("table:last");
							}
							if (!oldpost.length) {
								oldpost = threadblock;
							}
							oldpost.after($("<div id=\"spy"+newlastid+"\">"+responseText+"</div>").hide());
							lastid = newlastid;
							newpost = $("#spy"+lastid);
							newpost.slideToggle("normal");
							addpreviewevents(newpost);
							delandbanlinks(newpost);
					}
				   
					setTimeout('postSpy(' + threadid + ', "' + board + '");', 5000);
				}
			}
		);
	}
}

function getwatchedthreads(threadid, board) {
	var watchedthreadbox = $("#watchedthreadlist");
	if (watchedthreadbox.length) {
		watchedthreadbox.html(_('Loading watched threads...'));
		watchedthreadbox.load(ku_boardspath + '/ajax.php?act=threadwatch&board=' + board + '&thread=' + threadid,{},
			function (responseText, textStatus) {
				if (!responseText) {
					watchedthreadbox.html(_("something went wrong (blank response)"));
				}
				if (textStatus == "error") {
					alert(_('Something went wrong...'));
				}
				$("#watchedthreads").css("min-height", ($("#watchedthreadlist").attr('offsetHeight')+$("#watchedthreadsdraghandle").attr('offsetHeight')+$("#watchedthreadsbuttons").attr('offsetHeight')));
			});
	}
}

function addtowatchedthreads(threadid, board) {
	var watchedthreadbox = $("#watchedthreadlist");
	if (watchedthreadbox.length) {
		$.get(ku_boardspath + '/ajax.php?act=threadwatch&do=addthread&board=' + board + '&thread=' + threadid,{},
			function (responseText, textStatus) {
					if(textStatus == "success") {
						alert(_('Thread successfully added to your watch list.'));
						getwatchedthreads('0', board);
					}
					else {
						alert(_('Something went wrong...'));
					}
			});
	}
}

function removefromwatchedthreads(threadid, board) {
	var watchedthreadbox = $("#watchedthreadlist");
	if (watchedthreadbox.length) {
		$.get(ku_boardspath + '/ajax.php?act=threadwatch&do=removethread&board=' + board + '&thread=' + threadid,{},
			function (responseText, textStatus) {
					if(textStatus == "success") {
						getwatchedthreads('0', board);
					}
					else {
						alert(_('Something went wrong...'));
					}
			});
	}
}

function hidewatchedthreads() {
	set_cookie('showwatchedthreads','0',30);
	$("#watchedthreads").fadeOut("slow", function(){$(this).remove()});
	
}

function showwatchedthreads() {
	set_cookie('showwatchedthreads','1',30);
	window.location.reload(true);
}

function togglePostSpy() {
        var postspy = getCookie('postspy');
        if (postspy == '1') {
                set_cookie('postspy', '0', 30);
                alert('Post Spy disabled.  Any pages loaded from now on will not utilize the Post Spy feature.');
        } else {
                set_cookie('postspy', '1', 30);
                alert('Post Spy enabled.  Any pages loaded from now on will utilize the Post Spy feature.');
        }
}

function checkcaptcha(formid) {
	if ($("#"+formid+" [name=captcha]").length && $("#"+formid+" [name=captcha]").val() == '') {
		alert('Please enter the captcha image text.');
		$("#"+formid+" [name=captcha]").focus();
		return false;
	}
	
	return true;
}

function expandimg(postnum, imgurl, thumburl, imgw, imgh, thumbw, thumbh) {
	var img = $("#thumb" + postnum + " img:first-child");
	var parent = $("#thumb" + postnum);
	if(img.attr("src") == thumburl) {
		parent.html('<img src="'+ imgurl +'" alt="'+ postnum +'" class="'+ img.attr("class") +'" height="'+ imgh +'" width="'+imgw +'">');
	} else if(img.attr("src") == imgurl) {
		parent.html('<img src="'+ thumburl +'" alt="'+ postnum +'" class="'+ img.attr("class") +'" height="'+ thumbh +'" width="'+ thumbw +'">');
	}
}

function postpreview(divid, board, parentid, message) {
	var previewdiv = $("#" + divid);
	if (previewdiv.length) {
		previewdiv.load(ku_boardspath + '/ajax.php?act=preview&board=' + board + '&thread=' + parentid + '&message=' + escape(message),{},
		function (responseText, textStatus) {
			if (!responseText) {
				previewdiv.html(_("something went wrong (blank response)"));
			}
		});
	}
}

function set_inputs(id) {
	if ($("#" + id).length) {
		if(!$('#' + id + ' [name=name]').val()) $('#' + id + ' [name=name]').val(getCookie("name"));
		if(!$('#' + id + ' [name=em]').val()) $('#' + id + ' [name=em]').val(getCookie("email"));
		if(!$('#' + id + ' [name=postpassword]').val()) $('#' + id + ' [name=postpassword]').val(get_password("postpassword"));
	}
}

function set_delpass(id) {
	if ($("#" + id).length) {
		if(!$('#' + id + ' [name=postpassword]').val()) $('#' + id + ' [name=postpassword]').val(get_password("postpassword"));
	}
}

function addpreviewevents(context) {

	$("a[class^='ref|']", context).mouseenter(
	function(e){
		var ainfo = $(this).attr("class").split('|');
		var previewelement = $("<div></div>").addClass('reflinkpreview').attr({
				id: "preview" + $(this).attr("class"),
				style: "left:" + (e.pageX + 50) + "px;display:none"});
		if ( $('#postform [name=board]').val() == ainfo[1] && $('#reply' + ainfo[3]).length && (($('#thumb' + ainfo[3]).length && $('#thumb' + ainfo[3] + " img:first-child").attr("src").lastIndexOf("thumb") != -1) || !$('#thumb' + ainfo[3]).length)) {
			var isonpage = true;
			previewelement.html($("#reply" + ainfo[3]).parents("table").html());
		}
		else {
			$.get(ku_boardspath + '/ajax.php?act=read&board=' + ainfo[1] + '&thread=' + ainfo[2] + '&post=' + ainfo[3] + '&single',{},
				function (responseText, textStatus) {
					if(textStatus != "success") {
						alert('wut');
					}
					else {
						if (responseText) {
							previewelement.html(responseText).toggle("normal");
						}
						else {
							previewelement.html(_("something went wrong (blank response)")).toggle("normal");
						}
					}
				});
		}
		previewelement.insertBefore($(this));
		if(isonpage) previewelement.toggle("normal");
	}).mouseleave(
	function(e){
		var previewelement = ($("div [id='preview"+$(this).attr("class")+"']"));
		if (previewelement.length) {
			previewelement.remove();
		}
	}).click(
	function(e){
		var ainfo = $(this).attr("class").split('|');
		return highlight(ainfo[3], true);
	});
}

$(document).ready(function(){
    if (getCookie("kumod") == "allboards") {
        kumod_set = true
    }
    else if(getCookie("kumod") != "") {
        var listofboards = getCookie("kumod").split('|');
        var thisboard = $("#postform [name=board]").val();
        for (var cookieboard in listofboards) {
            if (listofboards[cookieboard] == thisboard) {
                kumod_set = true;
                break
            }
        }
    }
	
	delandbanlinks();
	addpreviewevents();
	checkhighlight();
	
	if ($('#watchedthreads').length) {
		$('#watchedthreads').draggable({
				handle: '#watchedthreadsdraghandle',
				opacity: 0.7,
   				stop: function() {
					set_cookie('watchedthreadstop',$(this).css('top'),30);
					set_cookie('watchedthreadsleft',$(this).css('left'),30);
				}});
		$('#watchedthreads').resizable({
				minHeight: ($("#watchedthreadlist").attr('offsetHeight')+$("#watchedthreadsdraghandle").attr('offsetHeight')+$("#watchedthreadsbuttons").attr('offsetHeight')),
				stop: function() {
					set_cookie('watchedthreadswidth',$(this).width(),30);
					set_cookie('watchedthreadsheight',$(this).height(),30);
				}});
		
	}
	$(this).keydown(function(e){
		if (e.altKey) {
			var docloc = document.location.toString();
			if ((docloc.indexOf('catalog.html') == -1 && docloc.indexOf('/res/') == -1) || (docloc.indexOf('catalog.html') == -1 && e.keyCode == 80)) {
				if (e.keyCode != 18 && e.keyCode != 16) {
					if (docloc.indexOf('.html') == -1 || docloc.indexOf('board.html') != -1) {
						var page = 0;
						var docloc_trimmed = docloc.substr(0, docloc.lastIndexOf('/') + 1);
					} else {
						var page = docloc.substr((docloc.lastIndexOf('/') + 1));
						page = (+page.substr(0, page.indexOf('.html')));
						var docloc_trimmed = docloc.substr(0, docloc.lastIndexOf('/') + 1);
					}
					if (page == 0) {
						var docloc_valid = docloc_trimmed;
					} else {
						var docloc_valid  = docloc_trimmed + page + '.html';
					}
					
					if (e.keyCode == 222 || e.keyCode == 221) {
						if(match=/#s([0-9])/.exec(docloc)) {
							var relativepost = (+match[1]);
						} else {
							var relativepost = -1;
						}
						
						if (e.keyCode == 222) {
							if (relativepost == -1 || relativepost == 9) {
								var newrelativepost = 0;
							} else {
								var newrelativepost = relativepost + 1;
							}
						} else if (e.keyCode == 221) {
							if (relativepost == -1 || relativepost == 0) {
								var newrelativepost = 9;
							} else {
								var newrelativepost = relativepost - 1;
							}
						}
						
						document.location.href = docloc_valid + '#s' + newrelativepost;
					} else if (e.keyCode == 59 || e.keyCode == 219) {
						if (e.keyCode == 59) {
							page = page + 1;
						} else if (e.keyCode == 219) {
							if (page >= 1) {
								page = page - 1;
							}
						}
						
						if (page == 0) {
							document.location.href = docloc_trimmed;
						} else {
							document.location.href = docloc_trimmed + page + '.html';
						}
					} else if (e.keyCode == 80) {
						document.location.href = docloc_valid + '#postbox';
					}
				}
			}
		}
	});
});

if(style_cookie) {
	var cookie = getCookie(style_cookie);
	var title = cookie ? cookie : get_default_stylesheet();

	if (title != get_active_stylesheet())
		set_stylesheet(title);
}

if(style_cookie_txt) {
	var cookie=getCookie(style_cookie_txt);
	var title=cookie?cookie:get_default_stylesheet();

	set_stylesheet(title, true);
}

if(style_cookie_site) {
	var cookie=getCookie(style_cookie_site);
	var title=cookie?cookie:get_default_stylesheet();

	set_stylesheet(title, false, true);
}
