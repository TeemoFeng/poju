(function ($) {
    window.msg = {
        tips: function (s) {
            layer.msg(s.msg, { icon: s.code, time: s.timeout||1300 }, function () { (s.code==1|| s.code==6)&& window.location.reload(); });
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
            layer.msg(s.msg, { icon: s.code, time: 1300 }, function () { s.hasOwnProperty("url") ? (window.location = s.url) : ($("[name='password']").val(''), $("[name='code']").val(''), $(".img-code").click()) });
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
        }
    }
    
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
                errorPlacement: function (error, element) {$(element).closest("div").append(error);},
                errorClass: "error",
                errorElement: "div",
                messages: messages
            }).form();
        }
    }
    window.formRander = function () {
        $(".layui-layer-content form input,.layui-layer-content form select").each(function () {
            var model = $(this).data("rander-model"),elem = $(this);
            switch(model)
            {
                case "unload":
                    window.msg.unload(elem);
                   if(elem.attr("dragsort")){
                     var imgbox = $(elem).siblings(".imglist");
                     $(".imglist").dragsort({ 
                        placeHolderTemplate: "<div style='border:1px dashed #00C1DE;box-sizing:border-box;'></div>",
                        dragSelectorExclude:"i",
                        dragEnd:function(){
                            msg.loadurl(imgbox, elem, 1);
                        }
                    });
                   } 
                break;
                case "selectize":
                    var selectpicker = elem;
                    selectpicker.data("url")?
                    $.get(selectpicker.data("url"), function(res){
                        var s = selectpicker.selectize({
                        valueField: selectpicker.attr("key")||'id',
                        labelField: selectpicker.attr("lab")||'name',
                        searchField: selectpicker.attr("lab")||'name',
                        options: res,
                        onChange:function (value) {
                            selectpicker.data("onchange")?formRequest.getFunction(selectpicker.data("onchange")).apply(this, [value]):"";
                        }
                    });
                        selectpicker.get(0).nodeName != "INPUT" && s[0].selectize.setValue(selectpicker.data("value"));
                    },"json")
                    :(function(){
                        var s = selectpicker.selectize({create: true});
                        selectpicker.get(0).nodeName != "INPUT" && s[0].selectize.setValue(selectpicker.data("value"))
                    }).call(this);
                break;
                case "date":
                    var datepicker = elem.attr("name");
                    laydate.render({ elem:"[name="+datepicker+"]" , type: 'datetime'});
                break;
                case "tags":
                    elem.selectize({
                        plugins: ['restore_on_backspace'],
                        persist: false,
                        create: true
                    });
                    break;
            }       
        });
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
}(jQuery));