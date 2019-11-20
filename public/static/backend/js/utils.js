$(function () {
    //图片预览
    $(document).on("mouseover","img.preview",function (e) {
        $(this).parents("body").append("<div class='img-preview-box'><img src='" + $(this).data("url") + "'/><div>");
        $(".img-preview-box").css({ "left": e.pageX + 12, "top": e.pageY + 12, "display": "block" });
    }).on("mouseleave","img.preview",function () {
        $(this).parents("body").find('div.img-preview-box').remove();
    });
    //多选
    if ($('.cheack-all').length) {
        $('.cheack-all').click(function () {
            var inputs = $(this).closest('table').find('tbody input[type="checkbox"]');
            if ($(this).is(':checked')) { inputs.prop('checked', true); } else { inputs.prop('checked', false); }
        });
        $('tbody').on('click', 'input[type="checkbox"]', function () {
            var tbody = $(this).closest('tbody');
            var headTr = tbody.parent().find('.cheack-all');
            if (tbody.find(':checked').length == tbody.find('input[type="checkbox"]').length) {
                if (!headTr.is(':checked')) {
                    headTr.prop('checked', true);
                }
            } else {
                if (headTr.is(':checked')) {
                    headTr.prop('checked', false);
                }
            }
        });
    }
    function closeDropdown() {
        // if (e && e.which === 3) return
        $("[data-dropdown='click']").each(function () {
            var parent = $(this).parent(".dropdown");
            if (!parent.hasClass('open')) return
            parent.removeClass('open');
        });
    }
    $(document).click(function () {
        closeDropdown();
    });
    //下拉菜单
    $("[data-dropdown='click']").click(function (e) {
        e.stopPropagation();//阻止事件冒泡
        var parent = $(this).parent(".dropdown");
        parent.toggleClass("open");
        return false;
    });
    //tab-hover
    $('.tab-box ul.nav-tabs[theme="hover"] li').hover(function () {
        var i = $(this).index();
        $(this).addClass('active').siblings().removeClass('active');
        $('.tab-box .tab-content').children('.tab-item').eq(i).addClass('item-show').siblings().removeClass('item-show');
    });
    //tab-click
    $(document).on("click",".tab-box ul.nav-tabs[theme=\"click\"] li",function () {
        var i = $(this).index();
        $(this).addClass('active').siblings().removeClass('active');
        $('.tab-box .tab-content').children('.tab-item').eq(i).addClass('item-show').siblings().removeClass('item-show');
    });
    //navbar
    $(".scope").click(function () {
        var u = $(this).siblings("ul");
        var i = $(this).find("i");
        u.hasClass("hide") ? (function () { u.removeClass("hide"); i.removeClass("icon-triangle-right").addClass("icon-triangle-down"); }())
         : (function () { u.addClass("hide"); i.removeClass("icon-triangle-down").addClass("icon-triangle-right"); }());
    });
    $(".collapse-btn").click(function () {
        var v = $(".viewframework-navbar").parent();
        v.hasClass("view-mini") ? (function () { v.removeClass("view-mini").addClass("view-full"); }()) : (function () { v.removeClass("view-full").addClass("view-mini"); }());
    });
    $(document).on("click", ".page-box .btn", function () {
        var max = $(this).attr("data-max"), p = $(this).siblings("input").val(), url = location.href.split('?')[0];
        if (!p || Number(p) == 0 || Number(p) > Number(max)) {
            layer.msg("请输入正确的页码！", { icon: 5, time: 2000 });
        } else { location.href = url +"?page="+ p; }
    });
    //tips
    $(document).on('mouseenter', '*[help-tips]', function () {
        var content = $(this).attr('help-tips');
        this.index = layer.tips('<div style="padding: 10px; font-size: 14px; color: #eee;">' + content + '</div>', this, {
            time: -1
          , maxWidth: 280
          , tips: [3, '#3A3D49']
        });
    }).on('mouseleave', '*[help-tips]', function () {
        layer.close(this.index);
    });
});