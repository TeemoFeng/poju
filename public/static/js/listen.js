(function ($) {
    window.msg = {
        tips: function (s) {
            layer.msg(s.msg, { icon: s.code, time: s.timeout||1300 }, function () {s.act?s.act:(s.code==1|| s.code==6)&& window.location.reload(); });
        },
        model: function (s, e) {
            var a = (e.getAttribute("model-area") || "").split(",");
            var index = layer.open({type: 1, area: a, fixed: false, content: s, anim: 5,success: window.formRander});
            Number(a[0].split("px")[0]) > 1000 && layer.full(index);
            //var w = $("body", window.parent.document).width() - 50;
            //layer.style(index, { left: (w - Number(a[0].replace("px", ""))) / 2 + "px" });
        },
        error: function (s) {
            layer.msg(s, { icon: 2, time: 2000 });
        },
        confirm: function (s,f) {
            layer.confirm(s, function (index) {
                typeof f === 'function' && f.call(this);
                layer.close(index);
            })
        },
        login: function (s) {
            switch (s.model){
                case 'msg':
                    layer.msg(s.msg, { icon: s.code, time: 1300 }, function () { s.hasOwnProperty("url") ? (window.location = s.url) : ($("[name='password']").val('')) });
                    break;
                case 'confirm':
                    layer.confirm(s.msg, {
                        title:'邮件未验证',
                        btn: ['发送','不了']
                    }, function(){
                        $.get(s.act,function (res) {
                           layer.msg(res.msg, {icon: res.code});
                        });
                    });
                    break;
            }

        },
        upload: function (s, elem) {
            var imgbox = $(elem).siblings(".imglist"), _tb = $(elem).siblings("[type='hidden']");
            if(s.code == 1)
            {
                switch(s.ft){
                    case 1 :
                        imgbox.append("<div><img src=" + s.url + " /><i>×</i></div>");
                        break;
                    case 2:
                        imgbox.append("<div><span class=\"iconfont icon-shipin2\" data-url=\"" + s.url + "\"></span><i>×</i></div>");
                        break;
                    case 4:
                        imgbox.append("<div><span class=\"iconfont icon-paperclip\" data-url=\"" + s.url + "\"></span><i>×</i></div>");
                        break;
                }
                msg.loadurl(imgbox, _tb, s.ft);
            }else{
                layer.msg(s.msg, { icon: s.code, time: 2000 })
            }
        },
        loadurl: function (a, b, t) {
            var _ele = "", _s = "";
            if (t == 1) {
                _ele = $(a).find("img");
                $.each(_ele, function () { _s += $(this).attr("src") + ","; });
            }else{
                _ele = $(a).find("span");
                $.each(_ele, function () { _s += $(this).attr("data-url") + ","; });
            }
            b.val(_s.substring(0, _s.length - 1));
        },
        unload: function (a) {
            var s = $(a).val(), b = $(a).siblings(".imglist"), t = $(a).attr("filetype");
            if (!t || t == "1") {
                if (s) { var l = s.split(","); $.each(l, function () { b.append("<div><img src=" + this + " /><i>×</i></div>"); }); }
            } else if (t == "2"){
                if (s) { var l = s.split(","); $.each(l, function () { b.append("<div><span class=\"iconfont icon-shipin2\" data-url=\"" + this + "\"></span><i>×</i></div>"); }); }
            } else if (t == "4") {
                if (s) { var l = s.split(","); $.each(l, function () { b.append("<div><span class=\"iconfont icon-paperclip\" data-url=\"" + this + "\"></span><i>×</i></div>"); }); }
            }
        },
        waiting: function (elem) {
            var i = $(elem).siblings(".file-bg").find("i");
            i.removeClass("icon-upload").addClass("loading");
        },
        complete: function (elem) {
            var i = $(elem).siblings(".file-bg").find("i");
            i.addClass("icon-upload").removeClass("loading");
        },
        avatar:function (s, elem) {
            var img = $(elem).siblings("img"), _tb = $(elem).siblings("[type='hidden']"),p = $(elem).siblings("p"),f= $(elem).parent('figure');
            img.attr('src',s.url);
            _tb.val(s.url);
            p.remove();
            f.addClass('nomask');
        }
    }
    window.raven = function(a,b){
        $.get('/digg/is_login',function (r) {
            if(r.code==1){
                $.post('/digg/'+a,{aid:b},function (c) {
                    console.log(c);
                });
            }else {
                window.location.href='/user/login';
            }
        })
    }
    window.comment = function (a) {
        $.get('/digg/is_login',function (r) {
            if(r.code==1){
                $.post('/digg/add_comment',{aid:a.data('id'),info:a.parent('footer').siblings('textarea').val()},function (res) {
                    $('.comment-main').prepend(
               '<div class="comment-item clearfix">'+
                        '<figure><img src="'+ res.user.avatar +'"></figure>'+
                        '<section>'+
                    '<h3>'+res.user.nickname+'</h3>'+
                    '<article>'+ res.info+
                    '</article>'+
                    '<footer class="clearfix">'+
                    '<span class="time">刚刚</span>'+
                    '<span class="thumb"><i class="iconfont icon-thumb"></i>点赞</span>'+
                    '<span class="reply"><i class="iconfont icon-reply"></i>回复</span>'+
                    '</footer>'+
                    '</section>'+
                    '</div>');
                },'json');
            }else {
                window.location.href='/user/login?b='+location.href.split('#')[0];
            }
        })
    }
    window.dateDiff = function (timestamp) {
        var arrTimestamp = (timestamp + '').split('');
        for (var start = 0; start < 13; start++) {
            if (!arrTimestamp[start]) {
                arrTimestamp[start] = '0';
            }
        }
        timestamp = arrTimestamp.join('') * 1;

        var minute = 1000 * 60;
        var hour = minute * 60;
        var day = hour * 24;
        var halfamonth = day * 15;
        var month = day * 30;
        var now = new Date().getTime();
        var diffValue = now - timestamp;
        if (diffValue < 0) {
            return '不久前';
        }
        var monthC = diffValue / month;
        var weekC = diffValue / (7 * day);
        var dayC = diffValue / day;
        var hourC = diffValue / hour;
        var minC = diffValue / minute;
        var zero = function (value) {
            if (value < 10) {
                return '0' + value;
            }
            return value;
        };

        // 使用
        if (monthC > 3) {
            return (function () {
                var date = new Date(timestamp);
                return date.getFullYear() + '年' + zero(date.getMonth() + 1) + '月' + zero(date.getDate()) + '日';
            })();
        } else if (monthC >= 1) {
            return parseInt(monthC) + "月前";
        } else if (weekC >= 1) {
            return parseInt(weekC) + "周前";
        } else if (dayC >= 1) {
            return parseInt(dayC) + "天前";
        } else if (hourC >= 1) {
            return parseInt(hourC) + "小时前";
        } else if (minC >= 1) {
            return parseInt(minC) + "分钟前";
        }
        return '刚刚';
    };
    window.formRequest = {
        getFunction: function (code, argNames) {
        var fn = window, parts = (code || "").split(".");
        while (fn && parts.length) {fn = fn[parts.shift()];}
        if (typeof (fn) === "function") { return fn; }
        argNames.push(code);
        return Function.constructor.apply(null, argNames);
    },
        GoRequest: function (e, options) {
            var index;
            $.extend(options, {
                beforeSend: function (xhr) {
                    if (e.getAttribute("send-begin")) {
                        formRequest.getFunction(e.getAttribute("send-begin")).apply(this, [e])
                    } else {index = layer.load(1);}
                },
                complete: function () {e.getAttribute("complete")?formRequest.getFunction(e.getAttribute("complete")).apply(this, [e]):layer.close(index); },
                success: function (data,status, xhr) {
                    formRequest.getFunction(e.getAttribute("success"), ["data", "status", "xhr"]).apply(this, [data, e]);
                }
            });
            $.ajax(options);
        },
        validate: function (form) {
            var _ele = $(form).find(":input[name]");
            var messages = {};
            var adp = ["required", "remote", "email", "url", "number", "digits", "equalTo", "maxlength", "minlength", "max", "min"];
            $.each(_ele, function () {
                messages[this.name] = {};
                var _cele = this;
                var temp = {};
                $.each(adp, function () {
                    if ($(_cele).attr(this) !== undefined) {
                        temp[this] = $(_cele).attr(this + "-msg");
                    }
                })
                messages[this.name] = temp;
            });
            return $(form).validate({
                errorPlacement: function (error, element) {$(element).closest("li").append(error);},
                errorClass: "pTip",
                errorElement: "div",
                messages: messages
            }).form();
        }
    }
    $(document).on("submit", "form[ajax='true']", function (e) {
        e.preventDefault();
        var _e = this, _v = $(this).attr("data-valid"), _gr = function () { formRequest.GoRequest(_e, { url: _e.action, type: _e.method || "GET", data: $(_e).serializeArray() }); };
        _v ? (formRequest.validate(this) ? _gr() : "" ): _gr();
    });
    $(document).on("click", "a[ajax='true']", function (e) {
        e.preventDefault();
        this.getAttribute("left-mini")&&parent.LeftMenu.mini();
        var _e = this, _gr = function () { formRequest.GoRequest(_e, { url: _e.href, type: _e.getAttribute("data-method") || "GET", data: [] }) }, _cf = $(this).attr("data-confirm");
        _cf ? msg.confirm(_cf, function () {_gr();}) : _gr();
    });
    $(document).on("click", "[batch='true']", function (e) {
        e.preventDefault();
        var id = (function () {
            var data = [];
            return $($(this).attr('data-list') || '.ord_table tbody input[type=checkbox]').map(function () {
                (this.checked) && data.push(this.value);
            }), data.join(',');
        }).call(this);
        if (id.length) {
            var _e = this, _f = function () { formRequest.GoRequest(_e, { url: $(_e).attr("data-url"), type: "POST", data: { idlist: id } }); }, _cf = $(this).attr("data-confirm");
            _cf ? msg.confirm(_cf, function () { _f(); }) : _f();
        } else { msg.error("请选择要操作的记录")}
        
    });
    $(document).on("click", "ul.icon_lists li", function () {
        var ic = $(this).find(".fontclass").text().substring(1);
        $("[name='iconfont']").val(ic);
        $("#show-icon").removeClass().addClass("iconfont " + ic);
        layer.close(layer.index);
    });
    $(document).on("click", ".imglist i", function () {
        var a = $(this).parents(".imglist"),b = a.siblings("[type='hidden']");
        $(this).parent("div").remove();
        msg.loadurl(a,b);
    });
    $(document).on("change", ".upload-file", function () {
        var _f = $(this)[0].files, elem = this, pd = JSON.parse(elem.getAttribute("post-data")||"{}"), _gr = function (d) { formRequest.GoRequest(elem, { url: elem.getAttribute("url"), type: 'POST', data: d, processData: false, contentType: false }) },
            multiple = function () { $.each(_f, function () { var _fd = new FormData(); _fd.append("file", this); for (p in pd) { _fd.append(p, pd[p]) }; _gr(_fd); }) }, single = function () { var _fd = new FormData(); _fd.append("file", _f[0]); for (p in pd) { _fd.append(p, pd[p]) }; _gr(_fd) }
            elem.getAttribute("multiple") ? multiple() : single();
    });
    $('.contribute-link').click(function () {
        showTipsModal('投稿请联系：dengfeng@chainheadline.com');
    })

}(jQuery));