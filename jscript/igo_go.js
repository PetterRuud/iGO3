tinyMCE.init({
// General options
mode : "textareas",
editor_selector : "tinymce",
theme : "advanced",
plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,phpimage",
// Theme options
theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,blockquote,|,undo,redo,|,link,unlink,anchor,phpimage,|,backcolor",
theme_advanced_buttons3 : "hr,removeformat,visualaid,|,charmap,emotions,media,|,insertdate,inserttime,|,preview,|,cleanup,code,fullscreen",
theme_advanced_buttons4 : "",
theme_advanced_toolbar_location : "top",
theme_advanced_toolbar_align : "left",
theme_advanced_statusbar_location : "bottom",
theme_advanced_resizing : true,
// Example content CSS (should be your site CSS)
content_css : "/style/site.css"
});

$(function () {
            $('.anythingSlider').anythingSlider({
                easing: "easeInOutExpo",
                autoPlay: true,
                delay: 15000,
                startStopped: false,
                animationTime: 600,
                hashTags: true,
                buildNavigation: true,
        		pauseOnHover: true
            });
        });

$(document).ready(function() {
	$("a.iframecontent").fancybox({
		'type' : 'iframe'
	});
	$("a.inlinecontent").fancybox();

});

$(document).ready(function() {
	$("#form").validate();
});

$(document).ready(function() {
$(".swap").focus(function() {
if( this.value == this.defaultValue ) {
this.value = "";
}
}).blur(function() {
if( !this.value.length || this.value == "" ) {
this.value = this.defaultValue;
}
});
});

