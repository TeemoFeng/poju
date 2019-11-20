
$(function () {
    //计算元素集合的总宽度
    function calSumWidth(elements) {
        var width = 0;
        $(elements).each(function () {
            width += $(this).outerWidth(true);
        });
        return width;
    }
    //滚动到指定选项卡
    function scrollToTab(element) {
        var marginLeftVal = calSumWidth($(element).prevAll()), marginRightVal = calSumWidth($(element).nextAll());
        // 可视区域非tab宽度
        var tabOuterWidth = calSumWidth($(".content-tabs").children().not(".J_menuTabs"));
        //可视区域tab宽度
        var visibleWidth = $(".content-tabs").outerWidth(true) - tabOuterWidth;
        //实际滚动宽度
        var scrollVal = 0;
        if ($(".page-tabs-content").outerWidth() < visibleWidth) {
            scrollVal = 0;
        } else if (marginRightVal <= (visibleWidth - $(element).outerWidth(true) - $(element).next().outerWidth(true))) {
            if ((visibleWidth - $(element).next().outerWidth(true)) > marginRightVal) {
                scrollVal = marginLeftVal;
                var tabElement = element;
                while ((scrollVal - $(tabElement).outerWidth()) > ($(".page-tabs-content").outerWidth() - visibleWidth)) {
                    scrollVal -= $(tabElement).prev().outerWidth();
                    tabElement = $(tabElement).prev();
                }
            }
        } else if (marginLeftVal > (visibleWidth - $(element).outerWidth(true) - $(element).prev().outerWidth(true))) {
            scrollVal = marginLeftVal - $(element).prev().outerWidth(true);
        }
        $('.page-tabs-content').animate({
            marginLeft: 0 - scrollVal + 'px'
        }, "fast");
    }
    //查看左侧隐藏的选项卡
    function scrollTabLeft() {
        var marginLeftVal = Math.abs(parseInt($('.page-tabs-content').css('margin-left')));
        // 可视区域非tab宽度
        var tabOuterWidth = calSumWidth($(".content-tabs").children().not(".J_menuTabs"));
        //可视区域tab宽度
        var visibleWidth = $(".content-tabs").outerWidth(true) - tabOuterWidth;
        //实际滚动宽度
        var scrollVal = 0;
        if ($(".page-tabs-content").width() < visibleWidth) {
            return false;
        } else {
            var tabElement = $(".J_menuTab:first");
            var offsetVal = 0;
            while ((offsetVal + $(tabElement).outerWidth(true)) <= marginLeftVal) {//找到离当前tab最近的元素
                offsetVal += $(tabElement).outerWidth(true);
                tabElement = $(tabElement).next();
            }
            offsetVal = 0;
            if (calSumWidth($(tabElement).prevAll()) > visibleWidth) {
                while ((offsetVal + $(tabElement).outerWidth(true)) < (visibleWidth) && tabElement.length > 0) {
                    offsetVal += $(tabElement).outerWidth(true);
                    tabElement = $(tabElement).prev();
                }
                scrollVal = calSumWidth($(tabElement).prevAll());
            }
        }
        $('.page-tabs-content').animate({
            marginLeft: 0 - scrollVal + 'px'
        }, "fast");
    }
    //查看右侧隐藏的选项卡
    function scrollTabRight() {
        var marginLeftVal = Math.abs(parseInt($('.page-tabs-content').css('margin-left')));
        // 可视区域非tab宽度
        var tabOuterWidth = calSumWidth($(".content-tabs").children().not(".J_menuTabs"));
        //可视区域tab宽度
        var visibleWidth = $(".content-tabs").outerWidth(true) - tabOuterWidth;
        //实际滚动宽度
        var scrollVal = 0;
        if ($(".page-tabs-content").width() < visibleWidth) {
            return false;
        } else {
            var tabElement = $(".J_menuTab:first");
            var offsetVal = 0;
            while ((offsetVal + $(tabElement).outerWidth(true)) <= marginLeftVal) {//找到离当前tab最近的元素
                offsetVal += $(tabElement).outerWidth(true);
                tabElement = $(tabElement).next();
            }
            offsetVal = 0;
            while ((offsetVal + $(tabElement).outerWidth(true)) < (visibleWidth) && tabElement.length > 0) {
                offsetVal += $(tabElement).outerWidth(true);
                tabElement = $(tabElement).next();
            }
            scrollVal = calSumWidth($(tabElement).prevAll());
            if (scrollVal > 0) {
                $('.page-tabs-content').animate({
                    marginLeft: 0 - scrollVal + 'px'
                }, "fast");
            }
        }
    }

    //通过遍历给菜单项加上data-index属性
    $(".J_menuItem").each(function (index) {
        if (!$(this).attr('data-index')) {
            $(this).attr('data-index', index);
        }
    });

    function menuItem() {
        // 获取标识数据
        var dataUrl = $(this).attr('href'),
            dataIndex = $(this).data('index'),
            menuName = $.trim($(this).text()),
            flag = true;

        if (!$(this).parent("li").hasClass(".nav_li_on")) {
            $(this).parent("li").addClass("nav_li_on").unbind('hover').siblings().removeClass("nav_li_on");
            $(this).parent("li").parents(".menu-nav").siblings().find("li").removeClass("nav_li_on");
        }



        if (dataUrl == undefined || $.trim(dataUrl).length == 0) return false;

        // 选项卡菜单已存在
        $('.J_menuTab').each(function () {
            if ($(this).data('id') == dataUrl) {
                if (!$(this).hasClass('active')) {
                    $(this).addClass('active').siblings('.J_menuTab').removeClass('active');
                    scrollToTab(this);
                    // 显示tab对应的内容区
                    $('.J_mainContent .J_iframe').each(function () {
                        if ($(this).data('id') == dataUrl) {
                            $(this).show().siblings('.J_iframe').hide();
                            return false;
                        }
                    });
                }
                flag = false;
                return false;
            }
        });

        // 选项卡菜单不存在
        if (flag) {
            var str = '<a href="javascript:;" class="active J_menuTab" data-id="' + dataUrl + '"><i class="icon-close"></i>' + menuName + ' </a>';
            $('.J_menuTab').removeClass('active');

            // 添加选项卡对应的iframe
            var str1 = '<iframe class="J_iframe" name="iframe' + dataIndex + '" width="100%" height="100%" src="' + dataUrl + '" frameborder="0" data-id="' + dataUrl + '" seamless></iframe>';
            $('.J_mainContent').find('iframe.J_iframe').hide().parents('.J_mainContent').append(str1);
            $('.J_menuTabs .page-tabs-content').append(str);
            scrollToTab($('.J_menuTab.active'));
        }
        return false;
    }
    $(document).on("click",".J_menuItem",menuItem);
   // $('.J_menuItem').on('click', menuItem);

    // 关闭选项卡菜单
    function closeTab() {
        var closeTabId = $(this).parents('.J_menuTab').data('id');
        var currentWidth = $(this).parents('.J_menuTab').width();

        // 当前元素处于活动状态
        if ($(this).parents('.J_menuTab').hasClass('active')) {

            // 当前元素后面有同辈元素，使后面的一个元素处于活动状态
            if ($(this).parents('.J_menuTab').next('.J_menuTab').size()) {

                var activeId = $(this).parents('.J_menuTab').next('.J_menuTab:eq(0)').data('id');
                $(this).parents('.J_menuTab').next('.J_menuTab:eq(0)').addClass('active');

                $('.J_mainContent .J_iframe').each(function () {
                    if ($(this).data('id') == activeId) {
                        $(this).show().siblings('.J_iframe').hide();
                        LeftOn(activeId);
                        return false;
                    }
                });

                var marginLeftVal = parseInt($('.page-tabs-content').css('margin-left'));
                if (marginLeftVal < 0) {
                    $('.page-tabs-content').animate({
                        marginLeft: (marginLeftVal + currentWidth) + 'px'
                    }, "fast");
                }

                //  移除当前选项卡
                $(this).parents('.J_menuTab').remove();

                // 移除tab对应的内容区
                $('.J_mainContent .J_iframe').each(function () {
                    if ($(this).data('id') == closeTabId) {
                        $(this).remove();
                        return false;
                    }
                });
            }

            // 当前元素后面没有同辈元素，使当前元素的上一个元素处于活动状态
            if ($(this).parents('.J_menuTab').prev('.J_menuTab').size()) {
                var activeId = $(this).parents('.J_menuTab').prev('.J_menuTab:last').data('id');
                $(this).parents('.J_menuTab').prev('.J_menuTab:last').addClass('active');
                $('.J_mainContent .J_iframe').each(function () {
                    if ($(this).data('id') == activeId) {
                        $(this).show().siblings('.J_iframe').hide();
                        LeftOn(activeId);
                        return false;
                    }
                });

                //  移除当前选项卡
                $(this).parents('.J_menuTab').remove();

                // 移除tab对应的内容区
                $('.J_mainContent .J_iframe').each(function () {
                    if ($(this).data('id') == closeTabId) {
                        $(this).remove();
                        return false;
                    }
                });
            }
        }
        // 当前元素不处于活动状态
        else {
            //  移除当前选项卡
            $(this).parents('.J_menuTab').remove();

            // 移除相应tab对应的内容区
            $('.J_mainContent .J_iframe').each(function () {
                if ($(this).data('id') == closeTabId) {
                    $(this).remove();
                    return false;
                }
            });
            scrollToTab($('.J_menuTab.active'));
        }
        return false;
    }

    $('.J_menuTabs').on('click', '.J_menuTab i', closeTab);

    //关闭其他选项卡
    function closeOtherTabs() {
        $('.page-tabs-content').children("[data-id]").not(":first").not(".active").each(function () {
            $('.J_iframe[data-id="' + $(this).data('id') + '"]').remove();
            $(this).remove();
        });
        $('.page-tabs-content').css("margin-left", "0");
    }
    $('.J_tabCloseOther').on('click', closeOtherTabs);

    //滚动到已激活的选项卡
    function showActiveTab() {
        scrollToTab($('.J_menuTab.active'));
    }
    $('.J_tabShowActive').on('click', showActiveTab);


    // 点击选项卡菜单
    function activeTab() {
        if (!$(this).hasClass('active')) {
            var currentId = $(this).data('id');
            // 显示tab对应的内容区
            $('.J_mainContent .J_iframe').each(function () {
                if ($(this).data('id') == currentId) {
                    $(this).show().siblings('.J_iframe').hide();
                    return false;
                }
            });
            $(this).addClass('active').siblings('.J_menuTab').removeClass('active');
            scrollToTab(this);
            LeftOn(currentId);
        }
    }

    function LeftOn(a) {
        var n = $("a[href='" + a + "']").parent('li');
        if (!n.hasClass("nav_li_on")) {
            n.addClass("nav_li_on").unbind('hover').siblings().removeClass("nav_li_on");
            n.parents(".menu-nav").siblings().find("li").removeClass("nav_li_on");
        }
    }


    $('.J_menuTabs').on("click", ".J_menuTab", activeTab);

    // 左移按扭
    $('.J_tabLeft').on('click', scrollTabLeft);

    // 右移按扭
    $('.J_tabRight').on('click', scrollTabRight);

    // 关闭全部
    $('body').on('click', '.J_tabCloseAll', function () {
        $('.page-tabs-content').children("[data-id]").not(":first").each(function () {
            $('.J_iframe[data-id="' + $(this).data('id') + '"]').remove();
            $(this).remove();
        });
        $('.page-tabs-content').children("[data-id]:first").each(function () {
            $('.J_iframe[data-id="' + $(this).data('id') + '"]').show();
            $(this).addClass("active");
        });
        $('.page-tabs-content').css("margin-left", "0");
    });

    $(document).on("click",".nav-tit",function () {
   // $(".nav-tit").click(function () {
        var tp = $(this).parent();
        var list = $(this).siblings("ul");
        if (tp.hasClass("open")) {
            tp.removeClass("open");
            list.css("max-height", "0");
        } else {
            tp.addClass("open");
            list.css("max-height", "");
            tp.siblings().removeClass("open");
            tp.siblings().find("ul").css("max-height", "0");
        }
    });
    $(document).on("click",".fold",function () {
   // $(".fold").click(function () {
        LeftMenu.flag ? (LeftMenu.mini(), LeftMenu.flag = false) : (LeftMenu.full(), LeftMenu.flag = true);
    });
    window.LeftMenu = {
        flag: true,
        mini: function () {
            var _m = $(".menu").parent(), _c = $(".con_right");
            _m.hasClass("full") ? _m.removeClass("full").addClass("mini") : "";
            _c.hasClass("con_mini") ? _c.removeClass("con_mini").addClass("con_full") : "";
        },
        full: function () {
            var _m = $(".menu").parent(), _c = $(".con_right");
            _m.hasClass("mini") ? _m.removeClass("mini").addClass("full") : "";
            _c.hasClass("con_full") ? _c.removeClass("con_full").addClass("con_mini") : "";
        }
    };
    $(document).on("hover",".nav-item li",function () {
    //$(".nav-item li").hover(function () {
        $(this).addClass("li_hover");
    }, function () { $(this).removeClass("li_hover"); });
    $(document).on("click",".nav-item li",function () {
    //$(".nav-item li").click(function () {
        if (!$(this).hasClass(".nav_li_on")) {
            $(this).addClass("nav_li_on").unbind('hover').siblings().removeClass("nav_li_on");
            $(this).parents(".menu-nav").siblings().find("li").removeClass("nav_li_on");
        }
    });
    $("body").on("mouseover mouseout", ".mini .nav-tit", function (e) {
        if (e.type == "mouseover") {
            var X = $(this).offset().top;
            var Y = $(this).offset().left;
            var info = $(this).find("span").text();
            var tips = '<div class="item-tips" style="top:' + X + 'px;left:' + 60 + 'px;">' + info + '</div>';
            $("body").append(tips);
            $(".item-tips").animate({ opacity: "1" }, 300);
        } else { $(".item-tips").remove(); }
    });
    $("body").on("mouseover mouseout", ".mini .nav-item li", function (e) {
        if (e.type == "mouseover") {
            var X = $(this).offset().top;
            var Y = $(this).offset().left;
            var info = $(this).find("span").text();
            var tips = '<div class="item-tips" style="top:' + X + 'px;left:' + 60 + 'px;">' + info + '</div>';
            $("body").append(tips);
            $(".item-tips").animate({ opacity: "1" }, 300);
        } else { $(".item-tips").remove(); }
    });

    $('.J_menuTabs').on("mousedown", ".active", function (e) {
        if (3 == e.which) {
            var X = e.clientX+5;
            var Y = e.clientY+5;
            $(".rc_menu").css({ "top": Y + "px", "left": X + "px" }).show();
       }
    });
    $("html").click(function () { $(".rc_menu").hide(); })
    $(".J_reload").click(function () {
        var at = $(".J_menuTabs a.active").attr("data-id");
        $("iframe[data-id=\"" + at + "\"]").attr("src", at);
    });



});
