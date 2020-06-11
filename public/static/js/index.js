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

	//倒计时
	function settime(val,color,url) {
		var element = $(val).closest('form').find("input[name='mobile']"),timer = {};
		var mobile_prefix = $(val).closest('form').find("select[name='mobile_prefix'] option:selected");
		if (mobile_prefix.val() === "") {
			layer.msg("请选择国家区号",{icon:2, time:1200});
			return false;
		}
		if (element.val() === "") {
			layer.msg("手机号不能为空！",{icon:2,time:1200});
			element.focus();
			return false;
		}
		// if (!element.val().match(/^1[3-9]\d{9}$/)) {
		//     layer.msg("请输入正确的手机号",{icon:2,time:1200});
		//     element.focus();
		//     return false;
		// }
		$btn = true;

		if(val.getAttribute("class")=='y-verCode'){
			$btn = false;
		}
		$btn && (val.style.backgroundColor=color);
		val.setAttribute("disabled", true);
		val.innerText="发送中...";
		$.ajax({
			url:url,
			type: 'POST',
			data: {mobile_prefix: mobile_prefix.val(),mobile: element.val()},
			success: function (res) {
				if (res.code == 2) {
					layer.msg(res.msg);
					val.removeAttribute("disabled");
					val.innerText="发送短信验证码";
					$btn && (val.style.backgroundColor="#f75959");
					return false;
				}
				clearInterval(timer['mobile']);
				var seconds = 60;
				timer['mobile'] = setInterval(function () {
					seconds--;
					if (seconds <= 0) {
						clearInterval(timer['mobile']);
						val.removeAttribute("disabled");
						val.innerText="发送短信验证码";
						$btn && (val.style.backgroundColor="#f75959");
					} else {
						val.setAttribute("disabled", true);
						val.innerText=seconds + "s再次获取验证码";
						$btn && (val.style.backgroundColor=color);
					}
				}, 1000);
			},
			error: function() {
				val.removeAttribute("disabled");
				val.innerText="发送短信验证码";
				$btn && (val.style.backgroundColor="#f75959");
			}
		})
		return false;
	}

});