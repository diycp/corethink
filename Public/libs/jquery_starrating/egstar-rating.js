// JavaScript Document(function($){

(function($) {
    var rating = {
        'allowedit': true, // if set to true, allows user to edit rating.
        'halfstars': true, // allow half ratings.
        'maxstars': 5, // number of stars, whole numbers only.
        'starwidth': 28, // set to the width of the star image. change only if making your own theme.
        'smstarwidth': 15, // set to the width of the small star image. change only if making your own theme.
        'a_speed': 200, // animation speed of resizing on star selection.
        'callback': false
    };

    function setstar(li, num) { // sets width for visible stars
        var wd = (rating.halfstars) ? rating.starwidth / 2 : rating.starwidth;
        li.parents('.eg-rtbtns').next('div').css('width', (wd * num) + 'px');
    }

    function seteditbox(egr, cont, val) {
        var wd = egr.children('.eg-rating').width() + egr.children('.eg-rtdash').width() + 27 + (rating.smstarwidth * rating.maxstars);
        if (rating.allowedit) {
            wd = wd + egr.children('.eg-edit').width();
        } else {
            wd = wd - 5;
        }
        cont.animate({
            width: wd
        }, rating.a_speed, editfade(egr, val, cont));

    }

    function editfade(egr, val, cont) {
        if (rating.allowedit) {
            egr.children('.eg-edit').fadeIn();
        }
        if (typeof(rating.callback) === 'function') {
            input = cont.next('input');
            val = {
                rating: val,
                input: input
            }
            rating.callback.call(val);
        }
    }

    function setsmallstar(rt, div) {
        rt = (rating.halfstars) ? rt * .5 : rt;
        div.width(rt * rating.smstarwidth);
    }

    function roll() {
        setstar($(this), $(this).index() + 1);
    }

    function rate() {
        var liN = $(this).index() + 1;
        $par = $(this).parent().parent();
        $par.hide();
        $par.next('.eg-on').hide();
        $par.next().next().show();
        var val = (rating.halfstars) ? liN * .5 : liN;
        $par.parent().next('input').val(val);
        var egr = $par.parent().find('.eg-rating');
        var smwd = rating.smstarwidth * rating.maxstars;
        var smstr = egr.next().next()
        smstr.width(smwd);
        egr.html(val);
        seteditbox(egr.parent(), $par.parent(), val);
        setsmallstar(liN, smstr.children('div'));

    }

    function out() {
        var num = $(this).parent().parent().parent().next('input').val();
        var num = (rating.halfstars) ? num / .5 : num;
        setstar($(this), num);
    }

    function edit() {
        $par = $(this).parent();
        $par.hide();
        $par.prev().show();
        $par.prev().prev().show();
        $par.parent().animate({
            width: rating.starwidth * rating.maxstars
        }, rating.a_speed);
    }

    function buildFrag() {
        var liwd = (rating.halfstars) ? rating.starwidth / 2 : rating.starwidth;
        var contwd = (rating.halfstars) ? (rating.starwidth / 2) * (rating.maxstars * 2) : rating.starwidth * rating.maxstars;
        var cont = $("<div>", {
            "class": "eg-rtcont",
            width: contwd + "px"
        });
        var rtd = $("<div>", {
            "class": "eg-rated"
        });
        var ul = $("<ul>");
        var li = $("<li>", {
            html: "&nbsp;",
            mouseover: roll,
            mouseout: out,
            click: rate,
            width: liwd + "px"
        });
        var m = (rating.halfstars) ? 2 * rating.maxstars : rating.maxstars;
        for ($i = 0; $i < m; $i++) {
            li.clone(true).appendTo(ul);
        }
        ul.appendTo($("<div>", {
            "class": "eg-rtbtns"
        }).appendTo(cont));
        $("<div>", {
            "class": "eg-on",
            html: "&nbsp;"
        }).appendTo(cont);
        $("<div>", {
            "class": "eg-rating",
            text: "0"
        }).appendTo(rtd);
        $("<div>", {
            "class": "eg-rtdash",
            text: "-"
        }).appendTo(rtd);
        $("<div>", {
            "class": "eg-smallstars",
            html: "<div>&nbsp;</div>"
        }).appendTo(rtd);
        if (rating.allowedit) {
            $("<div>", {
                "class": "eg-edit",
                text: "edit",
                click: edit
            }).appendTo(rtd);
        }
        rtd.appendTo(cont);
        return cont;
    }
    $.fn.elistars = function(options) {
        if (options) {
            $.extend(rating, options);
        }
        return this.each(function() {
            buildFrag().clone(true).insertBefore(this);
            $(this).hide();
        });
    }

})(jQuery);