// JavaScript Document\
$(document).ready(function (e) {
	$('.agenda .btns .slick').slick({
		dots: false,
		arrows: false,
		autoplay: false,
		autoplaySpeed: 3000,
		speed: 1000,
		centerMode: true,
		slidesToShow: 10,
		slidesToScroll: 1,
		centerMode: false,
		centerPadding: '0px',
		infinite: false,
		lazyLoad: 'ondemand',
		responsive: [{
			breakpoint: 1200,
			settings: {
				slidesToShow: 8,
				infinite: false,
				dots: false,
				arrows: false,
				dots: true
			}
		},
			{
				breakpoint: 959,
				settings: {
					slidesToShow: 6,
					infinite: false,
					dots: false,
					arrows: false,
					dots: true
				}
			},
			{
				breakpoint: 620,
				settings: {
					slidesToShow: 4,
					infinite: false,
					dots: false,
					arrows: false,
					dots: true
				}
			}
		]
	});



	$('.reviewList').flexslider({
		animation: "slide",
		easing: "swing",
		directionNav: true,
		slideshowSpeed: 5000, // 自动播放速度毫秒
		animationSpeed: 1200, //滚动效果播放时长
		touch: true

	});

	//phone-nav
	$('.menuBtn').append('<b></b><b></b><b></b>');
	$(".menuBtn").click(function () {
		$('body').toggleClass('fixme');
		$(".header .nav").slideToggle(400);
		$(".header .nav li").addClass('animated ' + ' fadeInUp');
	});

	//我要赞助
	if ($(window).width() < 1200) {
		$(".spBtn").click(function () {
			event.stopPropagation();
			$(this).toggleClass("cur");
			if ($(this).hasClass("cur")) {
				$(this).find(".emailDiv").addClass("animated fadeInRight").fadeIn(400);
			} else {
				$(this).find(".emailDiv").fadeOut(400);
			}
		})
		$(document).click(function () {
			$(".emailDiv").fadeOut(400);
			$(".spBtn").removeClass("cur");
		});

	}

	$(function () {

	});
	//平滑
	/*
	 $('a[href*=#],area[href*=#]').click(function() {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            var $target = $(this.hash);
            $target = $target.length && $target || $('[name=' + this.hash.slice(1) + ']');
            if ($target.length) {
                var targetOffset = $target.offset().top;
                $('html,body').animate({
                    scrollTop: targetOffset
                },
                1000);
                return false;
            }
        }
    });*/
	;
	(function () {
		//峰会议程tab
		/*$(".agenda .btns li:first-child").addClass("hover");
		  $(".aList").eq(0).show();
		  $(".agenda .btns li").click(function(){
			  $(".agenda .btns li").eq($(this).index()).addClass("hover").siblings().removeClass('hover');
			  $(".aList").fadeOut(400).eq($(this).index()).addClass('animated '+' zoomIn').fadeIn(400);
		});*/



		var tab = $(".agenda .btns .slick-track");
		if (!tab.length) return;

		var tabQuery = tab.children(".slick-slide");
		var con = $(".catQuery");
		var query = con.children(".aList");
		query.each(function () {
			$(this).data("height", $(this).height());
		});

		tabQuery.eq(0).addClass("hover");
		query.not(":eq(0)").css("display", "none");

		tabQuery.click(function () {
			tabQuery.removeClass("hover");
			var index = $(this).addClass("hover").index();
			TweenLite.to(query.not(":eq(" + index + ")"), 0.5, {
				"height": 0,
				"display": "none"
			});
			TweenLite.to(query.eq(index), 0.5, {
				"height": query.eq(index).data("height"),
				"display": "block"
			});
		})

	})();

	//订阅会议动态弹窗
	$(".footfix .stateBtn").on("click", function () {
		target = $(this);
		TweenMax.to($(".mask"), 0.2, {
			"display": "block",
			"opacity": 0.5,
			"onComplete": function () {
				TweenMax.to($("#stateBox"), 0.5, {
					"top": '50%',
					"display": "block",
					"ease": Back.easeOut.config(2),
					"onComplete": function () {

					}
				});
			}
		});
	});

	$(".cenBtn").click(function () {
		TweenMax.to($("#stateBox"), 0.5, {
			"top": -310,
			"display": "none",
			"ease": Back.easeIn.config(2),
			"onComplete": function () {
				TweenMax.to($(".mask"), 0.2, {
					"display": "none",
					"opacity": 0
				});
			}
		});
	})
	// 登录方式切换
	$('.y-register-nav li').on('click', function () {
		var num = $(this).attr('num');
		$('.y-register-nav li').removeClass('active');
		$(this).addClass('active');
		$('.y-register-bx').hide();
		$('.y-register-bx' + num).toggle();
	});
	// 取消登录
	$(".cancelLogin").click(function () {
		TweenMax.to($("#loginBox"), 0.5, {
			"top": -310,
			"display": "none",
			"ease": Back.easeIn.config(2),
			"onComplete": function () {
				TweenMax.to($(".mask"), 0.2, {
					"display": "none",
					"opacity": 0
				});
			}
		});
	})

});