/**
 * Created by GuoYue on 2016/5/21.
 */
$(function(){
    //team
    (function(){
        var dialog = $(".teamDetaile");

        if ( !dialog.length ) return;
        var maxheight = $(document).height();
        var dialogTitle = dialog.find(".tName");
        var dialogLabel = dialog.find(".tInfo");
		var dialogLabel2 = dialog.find(".tInfo2");
        var dialogContent = dialog.find(".tContent");
        var dialogTxt = dialog.find(".teamTxt");
        var dialogimg = dialog.find(".timg");
        var inner = dialog.find(".tContentInner");
        var dialogClose = dialog.find(".CloseBtn");
        var mask = $(".mask");
        var target = null;
        var content = null;

        //dialogContent.mCustomScrollbar();
        dialogTxt.mCustomScrollbar();

        $(".gList ul li").on("click", function(){
            target = $(this);
            content=target.find(".text-content").appendTo( inner );
            //console.log(content);
            var $img = target.find(".img img").attr('src');
  
            dialogLabel.text( target.find(".name").text());
			dialogLabel2.text( target.find(".job").text());
            dialogimg.attr('src',$img);
            TweenMax.to( mask, 0.2, {
                "display" : "block",
                "opacity" : 0.5,
                "onComplete" : function(){
                    TweenMax.to(dialog, 0.5, {
                        "top" : '50%',
                        "display" : "block",
                        "ease" : Back.easeOut.config(2),
                        "onComplete" : function(){
                            //dialogContent.mCustomScrollbar("update");
                        }
                    });
                }
            });
        });
        dialogClose.on("click", function(){
            TweenMax.to(dialog, 0.5, {
                "top" : -430,
                "display" : "none",
                "ease" : Back.easeIn.config(2),
                "onComplete" : function(){
                    TweenMax.to( mask, 0.2, {
                        "display" : "none",
                        "opacity" : 0
                    });
                    content.appendTo( target.find("a") );   
                    target = null;
                    content = null;
					
                }
				
            });
			
        });
    })();
});
