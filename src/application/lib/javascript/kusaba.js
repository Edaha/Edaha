gt = new Gettext({ 'domain': 'kusaba' });
function _(msgid) { return gt.gettext(msgid); }

kusaba.toggle = function (button, area) {
    if (match = /manage/.exec(document.location.toString()))
        var speed = 0;
    else
        var speed = "normal";
    $('#' + area).slideToggle(speed, function () {
        $(button).html("&nbsp;" + ($(this).is(":hidden") ? '&plus;' : '&minus;') + "&nbsp;");
        $.cookie('nav_show_' + area, $(this).is(":hidden") ? '0' : '1', { expires: 30 })
    }).next("br").toggle();
}
kusaba.removeframes = function () {
    {
        var frame = $("#main", top.document);

        frame.css("left", (frame.width() * .15) + "px");
        frame.animate({
            left: "0",
            "width": "100%"
        },
            1000, '', function () {
                //$.cookie('frameremoved', 1, { expires : 30 });
                $.cookie("use_frames", null);
                top.document.title = $(this).contents().find("title").html();
                $(this).contents().find(".navbar").after("<a id=\"toggleframe\"></a>");
                $(this).contents().find("#toggleframe").html("< Bring back frame").bind("click",
                    function () {
                        $.cookie("use_frames", 1, { expires: 30 });
                        //$.cookie('frameremoved', 0, { expires : 30 });
                        $(this).remove();
                        frame = $("#main", top.document);
                        frame.css("left", 0);
                        frame.animate({
                            left: (frame.width() * .15) + "px",
                            "width": (frame.width() - frame.width() * .15) + "px"
                        }, 1000, '');
                    });
            });
    }
    return false
}

kusaba.reloadmain = function () {
    if (parent.main) {
        parent.main.location.reload();
    }
}

kusaba.insert = function (text) {
    if (!kusaba.ispage || kusaba.quick_reply) {
        var textarea = document.forms.postform.message;
        if (textarea) {
            if (textarea.createTextRange && textarea.caretPos) { // IE
                var caretPos = textarea.caretPos;
                caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == " " ? text + " " : text;
            } else if (textarea.setSelectionRange) { // Firefox
                var start = textarea.selectionStart;
                var end = textarea.selectionEnd;
                textarea.value = textarea.value.substr(0, start) + text + textarea.value.substr(end);
                textarea.setSelectionRange(start + text.length, start + text.length);
            } else {
                textarea.value += text + " ";
            }
            textarea.focus();

            return false;
        }
    }
    return true;
}

kusaba.checkhighlight = function () {
    var match;

    if (match = /#i([0-9]+)/.exec(document.location.toString()))
        if (!document.forms.postform.message.value)
            kusaba.insert(">>" + match[1] + "\n");

    if (match = /#([0-9]+)/.exec(document.location.toString()))
        kusaba.highlight(match[1]);
}
kusaba.highlight = function (post, checknopage) {

    if ((checknopage && kusaba.ispage) || kusaba.ispage) {
        // Uncomment the following line to always send the user to the thread if the link was clicked on the board page.
        //return;
    }

    $("div.highlight").removeClass().addClass("reply");

    var reply = $("#reply_" + post);

    if ((reply.length || $('#postform [name=replythread]').val() == post) && $(reply).parents("div.reflinkpreview").length == 0) {
        if (reply.length) {
            reply.addClass("highlight");
        }
        var match = /^([^#]*)/.exec(document.location.toString());
        document.location = match[1] + "#" + post;
        return false;
    }

    return true;
}

kusaba.get_password = function (name) {
    var pass = $.cookie(name);
    if (pass) return pass;

    var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    var pass = '';

    for (var i = 0; i < 8; i++) {
        var rnd = Math.floor(Math.random() * chars.length);
        pass += chars.substring(rnd, rnd + 1);
    }
    $.cookie(name, pass, { expires: 365, path: '/' })
    return (pass);
}

kusaba.togglePassword = function () {
    $("#posting_form ol:first").append('<li><label for"mod">' + _("Mod") + '</label><div class="fixer"><input type="text" name="modpassword" size="20" maxlength="75"></div>&nbsp;<acronym title="' + _("Display staff status (Mod/Admin)") + '">D</acronym>:<input type="checkbox" name="displaystaffstatus" checked>&nbsp;<acronym title="' + _("Lock") + '">L</acronym>:<input type="checkbox" name="lockonpost">&nbsp;<acronym title="' + _("Sticky") + '">S</acronym>:<input type="checkbox" name="stickyonpost">&nbsp;<acronym title="' + _("Raw HTML") + '">RH</acronym>:<input type="checkbox" name="rawhtml">&nbsp;<acronym title="' + _("Name") + '">N</acronym>:<input type="checkbox" name="usestaffname"></li>');
    return false;
}

kusaba.toggleOptions = function (threadid, formid, board) {

    if ($('#opt' + threadid).length) {
        if (!$('#opt' + threadid).is(":hidden")) {
            $('#opt' + threadid).toggle();
            $('#opt' + threadid).html();
        } else {
            var newhtml = '<td class="label"><label for="formatting">Formatting:</label></td><td colspan="3"><select name="formatting"><option value="" onclick="$(\'#formattinginfo' + threadid + '\').html(_(\'All formatting is performed by the user.\'));">Normal</option><option value="aa" onclick="$(\'#formattinginfo' + threadid + '\').html(_(\'[aa] and [/aa] will surround your message.\'));"';
            if ($.cookie('kuformatting') == 'aa') {
                newhtml += ' selected';
            }
            newhtml += '>Text Art</option></select> <input type="checkbox" name="rememberformatting"><label for="rememberformatting">Remember</label> <span id="formattinginfo' + threadid + '">';
            if ($.cookie('kuformatting') == 'aa') {
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

kusaba.set_stylesheet = function (styletitle, txt, site) {
    if ($("styletitle").length) {
        if (styletitle == 'Nigrachan') {
            $("body").bind('mousemove.rainbow', function (e) {
                if (!kusaba.loopid && $(e.target).is('styletitle')) {
                    anchorobj = e.target;
                    kusaba.loopid = setInterval("kusaba.colors()", 100)
                }
                else if (kusaba.loopid && e.target != anchorobj) {
                    clearInterval(kusaba.loopid);
                    $(anchorobj).css("color", '');
                    kusaba.loopid = 0;
                }
            });
        } else {
            $("body").unbind('.rainbow')
        }
    }
    if (txt) {
        if (styletitle == kusaba.get_default_stylesheet())
            $.cookie("kustyle_txt", null, { path: '/' });
        else
            $.cookie("kustyle_txt", styletitle, { expires: 365, path: '/' })
    } else if (site) {
        if (styletitle == kusaba.get_default_stylesheet())
            $.cookie("kustyle_site", null, { path: '/' });
        else
            $.cookie("kustyle_site", styletitle, { expires: 365, path: '/' })
    } else {
        if (styletitle == kusaba.get_default_stylesheet())
            $.cookie("kustyle", null, { path: '/' });
        else
            $.cookie("kustyle", styletitle, { expires: 365, path: '/' })
    }
    var found = false;
    $('link[rel$=stylesheet][title]').each(function (i) {
        this.disabled = true;
        if (this.getAttribute('title') == styletitle) {
            this.disabled = false;
            found = true;
        }
    });
    $("#rulesbottom").css({ "background-color": $("#rules").css("border-top-color"), "width": $("#rules").width(), "top": $("#rules").height() > $("#posting_form ol").height() ? $("#rules").height() + 2 : $("#posting_form ol").height() });
    if (!found) kusaba.set_preferred_stylesheet();
}

kusaba.set_preferred_stylesheet = function () {
    $('link[rel=stylesheet][title]')[0].disabled = false;
}

kusaba.get_active_stylesheet = function () {
    return $('link[rel$=stylesheet][title]').filter(function () { return !this.disabled; }).last().attr('title');
}

kusaba.get_default_stylesheet = function () {
    style = $('link[rel=stylesheet][title]');
    if (style.length) {
        return style.attr('title');
    }
    return null;
}

kusaba.delandbanlinks = function (context) {
    if (!kumod_set) return;
    if (!context) {
        kusaba.togglePassword();
        var bottombox = $("#fileonly");
        bottombox.length && bottombox.parent().html('[<input type="checkbox" name="fileonly" id="fileonly" value="on" /><label for="fileonly">File Only</label>] <input name="moddelete" onclick="return confirm(_(\'Are you sure you want to delete these posts?\'))" value="' + _('Delete') + '" type="submit" /> <input name="modban" value="' + _('Ban') + '" onclick="this.form.action=\'' + kusaba.cgipath + '/manage_page.php?action=bans\';" type="submit" />');
    }
    var newhtml;
    var dnbelements = $("span[id^='dnb']", context).each(
        function (i) {
            // N3X15 TODO: Load this entire thing from PHP so we can determine if use has access to Flag or not
            // Maybe even eventually add a popup window for inline bans and dropdown ban presets
            dnbinfo = $(this).attr("id").split('_');
            $.get(kusaba.boardspath + "/manage_page.php?action=getip&boarddir=" + dnbinfo[1] + "&id=" + dnbinfo[2], {},
                function (responseText, textStatus) {
                    ipaddr = responseText.split("=") || "what are you doing get out you don't even fit";
                    $('#' + ipaddr[0]).prepend("[IP: " + ipaddr[1] + " <a href=\"" + kusaba.boardspath + "/manage_page.php?action=deletepostsbyip&ip=" + ipaddr[1] + "\" title=\"" + _('Delete all posts by this IP') + "\">D</a> / <a href=\"" + kusaba.boardspath + "/manage_page.php?action=ipsearch&ip=" + ipaddr[1] + "\" title=\"" + _('Search for posts with this IP') + "\">S</a>] ");
                });
            newhtml = '&#91;<a href="' + kusaba.cgipath + '/manage_page.php?action=delposts&boarddir=' + dnbinfo[1] + '&del';
            if (dnbinfo[3] == 'y') {
                newhtml += 'thread';
            } else {
                newhtml += 'post';
            }
            newhtml += 'id=' + dnbinfo[2] + '" title="' + _('Delete') + '" onclick="return confirm(_(\'Are you sure you want to delete this post/thread?\'));">D<\/a>&nbsp;<a href="' + kusaba.cgipath + '/manage_page.php?action=delposts&boarddir=' + dnbinfo[1] + '&del';
            if (dnbinfo[3] == 'y') {
                newhtml += 'thread';
            } else {
                newhtml += 'post';
            }
            newhtml += 'id=' + dnbinfo[2] + '&postid=' + dnbinfo[2] + '" title="' + _('Delete &amp; Ban') + '" onclick="return confirm(_(\'Are you sure you want to delete and ban the poster of this post/thread?\'));">&amp;<\/a>&nbsp;<a href="' + kusaba.cgipath + '/manage_page.php?action=bans&banboard=' + dnbinfo[1] + '&banpost=' + dnbinfo[2] + '" title="' + _('Ban') + '">B<\/a>&#93;&nbsp;&#91;<a href="' + kusaba.cgipath + '/manage_page.php?action=bans&banboard=' + dnbinfo[1] + '&banpost=' + dnbinfo[2] + '&instant=y" title="' + _('Instant Permanent Ban') + '" onclick="instantban(\'' + dnbinfo[1] + '\',' + dnbinfo[2] + '); return false;">P<\/a>&#93;&nbsp;&#91;<a href="' + kusaba.cgipath + '/manage_page.php?action=delposts&boarddir=' + dnbinfo[1] + '&del';
            if (dnbinfo[3] == 'y') {
                newhtml += 'thread';
            } else {
                newhtml += 'post';
            }
            newhtml += 'id=' + dnbinfo[2] + '&postid=' + dnbinfo[2] + '&cp=y" title="' + _('Child Pornography') + '" onclick="return confirm(_(\'Are you sure that this is child pornography?\'));">CP<\/a>&#93;';
            newhtml += '&nbsp;&#91;<a href="' + kusaba.cgipath + '/manage_page.php?action=flag&board=' + dnbinfo[1] + '&post=' + dnbinfo[2] + '" title="Flag">F</a>&#93;';
            $(this).html(newhtml);
        });
}

kusaba.instantban = function (boardid, postid) {
    var reason = prompt(_('Are you sure you want to permenently ban the poster of this post/thread?\nIf so enter a ban message or click OK to use the default ban reason. To cancel this operation, click "Cancel".'));
    if (typeof reason === 'string') {
        var url = kusaba.cgipath + '/manage_page.php?action=bans&banboard=' + boardid + '&banpost=' + postid + '&instant=y';
        if (reason != '') {
            url += '&reason=' + reason;

            // Require ban reason.
            $.get(url, {},
                function (ban, status) {
                    if (ban == "success")
                        alert(_("Ban was sucessful."));
                    else
                        alert(_("Ban failed!"));
                    if (status != "success")
                        alert(_("Ban failed!"));
                });
        } else {
            alert(_("Ban reason required!"));
        }
    }
    else {
        alert(_("OK, no action taken."));
    }
}

kusaba.togglethread = function (threadid, board) {
    if (kusaba.hiddenthreads.toString().indexOf(threadid + board) !== -1) {
        $('#unhidethread_' + threadid + '_' + board).toggle();
        $('#thread_' + threadid + '_' + board).toggle();
        kusaba.hiddenthreads.splice(kusaba.hiddenthreads.indexOf(threadid + board), 1);
        $.cookie('hiddenthreads', kusaba.hiddenthreads.join('!'), { expires: 30 })
    } else {
        $('#thread_' + threadid + '_' + board).toggle();
        $('#unhidethread_' + threadid + '_' + board).toggle();
        kusaba.hiddenthreads.push(threadid + board);
        $.cookie('hiddenthreads', kusaba.hiddenthreads.join('!'), { expires: 30 })
    }
    return false;
}

kusaba.toggleblotter = function (save) {
    $(".blotterentry").slideToggle("normal",
        function () {
            if (save) {
                $.cookie('ku_showblotter', $(this).is(":hidden") ? '0' : '1', { expires: 365 })
            }
        });
}

kusaba.expandthread = function (threadid, board) {
    var repliesblock = $("#replies_" + threadid + "_" + board);
    var omittedblock = $("#p" + threadid + " .omittedposts");
    if (repliesblock.length) {
        omittedblock.html(_('Expanding thread') + '...<br /><br />');
        repliesblock.load(kusaba.boardspath + '/ajax.php?act=expand&board=' + board + '&thread=' + threadid, {},
            function (responseText, textStatus) {
                omittedblock.hide();
                if (!responseText) {
                    repliesblock.html(_("something went wrong (blank response)"));
                }
                if (textStatus == "error") {
                    alert(_('Something went wrong...'));
                }
                kusaba.addevents(this);
            }
        );
    }
    return false;
}

kusaba.quickreply = function (threadid) {
    if (threadid == 0) {
        kusaba.quick_reply = false;
        $('#posttypeindicator').html('new thread');
    } else {
        kusaba.quick_reply = true;
        $('#posttypeindicator').html('reply to ' + threadid + ' [<a href="#postbox" onclick="javascript:quickreply(\'0\');" title="Cancel">x</a>]');
    }
    $('#postform [name=replythread]').val(threadid);
}

kusaba.startPostSpyTimeout = function (threadid, board, thelastid) {
    var postspy = $.cookie('postspy');
    if (postspy == '1') {
        if ($('#thread_' + threadid + '_' + board).length) {
            lastid = thelastid;
            setTimeout('kusaba.postSpy(' + threadid + ', "' + board + '");', 10000);
        }
    }
}

kusaba.postSpy = function (threadid, board) {
    var threadblock = $("#thread_" + threadid + "_" + board);
    if (threadblock.length) {
        $.get(kusaba.boardspath + '/ajax.php?act=spy&board=' + board + '&thread=' + threadid + '&pastid=' + lastid, {},
            function (responseText, textStatus) {
                if (textStatus != "success") {
                    alert(_('Something went wrong...'));
                }
                else {
                    var response_split = responseText.split('|');
                    newlastid = response_split[0];
                    if (newlastid != '') {
                        responseText = responseText.substr((newlastid.length + 1));
                        oldpost = $("#spy" + lastid);
                        if (!oldpost.length) {
                            oldpost = threadblock.children("table:last");
                        }
                        if (!oldpost.length) {
                            oldpost = threadblock;
                        }
                        oldpost.after($("<div id=\"spy" + newlastid + "\">" + responseText + "</div>").hide());
                        lastid = newlastid;
                        newpost = $("#spy" + lastid);
                        newpost.slideToggle("normal");
                        kusaba.addevents(newpost);
                    }

                    setTimeout('postSpy(' + threadid + ', "' + board + '");', 5000);
                }
            }
        );
    }
}

kusaba.getwatchedthreads = function (threadid, board) {
    /*  var watchedthreadbox = $("#watchedthreadlist");
        if (watchedthreadbox.length) {
            watchedthreadbox.html(_('Loading watched threads...'));
            watchedthreadbox.load(kusaba.boardspath + '/ajax.php?act=threadwatch&board=' + board + '&thread=' + threadid,{},
                function(responseText, textStatus) {
                    if (!responseText) {
                        watchedthreadbox.html(_("something went wrong (blank response)"));
                    }
                    if (textStatus == "error") {
                        alert(_('Something went wrong...'));
                    }
                    $("#watchedthreads").css("min-height", ($("#watchedthreadlist").attr('offsetHeight')+$("#watchedthreadsdraghandle").attr('offsetHeight')+$("#watchedthreadsbuttons").attr('offsetHeight')));
                });
        }*/
    if (kusaba.gttimeout) {
        clearTimeout(kusaba.gttimeout);
        kusaba.getthreads();
    }
}

kusaba.addtowatchedthreads = function (threadid, board) {
    var watchedthreadbox = $("#watchedthreadlist");
    //  if (watchedthreadbox.length) {
    $.get(kusaba.boardspath + '/ajax.php?act=threadwatch&do=addthread&board=' + board + '&thread=' + threadid, {},
        function (responseText, textStatus) {
            if (textStatus == "success") {
                //alert(_('Thread successfully added to your watch list.'));
                div = $("<div />").css({ "border": "1px solid #C5C8D2", "background-color": "#F9F9F9", "color": "#4F5260", "padding": "5px", "position": "fixed", "right": "0", "bottom": "0px" }).html("Thread #" + threadid + " added to watched threads").hide()
                $("body").append(div);
                div.fadeIn(500)
                div.delay(3000).fadeOut();
                kusaba.getwatchedthreads('0', board);
            }
            else {
                alert(_('Something went wrong...'));
            }
        });
    //  }
}

kusaba.removefromwatchedthreads = function (threadid, board) {
    var watchedthreadbox = $("#watchedthreadlist");
    //  if (watchedthreadbox.length) {
    $.get(kusaba.boardspath + '/ajax.php?act=threadwatch&do=removethread&board=' + board + '&thread=' + threadid, {},
        function (responseText, textStatus) {
            if (textStatus == "success") {
                getwatchedthreads('0', board);
            }
            else {
                alert(_('Something went wrong...'));
            }
        });
    //  }
}
kusaba.hidewatchedthreads = function () {
    $.cookie('showwatchedthreads', '0', { expires: 30 });
    $("#watchedthreads").fadeOut("slow", function () { $(this).remove() });

}
kusaba.showwatchedthreads = function () {
    $.cookie('showwatchedthreads', '1', { expires: 30 });
    window.location.reload(true);
}
kusaba.togglePostSpy = function () {
    var postspy = $.cookie('postspy');
    if (postspy == '1') {
        $.cookie('postspy', '0', { expires: 30 });
        //alert('Post Spy disabled.  Any pages loaded from now on will not utilize the Post Spy feature.');
        div = $("<div />").css({ "border": "1px solid #C5C8D2", "background-color": "#F9F9F9", "color": "#4F5260", "padding": "5px", "position": "fixed", "right": "0", "bottom": "0px" }).html("Post Spy disabled.  Any pages loaded from now on will not utilize the Post Spy feature.").hide()
    } else {
        $.cookie('postspy', '1', { expires: 30 });
        //alert('Post Spy enabled.  Any pages loaded from now on will utilize the Post Spy feature.');
        div = $("<div />").css({ "border": "1px solid #C5C8D2", "background-color": "#F9F9F9", "color": "#4F5260", "padding": "5px", "position": "fixed", "right": "0", "bottom": "0px" }).html("Post Spy enabled.  Any pages loaded from now on will utilize the Post Spy feature.").hide()
    }
    $("body").append(div);
    div.fadeIn(500)
    div.delay(3000).fadeOut();
    return false;
}

kusaba.checkcaptcha = function (formid) {
    if ($("#" + formid + " [name=captcha]").length && $("#" + formid + " [name=captcha]").val() == '') {
        alert('Please enter the captcha image text.');
        $("#" + formid + " [name=captcha]").focus();
        return false;
    }

    return true;
}

kusaba.expandimg = function (postnum, imgurl, thumburl, imgw, imgh, thumbw, thumbh) {
    var img = $("#thumb_" + postnum + " img:first-child");
    //var parent = $("#thumb_" + postnum + " a");
    if (img.attr("src") == thumburl) {
        img.attr({ "src": imgurl, "alt": postnum, "height": imgh, "width": imgw });
    } else if (img.attr("src") == imgurl) {
        img.attr({ "src": thumburl, "alt": postnum, "height": thumbh, "width": thumbw });
    }
}

kusaba.postpreview = function (divid, board, parentid, message) {
    var previewdiv = $("#" + divid);
    if (previewdiv.length) {
        previewdiv.load(kusaba.boardspath + '/ajax.php?act=preview&board=' + board + '&thread=' + parentid + '&message=' + escape(message), {},
            function (responseText, textStatus) {
                if (!responseText) {
                    previewdiv.html(_("something went wrong (blank response)"));
                }
            });
    }
}

kusaba.set_inputs = function (id) {
    if ($("#" + id).length) {
        if (!$('#' + id + ' [name=name]').val() && $.cookie("name") != null) $('#' + id + ' [name=name]').val($.cookie("name"));
        if (!$('#' + id + ' [name=em]').val() && $.cookie("email") != null) $('#' + id + ' [name=em]').val($.cookie("email"));
        if (!$('#' + id + ' [name=postpassword]').val()) $('#' + id + ' [name=postpassword]').val(kusaba.get_password("postpassword"));
    }
}

kusaba.set_delpass = function (id) {
    if ($("#" + id).length) {
        if (!$('#' + id + ' [name=postpassword]').val()) $('#' + id + ' [name=postpassword]').val(kusaba.get_password("postpassword"));
    }
}
kusaba.addevents = function (context) {
    if (!context && kusaba.board != "roulette") {
        $("span[id^=hide_]").html('<a href="#" title="' + _("Hide Thread") + '"><img src="' + kusaba.boardspath + '/css/icons/blank.gif" border="0" class="hidethread" alt="hide" /></a>').click(function (e) {
            kusaba.togglethread(this.id.substr(5), kusaba.board);
            e.preventDefault();
        });
        $("span[id^=watch_]").html('<a href="#" title="' + _("Watch Thread") + '"><img src="' + kusaba.boardspath + '/css/icons/blank.gif" border="0" class="watchthread" alt="watch" /></a>').click(function (e) {
            kusaba.addtowatchedthreads(this.id.substr(6), kusaba.board);
            e.preventDefault();
        });
        $("span[id^=expand_]").html('<a href="#" title="' + _("Expand Thread") + '"><img src="' + kusaba.boardspath + '/css/icons/blank.gif" border="0" class="expandthread" alt="expand" /></a>').click(function (e) {
            kusaba.expandthread(this.id.substr(7), kusaba.board);
            e.preventDefault();
        });
        $("span[id^=quickreply_]").html('<a href="#postbox" title="' + _("Quick Reply") + '"><img src="' + kusaba.boardspath + '/css/icons/blank.gif" border="0" class="quickreply" alt="quickreply" /></a>').click(function (e) {
            kusaba.quickreply(this.id.substr(11));
        });

        $("#showwatchedthreads").click(kusaba.showwatchedthreads);
        $("#togglepostspy").click(kusaba.togglePostSpy);
        $("#sitestyles").click(kusaba.showstyleswitcher);
        $("#showdirs").click(kusaba.showdirs);
        $("#hidedirs").click(kusaba.hidedirs);
    }
    if (!kusaba.ispage || (context && context.className != 'reply')) {
        if (!kusaba.ispage) {
            $("div[id^=replies_" + kusaba.thread + "]").prepend('<a href="#top" id="expandall_' + kusaba.thread + '_' + kusaba.board + '">' + _("Expand all images") + '</a>');
        }
        else if (!$('#expandall_' + context.id.split("_")[1] + '_' + kusaba.board).length) {
            var threadid = context.id.split("_")[1];
            $(context).prepend('<a href="#top" id="expandall_' + threadid + '_' + kusaba.board + '">' + _("Expand all images") + '</a>');
        }
    }
    if (1 || $('link[rel$=stylesheet][title]:not([disabled])').attr("title") == "Burichan") {
        $("a[id^=expandall]").not(".moved").attr("title", _("Expand all images")).html(
            $("<img />")
                .attr("src", kusaba.boardspath + "/css/icons/blank.gif")
                .addClass("expandall")
        ).addClass("moved").each(function () {
            $(this).parent(".replies").prev().find(".extrabtns").prepend($("<span />").prepend(this));
        });
    }
    $("a[id^=expandall]").click(
        function (e) {
            tinfo = $(this).attr("id").split('_');
            $('#thread_' + tinfo[1] + '_' + tinfo[2] + ' span[class^=multithumb] a, #thread_' + tinfo[1] + '_' + tinfo[2] + ' .file_size a').click();
            return false;
        });
    $("a[id^=expandimg], span[class^=multithumb] a", context).bind('click',
        function (e) {
            if ((e.which && e.which != 1) || e.metaKey || e.ctrlKey) { return; }
            var imginfo = $(this).attr("id").split('_');
            var type = this.href.match(/.+\.([^?]+)(\?|$)/)[1];
            var thumburl = this.toString().replace(/\.[^.]+$/, 's$&').replace("src", "thumb");
            if (type == 'webm') {
                thumb = $("#thumb_" + imginfo[1] + " a").add($("#thumb_" + imginfo[1]).parent("a"));
                // WebM thumbnail
                if (thumb.is(':visible')) {
                    video = $("<video></video>")
                        .attr({
                            'muted': true,
                            'controls': true,
                            'loop': true,
                            'autoplay': true,
                            'src': this.href
                        })
                        .css('max-width', ((document.documentElement.clientWidth - thumb.parents(".post")[0].getBoundingClientRect().left) * .95) - 35 + 'px');
                    //                        var maxWidth = $("#" + parseInt(imginfo[1], 10)).css('max-width');
                    //                        $("#thumb_" + imginfo[1]).css('max-width', '96%');

                    _ref = kusaba.videoCB;
                    for (eventName in _ref) {
                        cb = _ref[eventName];
                        video.on(eventName, cb);
                    }
                    thumb.hide();
                    thumb.parent().append(video);
                } else {
                    kusaba.contractwebm($("#thumb_" + imginfo[1] + " video").add($("#thumb_" + imginfo[1]).parent().next())[0]);
                }
            } else {
                var img = $("#thumb_" + imginfo[1] + " img:first-child");
                if (img.attr("src") == thumburl) {
                    img.attr({ "src": this, "alt": imginfo[1], "height": imginfo[3], "width": imginfo[2], "style": "" });
                } else if (img.attr("src") == this) {
                    img.attr({ "src": thumburl, "alt": imginfo[1], "height": imginfo[5], "width": imginfo[4], "style": "" });
                }
            }
            return false;
        });
    /*  $("img.thumb", context).parent("a").bind('click',
            function(e) {
                if ((e.which && e.which != 1) || e.metaKey || e.ctrlKey) { return true;}
                var link = $("a[id^=expandimg_"+$(this).children("img").attr("alt")+"]");
                    if (link.length) {
                        link.click();
                        return false;
                }
                return true;
            });
        $("img.multithumb, img.multithumbfirst", context).parents("a").bind('click',
            function(e) {
                if ((e.which && e.which != 1) || e.metaKey || e.ctrlKey) { return true;}
                var link = $("span[class^=multithumb] a[id^=expandimg_"+$(this).children("span").attr("id").replace("thumb_", "")+"]");
                if (link.length) {
                    link.click();
                    e.preventDefault();
                    return;
                }
                return;
            });
    
        $("img.multithumb, img.multithumbfirst, img.thumb").parents("a").dblclick(
            function() {
                window.open(this.href);
            });*/
    jQuery.fn.single_double_click = function (single_click_callback, double_click_callback, timeout) {
        return this.each(function () {
            var clicks = 0, self = this, doReturn = true;
            jQuery(this).click(function (event) {
                if ((event.which && event.which != 1) || event.metaKey || event.ctrlKey) { return true; }
                clicks++;
                if (clicks == 1) {
                    setTimeout(function () {
                        if (clicks == 1) {
                            single_click_callback.call(self, event);
                        } else {
                            double_click_callback.call(self, event);
                        }
                        clicks = 0;
                    }, timeout || 300);
                }
                return false;
            });
        });
    }
    $("img.thumb", context).parent('a[href$=".gif"], a[href$=".jpg"], a[href$=".png"], a[href$=".webm"]').single_double_click(
        function (e) {
            var link = $("a[id^=expandimg_" + $(this).children("img").attr("alt") + "]");
            if (link.length) {
                link.click();
                return false;
            }
            return true;
        }, function () {
            window.open(this.href);
        });
    $("img.multithumb, img.multithumbfirst", context).parents('a[href$=".gif"], a[href$=".jpg"], a[href$=".png"], a[href$=".webm"]').single_double_click(
        function (e) {
            var link = $("span[class^=multithumb] a[id^=expandimg_" + $(this).children("span").attr("id").replace("thumb_", "") + "]");
            if (link.length) {
                link.click();
                return false;
            }
            return true;
        }, function () {
            window.open(this.href);
        });
    $('a[href*="#"]', context).click(function (e) {
        var anchor = this.href.split(/#/)[1];
        if (typeof anchor == 'undefined') {
            return true;
        }

        var matches = anchor.match(/(i)?([0-9]+)/);

        if (matches == null) {
            return true;
        }

        if (matches[1] == 'i') {
            var change_href = kusaba.insert(">>" + matches[2] + "\n");
        }
        else {
            var change_href = kusaba.highlight(matches[2]);
        }
        if (!change_href) {
            e.preventDefault();
        }


    });
    kusaba.addpreviewevents(context);
    kusaba.delandbanlinks(context);
    kusaba.addbacklinks(context);

}
kusaba.videoCB = (function () {
    var mousedown;
    mousedown = false;
    return {
        mouseover: function () {
            return mousedown = false;
        },
        mousedown: function (e) {
            if (e.button === 0) {
                return mousedown = true;
            }
        },
        mouseup: function (e) {
            if (e.button === 0) {
                return mousedown = false;
            }
        },
        mouseout: function (e) {
            if (mousedown && e.clientX <= this.getBoundingClientRect().left) {
                return kusaba.contractwebm(this);
            }
        },
        click: function (e) {
            if (this.paused && !this.controls) {
                this.play();
                return e.preventDefault();
            }
        }
    };
})();
kusaba.contractwebm = function (video) {
    $(video.parentNode).children("a").show();
    $(video).remove();
}
kusaba.addpreviewevents = function (context) {
    $("a[class^='ref|']", context).mouseenter(
        function (e) {
            var ainfo = $(this).attr("class").split('|');
            var previewelement = $("<div></div>")
                .addClass('reply reflinkpreview')
                .attr({ id: "preview" + $(this).attr("class") })
                .css({
                    "left": (e.pageX + 50) + "px",
                    "top": e.pageY + "px",
                    "visibility": "hidden",
                    "overflow-x": "visible",
                    "font-size": $(".reply .post").first().css("font-size")
                })

            if (
                kusaba.board == ainfo[1]
                && $('#reply_' + ainfo[3]).length
                && (
                    (
                        $('#thumb_' + ainfo[3]).length
                        && $('#thumb_' + ainfo[3] + " img:first-child").attr("src").lastIndexOf("thumb") != -1
                    )
                    || !$('#thumb_' + ainfo[3]).length)
            ) {
                var isonpage = true;
                var dest = $("#reply_" + ainfo[3]).clone();
                dest.children(".post").removeAttr("id");
                previewelement.html(dest);
            } else {
                $.get(kusaba.boardspath + '/ajax.php?act=read&board=' + ainfo[1] + '&thread=' + ainfo[2] + '&post=' + ainfo[3] + '&single' + (typeof kusaba.board === 'undefined' || kusaba.board != ainfo[1] ? "&showboard" : ""), {},
                    function (responseText, textStatus) {
                        if (textStatus != "success") {
                            alert('wut');
                        }
                        else {
                            if (responseText) {
                                previewelement.html(responseText);
                                if (previewelement.width() + (e.pageX + 50) > $(window).width()) {
                                    previewelement.css({ left: '', "max-width": 800, right: ($(window).width() - e.pageX) })
                                }
                                previewelement.css({ opacity: 0.0, visibility: "visible" }).animate({ opacity: 1.0 });
                            }
                            else {
                                previewelement.html(_("something went wrong (blank response)")).css({ opacity: 0.0, visibility: "visible" }).animate({ opacity: 1.0 });
                            }
                        }
                    });
            }
            previewelement.insertBefore($(this));
            if (isonpage) previewelement.css({ opacity: 0.0, visibility: "visible" }).animate({ opacity: 1.0 });
        }).mouseleave(
            function (e) {
                var previewelement = ($("div[id='preview" + $(this).attr("class") + "']"));
                if (previewelement.length) {
                    previewelement.remove();
                }
            }).click(
                function (e) {
                    var ainfo = $(this).attr("class").split('|');
                    return kusaba.highlight(ainfo[3], true);
                });
}

kusaba.addbacklinks = function (context) {
    $(".message a[class^='ref|']", context).each(function () {
        var refinfo = this.className.split("|");
        var thread = refinfo[2];
        var reply = refinfo[3];
        var post = $("#" + reply);
        var header, reflinks;
        var dest = $(this).parents(".post");
        refinfo[3] = dest.attr("id");
        if (post.length) {
            // Add the replies span if it hasn't already been added
            reflinks = $(".reflinks", post);
            if (!reflinks.length) {
                header = $(".post_header", post);
                reflinks = $("<span class='reflinks'>Replies: </span>")
                    .css({
                        'display': 'block',
                        'font-size': '11px',
                        'line-height': 'normal'
                    });
                header.append(reflinks);

            }
            var newHref = $(this).attr('href').split("#");
            newHref.pop();
            newHref.push(refinfo[3]);
            var newLink = $(this)
                .clone(true)
                .attr({
                    'class': refinfo.join("|"),
                    'href': newHref.join("#")
                })
                .text(">>" + refinfo[3])
            reflinks.append(" ").append(newLink);
        }
    });
}

if (kusaba.style_cookie) {
    var cookie = $.cookie(kusaba.style_cookie);
    var title = cookie ? cookie : kusaba.get_default_stylesheet();

    if (title != kusaba.get_active_stylesheet())
        kusaba.set_stylesheet(title);
}

if (kusaba.style_cookie_txt) {
    var cookie = $.cookie(kusaba.style_cookie_txt);
    var title = cookie ? cookie : kusaba.get_default_stylesheet();

    kusaba.set_stylesheet(title, true);
}

if (kusaba.style_cookie_site) {
    var cookie = $.cookie(kusaba.style_cookie_site);
    var title = cookie ? cookie : kusaba.get_default_stylesheet();

    kusaba.set_stylesheet(title, false, true);
}

$(document).ready(function () {
    var youtubeLinks = $('a').filter('[data-yt-id]');
    var youtubeIds = youtubeLinks.map(function () {
        return $(this).data("yt-id");
    }).get();
    while (youtubeIds.length) {
        $.get(
            "https://www.googleapis.com/youtube/v3/videos", {
            part: 'snippet',
            id: youtubeIds.splice(0, 50).join(','),
            fields: 'items(id,snippet(title))',
            key: 'AIzaSyDDmV03GVV8pmBLhGuOclQHIzdauU67lvE'
        },
            function (data) {
                $.each(data.items, function (i, item) {
                    var link = $("a").filter('[data-yt-id=' + item.id + "]");
                    link.text("[Youtube] " + item.snippet.title);
                    var embed = link.clone().text("(embed)");
                    embed.click(function (e) {
                        e.preventDefault();
                        if (embed.hasClass("embedded")) {
                            $(this.nextElementSibling).remove();
                            embed.text("(embed)");
                        } else {
                            var iframe = $('<div/>').append($("<iframe></iframe>", {
                                "src": "//www.youtube.com/embed/" + item.id + "?wmode=opaque",
                                "width": link.data("width"),
                                "height": link.data("height"),
                            }));
                            embed.after(iframe);
                            embed.text("(unembed)");
                        }

                        return embed.toggleClass('embedded');
                    });
                    link.after("\n", embed);
                })
            }
        );
    }
    if ($("#main", top.document).length) {
        if ($.browser.opera && $("base").length) {
        }
        if ($.cookie("use_frames") == null) {
            top.document.title = $(this).contents().find("title").html();
            $(this).contents().find(".navbar").after("<a id=\"toggleframe\"></a>");
            $(this).contents().find("#toggleframe").html("< Bring back frame").bind("click",
                function () {
                    $.cookie('use_frames', 1, { expires: 30 });
                    $(this).remove();
                    frame = $("#main", top.document);
                    frame.css("left", 0);
                    frame.animate({
                        left: (frame.width() * .15) + "px",
                        "width": (frame.width() - frame.width() * .15) + "px"
                    }, 1000, '');
                });
        }
    }
    if ($.cookie("kumod") == "allboards") {
        kumod_set = true
    }
    else if ($.cookie("kumod") != null) {
        var listofboards = $.cookie("kumod").split('|');
        var thisboard = $("#postform [name=board]").val();
        for (var cookieboard in listofboards) {
            if (listofboards[cookieboard] == thisboard) {
                kumod_set = true;
                break
            }
        }
    }
    else {
        kumod_set = false;
    }

    if ($("section#recent").length) {
        var hiddenSections = $("section#boardlist article ul:hidden").map(function () { return this.id }).get().join("|");
        var recentPosts = $("section#recent section#posts ul").css({ 'overflow': 'hidden' });
        var recentImages = $("section#recent section#images ul").css({ 'overflow-x': 'hidden' });
        setInterval(function () {
            $.getJSON(kusaba.boardspath + '/ajax.php?act=recentposts&last=' + escape($("section#recent section#posts li:first a").attr("class")) + '&ilast=' + escape($("section#recent section#images li:first a").attr("class")) + '&hidden=' + escape(hiddenSections),
                function (response, textStatus) {
                    if (textStatus == "success") {
                        var posts = response['posts'];
                        for (var prop in posts) {
                            if (posts.hasOwnProperty(prop)) {
                                posts[prop]['parentid'] = (posts[prop]['parentid'] == 0 ? posts[prop]['id'] : posts[prop]['parentid']);
                                var post = posts[prop];
                                kusaba.addpreviewevents($("<li></li>", { css: { 'display': 'none' } }).html("<a class=\"ref|" + post['boardname'] + "|" + post['parentid'] + "|" + post['id'] + "\" href=\"" + kusaba.boardspath + "/" + post['boardname'] + "/res/" + post['parentid'] + ".html#" + post['id'] + "\">&gt;&gt;&gt;/" + post['boardname'] + "/" + post['id'] + "</a> - " + post['message']).insertBefore($("li:first", recentPosts)).slideDown());
                                $("li:last", recentPosts).remove()
                            }
                        }
                        var images = response['images'];
                        for (var prop in images) {
                            if (images.hasOwnProperty(prop)) {
                                images[prop]['parentid'] = (images[prop]['parentid'] == 0 ? images[prop]['id'] : images[prop]['parentid']);
                                var image = images[prop];
                                kusaba.addpreviewevents($("<li></li>", { css: { 'display': 'none' } }).html("<a class=\"ref|" + image['boardname'] + "|" + image['parentid'] + "|" + image['id'] + "\" href=\"" + kusaba.boardspath + "/" + image['boardname'] + "/res/" + image['parentid'] + ".html#" + image['id'] + "\"><img src=\"" + kusaba.boardspath + "/" + image['boardname'] + "/thumb/" + image['file'] + "s." + (image['file_type'] == 'webm' ? 'jpg' : image['file_type']) + "\"></a>").insertBefore($("li:first", recentImages)).animate({ width: 'toggle' }, 350));
                                $("li:last", recentImages).remove()
                            }
                        }
                    }
                });
        }, 10000);
    }

    kusaba.addevents();
    kusaba.checkhighlight();
    $("#adminbar #dropswitch").change(
        function (e) {
            if ($(this).children('option:selected').attr('value')) kusaba.set_stylesheet($(this).children('option:selected').attr('value'));
        });
    $("#adminbar a[id$=style_]").click(
        function (e) {
            kusaba.set_stylesheet($(this).attr("id").substr(0, 6));
            return false;
        });
    if (1 || $('link[rel$=stylesheet][title]:not([disabled])').attr("title") == "Burichan") {
        $("#rules #tabs").append(
            $("<li />").html($("<a />").html(_("Watched Threads")).attr({ "href": "#", "id": "watchedthreadstab" })),
            $("<li />").html($("<a />").html(_("Settings")).attr({ "href": "#", "id": "settingstab" })),
            $("<li />").html($("<a />").html(_("Channel7")).attr({ "href": "#", "id": "settingstab" }))
        );
        var watchedThreadContent = $("<div />").html($("<span />").addClass("title").html(("Watched Threads"))).addClass("rulescontent").css("font-size", "1.2em").hide();
        $("#rules").append(watchedThreadContent,
            $("<div />").append($("<span />").addClass("title").html(_("Settings")), "Style: ", $("#dropswitch"), "<p />", $("#togglepostspy").html("Toggle Post Spy")).addClass("rulescontent").css("font-size", "1.2em").hide(),
            $("<div />").addClass("rulescontent").css("font-size", "1.2em").hide())

        kusaba.getthreads =
            function () {
                if (!kusaba.board || !kusaba.thread) {
                    return;
                }
                $.getJSON(kusaba.boardspath + '/ajax.php?act=threadwatch&board=' + kusaba.board + '&thread=' + kusaba.thread + '&format=json', function (data) {
                    if (data) {
                        var numnewreplies = 0;
                        $("p", watchedThreadContent).remove();
                        for (var i = 0, len = data.length; i < len; ++i) {
                            watchedThreadContent.append($("<p />").html(
                                $("<a />").addClass("ref|" + data[i]['board'] + "|" + data[i]['threadid'] + "|" + data[i]['threadid'])
                                    .html(">>>/" + data[i]['board'] + "/" + data[i]['threadid'])
                                    .attr("href", kusaba.boardspath + "/" + data[i]['board'] + "/res/" + data[i]['threadid'] + ".html")
                            ).append(" - "));
                            if (data[i]['subject']) {
                                $("p:eq(" + (i) + ")", watchedThreadContent).append($('<span />').html(data[i]['subject']).addClass("subject").css("font-size", "1em"));
                            }
                            $("p:eq(" + (i) + ")", watchedThreadContent).append(" [",
                                $(data[i]['numnewreplies'] > 0 ? '<a />' : '<span />')
                                    .css("color", data[i]['numnewreplies'] > 0 ? 'red' : 'green')
                                    .html(data[i]['numnewreplies'])
                                    .attr(data[i]['numnewreplies'] > 0 ? 'href' : null, kusaba.boardspath + "/" + data[i]['board'] + "/res/" + data[i]['threadid'] + ".html#" + data[i]['lastsawreplyid'])
                                , "]", $("<a />").css({ "float": "right", "font-weight": "bold" }).attr({ "title": _("Un-Watch"), "href": "#", "id": "unwatch-" + data[i]['board'] + "-" + data[i]['threadid'] }).html("X").click(
                                    function (e) {
                                        var thread = this.id.split("-");
                                        kusaba.removefromwatchedthreads(thread[2], thread[1]);
                                        $(this).parent("p").remove();
                                        return false;
                                    })
                            );
                            numnewreplies += parseInt(data[i]['numnewreplies']);
                        }
                        if (numnewreplies > 0) {
                            $("#watchedthreadstab").html(_("Watched Threads")).append($("<span />").css({ "color": "red", 'font-weight': 'bold' }).html(" (" + numnewreplies + ")"));
                        }
                        kusaba.addpreviewevents(watchedThreadContent);
                        kusaba.gttimeout = setTimeout(kusaba.getthreads, 60000);
                    }
                });
            }
        kusaba.getthreads();
        //watchedThreadContent.append($("<div />").load(kusaba.boardspath + '/ajax.php?act=threadwatch&board=' + kusaba.board + '&thread=' + kusaba.threadid =' &,{}));

        $("#rules #tabs li a").click(function () {
            if ($("#rules #tabs li.selected a").html() == 'Channel7') {
                $($("#rules .rulescontent")[$("#rules #tabs li").index($("#rules #tabs li.selected"))]).find("video").each(function () {
                    this.pause();
                    delete (this);
                    $(this).remove();
                    window.stop();
                });
                $($("#rules .rulescontent")[$("#rules #tabs li").index($("#rules #tabs li.selected"))]).empty();
            }
            $($("#rules .rulescontent")[$("#rules #tabs li").index($("#rules #tabs li.selected"))]).hide();
            $($("#rules .rulescontent")[$("#rules #tabs li a").index(this)]).show();
            $("#rules #tabs li.selected").removeClass("selected").children("a").attr("href", "#");
            $(this).parents("li").addClass("selected").children("a").removeAttr("href");
            if ($(this).html() == 'Channel7') {
                $($("#rules .rulescontent")[$("#rules #tabs li a").index(this)]).append(
                    $("<span />").addClass("title").html(_("Channel7")),
                    $("<video />").width(400).height(225).attr('autoplay', 'autoplay').append($("<source />").attr({ 'src': 'http://radio.7chan.org:8000/CH7.webm', 'type': 'video/webm' }), $("<source />").attr({ 'src': 'http://radio.7chan.org:8000/CH7', 'type': 'video/ogg' })),
                    $("<div />").html(
                        $("<a />").html("Open in new window").attr("href", "#").click(function () {
                            $("video").remove();
                            window.stop();
                            window.open('http://7chan.org/channel7.html', 'channel7', 'toolbar=0,location=0,status=0,menubar=0,scrollbars=0,resizable=0,width=900,height=550'); return false;
                        })
                    ).css("text-align", "center")
                );
            }
            $("#rulesbottom").css("top", $("#rules").height() > $("#posting_form ol").height() ? $("#rules").height() + 2 : $("#posting_form ol").height());
            return false;
        });
        $("#adminbar a").not(":first").each(function () { $(".navbar").append("[", $(this), "] ") });
    } else {
        if ($.cookie('showwatchedthreads') && $.cookie('showwatchedthreads') != 0) {
            $('#watchedthreads').css({ "top": Math.max(185, $.cookie('watchedthreadstop')) + "px", "left": Math.max(25, $.cookie('watchedthreadsleft')) + "px", "width": Math.max(250, $.cookie('watchedthreadswidth')) + 'px', "height": Math.max(75, $.cookie('watchedthreadsheight')) + 'px' }).attr("class", "watchedthreads").html('<div class="postblock" id="watchedthreadsdraghandle" style="width: 100%;">' + _("Watched Threads") + '<\/div><span id="watchedthreadlist"><\/span><div id="watchedthreadsbuttons"><a href="#" id="hidewt" title="' + _("Hide the watched threads box") + '"><img src="' + kusaba.webpath + 'css/icons/blank.gif" border="0" class="hidewatchedthreads" alt="hide" /><\/a>&nbsp;<a href="#" id="refreshwt" title="' + _("Refresh watched threads") + '"><img src="' + kusaba.webpath + 'css/icons/blank.gif" border="0" class="refreshwatchedthreads" alt="refresh" /><\/a><\/div><\/div>');

            kusaba.getwatchedthreads(kusaba.thread, kusaba.board);

            $('#watchedthreads').draggable({
                handle: '#watchedthreadsdraghandle',
                opacity: 0.7,
                stop: function () {
                    $.cookie('watchedthreadstop', $(this).css('top'), { expires: 30 });
                    $.cookie('watchedthreadsleft', $(this).css('left'), { expires: 30 });
                }
            }).resizable({
                minHeight: ($("#watchedthreadlist").attr('offsetHeight') + $("#watchedthreadsdraghandle").attr('offsetHeight') + $("#watchedthreadsbuttons").attr('offsetHeight')),
                stop: function () {
                    $.cookie('watchedthreadswidth', $(this).width(), { expires: 30 });
                    $.cookie('watchedthreadsheight', $(this).height(), { expires: 30 });
                }
            });
            $('#watchedthreads #hidewt').click(
                function (e) {
                    kusaba.hidewatchedthreads();
                });
            $('#watchedthreads #refreshwt').click(
                function (e) {
                    kusaba.getwatchedthreads(0, kusaba.board);
                });
        }
    }
    $('textarea').css({ "background-image": "url('/css/images/resize.png')", "background-repeat": "no-repeat", "background-position": "bottom right", "resize": "none", "margin-top": "0", "height": "" }).resizable({
        resize: function (event, ui) {
            //          $("#postform .ui-wrapper").css("padding" , 0);
            //          $("#message_label").css({"padding-top" : (($('#postform textarea').height()-9)/2) , "padding-bottom" : (($('#postform textarea').height()-9)/2)});
            $("#rulesbottom").css("top", $("#rules").height() > $("#posting_form ol").height() ? $("#rules").height() + 2 : $("#posting_form ol").height());
        }
    });
    $("#postform .ui-wrapper").css("padding", 0);
    //$("#postform .ui-wrapper").css("margin-top" , "4px");
    $("#rules").prepend("<div id=\"rulesbottom\" style=\"height: 1px;width: 400px;background-color: " + $("#rules").css("border-top-color") + ";position: relative;top: " + ($("#rules").height() > $("#posting_form ol").height() ? $("#rules").height() + 3 : $("#posting_form ol").height()) + "px;right: 10px;padding-right: 20px;\"</div>");
    $(this).keydown(function (e) {
        if (e.altKey && !$("input, textarea").is(":focus")) {
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
                        var docloc_valid = docloc_trimmed + page + '.html';
                    }

                    if (e.keyCode == 222 || e.keyCode == 221) {
                        if (match = /#s([0-9])/.exec(docloc)) {
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
    }).load(function () {
        $("#rulesbottom").css("top", $("#rules").height() > $("#posting_form ol").height() ? $("#rules").height() + 2 : $("#posting_form ol").height());
    });
    if (title == 'Nigrachan') {
        $("body").bind('mousemove.rainbow', function (e) {
            if (!kusaba.loopid && $(e.target).is('a')) {
                anchorobj = e.target;
                kusaba.loopid = setInterval("kusaba.colors()", 100)
            }
            else if (kusaba.loopid && e.target != anchorobj) {
                clearInterval(kusaba.loopid);
                $(anchorobj).css("color", '');
                kusaba.loopid = 0;
            }
        });
    }

});

kusaba.colors = function () {
    $(anchorobj).css("color", kusaba.makeColor())
}
kusaba.makeColor = function () {
    red = Math.sin(.3 * kusaba.val + 0) * 127 + 128;
    green = Math.sin(.3 * kusaba.val + 2) * 127 + 128;
    blue = Math.sin(.3 * kusaba.val + 4) * 127 + 128;
    red = Math.floor(red).toString(16);
    green = Math.floor(green).toString(16);
    blue = Math.floor(blue).toString(16);
    if (red.length == 1) red = "0" + red;
    if (green.length == 1) green = "0" + green;
    if (blue.length == 1) blue = "0" + blue;
    kusaba.val++;
    if (kusaba.val >= 40) kusaba.val = 0;
    return '#' + red + green + blue
}
