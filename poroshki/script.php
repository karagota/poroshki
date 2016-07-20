<?php
include_once($_SERVER['DOCUMENT_ROOT']."/poroshki/webstart.php");
header("Content-type:text/javascript");

?>
/*jslint unparam: true */
/*global window, $ */
$(function () {
    'use strict';
    // Change this to the location of your server-side upload handler:
    var url = '<?php echo $root_path;?>dist/jqupload/server/php/';
    $('#fileupload').fileupload({
        url: url,
        dataType: 'json',
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
			   if (file.error) {
					$("span.message").text(file.error).closest('.alert').removeClass('alert-success').addClass('alert-warning').show().find('span.glyphicon-remove').css('color','#8a6d3b');/*.delay( 1200 ).hide(800)*/;
			   } else $('span.message').html('<i class="glyphicon glyphicon-saved"></i>&nbsp;<?php echo $labels['avataruploaded'];?>').closest('.alert').removeClass('alert-warning').addClass('alert-success').show().find('span.glyphicon-remove').css('color','#3c763d');/*.delay(1200 ).hide(800)*/;
				$('#avatar').attr('src',url+'files/medium/'+file.name);
				$.get("<?php echo $root_path;?>ajax/avatar.php?file="+file.name);
            });
        }
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
});
Number.prototype.padLeft = function(base,chr){
	var  len = (String(base || 10).length - String(this).length)+1;
	return len > 0? new Array(len).join(chr || '0')+this : this;
}
function getFormatDate(d) {
	var curr_day = d.getDate().padLeft();
	var curr_month = (d.getMonth() + 1).padLeft(); //Months are zero based
	var curr_year = d.getFullYear();
	return curr_day + "." + curr_month + "." + curr_year;
}
function set_filter(id) {
	var d = new Date();
	$('#to').val(getFormatDate(d));
	var d0 = new Date();
	var days = 0;
	if (id=="day") {
		days = 1;
	} else if (id=='week') {
		days = 7;
	} else if (id=='month') {
		days = 30;
	} else if (id=='annual') {
		days = 365;
	} 
	d0.setDate(d0.getDate()-days);
	$('#from').val(getFormatDate(d0));
	$('.resetsgray').css("color","red");
	if (id=="alltime") {
		$('#from').val('');
		$('#to').val('');
		$('.resetsgray').removeAttr("style");
	}
	$("form.filtr .form-control").change();
}
$(".done").append('&nbsp;<span class="glyphicon glyphicon-ok"></span>');
$(document).ready(function () {
	$(document.body).tooltip({ selector: "[title]",placement: 'bottom', html: true });
	$(document.body).find('.tooltip-top').tooltip({ selector: "[title]",placement: 'top', html: true });
});
$.datepicker.setDefaults($.datepicker.regional['ru']);
$( ".datepicker" ).datepicker({ dateFormat: 'dd.mm.yy' });
$( ".datepicker_profile" ).datepicker({ dateFormat: 'dd.mm.yy', changeMonth: true, changeYear: true,  yearRange: "-100:+0"  });
$(".jumbotron").delegate("a","click",function(){
	$(".jumbotron .nav li" ).removeClass("active");
	$(this).parent('li').addClass("active");
	set_filter($(this).parent('li').attr('id'));
});
$("form.filtr").delegate(".clear","click",function(){
	$(this).find('span').removeClass('active');
	$(this).closest('.input-group').find('.form-control').val('').removeAttr('checked').prop('selectedIndex', 0).removeAttr('style').change();
	$(this).css('color','#AAA');
	$(this).find('span').removeAttr('style');
	
		
	
});

$("form.filtr").delegate(".form-control","change",function(){
	var color = '#AAA'; 
	if ($(this).val()!='' && $(this).val()!='0') color='red'; 
	$(this).next().find('a.clear').css('color',color);
	$(this).addClass('active');
	var type = $(this).attr('type');
	$('.row').last().load('?'+$("form.filtr").serialize()+' .pagecontent', function() {			
				$('.more').first().contents().unwrap();
				$('.pagecontent .pagecontent').first().contents().unwrap();
				if (type=='text') window.location = window.location.href.split('?')[0];
				
			});
});

	
$('.row').delegate('#loadmore','click',function (){
	$('#pagination').remove();
	var page = $(this).attr('class').split(' ').pop().split('-').pop();
	$('.more').last().load('/p'+page+' .pagecontent', function() {
	$('.more').first().contents().unwrap();
	$('.pagecontent .pagecontent').first().contents().unwrap();
	window.location.hash='#p'+page;
});
});
			
$(document).ready(function() {
   $('select').change(function() {
	   if ($(this).val()!='0') $(this).css('color','black');
	   else  $(this).css('color','#999');
   }); 
});
$(".pagination").delegate("li","click",function(){$(this).addClass('active').siblings().removeClass('active'); });
$(document).ready(function (global_annulate) {
	function transact_vote(event) {
	
		var target = event.currentTarget;
		var subject_type="article";
		var id = '';
		if ($(target).closest('div').hasClass('vote_comment')) 
		{
			subject_type = 'comment';
			id = $(target).closest('.vote_comment').attr('id').split('-')[1]; 
		} else id = $(target).parents('div.article').first().attr('id').split('-')[1];
		var annulate = '';
	   
		var message='<?php echo $labels['votecounted'];?>';
		if ($(target).hasClass('voted') || $(target).hasClass('voted-down')) {
			annulate='&annulate=1';
			message='<?php echo $labels['voteundone'];?>';
		}
		var alert_style ='alert-success';
		var direction='';
		var vote_class='';
		if ($(target).hasClass('vote-up')) {direction=1;vote_class="voted";}
		else if($(target).hasClass('vote-down')) { direction=-1; vote_class='voted-down';} 

		$(target).animate({'margin-left': "-5px",'margin-right':'5px'}, 200)
				 .animate({'margin-left': "0px",'margin-right':'0px'}, 200);
		$.get( '<?php echo $root_path;?>ajax/vote.php?id='+id+'&type='+subject_type+'&vote='+direction+annulate,
		function( data ) {
			if (data.error!='') {
				message=data.error;
				alert_style='alert-error';
				vote_class="";
			}
			else {
			
			$("#"+subject_type+"-"+id +' span.thumbs').html(data.thumb_html);
			
			$("#"+subject_type+"-"+id +' span.rating').html(data.article_rating);
			
			$( "#"+subject_type+"-"+id).closest('.comment').find(" .textcomment").removeClass('bad').removeClass('bad-1').removeClass('bad-2').removeClass('bad-3').removeClass('bad-4').removeClass('bad-5');
				
			if (data.grade_all<0)
					$( "#"+subject_type+"-"+id).closest('.comment').find(" .textcomment").addClass('bad').addClass('bad'+Math.max(-5,data.grade_all));
					
			if (data.grade_all==0)
				$(this).closest('.votebar').find(" .viewed").removeClass('viewer_voted');
			else $(this).closest('.votebar').find(" .viewed").addClass('viewer_voted');
				
			title = data.lastname + ' ' + data.name + '<br><?php echo $labels['rating'];?> '+ data.rating +'<br><?php echo $labels['of_articles'];?> ' + data.articles;
				
			$( "#"+subject_type+"-"+id +" span.author")
					.html('<a href="/author/' + data.author_id + '">' + data.nickname + '</a>')
					.attr('title',title)
					.attr('data-original-title',title);
					
			}
			$('body').find('.vote-message').removeClass('alert-success').removeClass('alert-error').addClass(alert_style).html(message).show().fadeOut(1600*4);
			
		}, "json");
		
		return false;
	};
	
	function add_fav(event) {
		var target = event.currentTarget;
		if ($(target).hasClass('favored')) return remove_fav(event);
		var id = $(target).parents('div.article').first().attr('id').split('-')[1];
		$.get( '<?php echo $root_path;?>ajax/edit_fav.php?id='+id,
		function( data ) {
			if (data.error!='') {
				message=data.error;
				alert_style='alert-error';
			}
			else {
			   
				$( "#article-"+id +" span.fav")
					.html( ' ');
				 $( "#article-"+id +" span.glyphicon-star-empty")
					.removeClass( 'glyphicon-star-empty' ).addClass('glyphicon-star').parent('a.add_fav').addClass('favored').attr('title','<?php echo $labels['remove_from_fav'];?>').attr('data-original-title','<?php echo $labels['remove_from_fav'];?>');
				
			}
		   
		}, "json");
	}
	
	function remove_fav(event) {
		var target = event.currentTarget;
		
		var id = $(target).attr('id').split('-')[1];
		$.get( '<?php echo $root_path;?>ajax/edit_fav.php?id='+id+'&remove=1',
		function( data ) {
			if (data.error!='') {
				message=data.error;
				alert_style='alert-error';
			}
			else {
			   
				 $( "#article-"+id +" span.glyphicon-star").removeClass('glyphicon-star').addClass('glyphicon-star-empty').parent('a.add_fav').removeClass('favored').attr('title','<?php echo $labels['add_to_fav'];?>').attr('data-original-title','<?php echo $labels['add_to_fav'];?>');
				 $( "#article-"+id +" span.fav").html( ' ');
				 $( target).parents('li').first().remove();
				
					
				
			}
		   
		}, "json");
	}
	
	function delete_comment(event) {
		var target = event.currentTarget;
		
		var id = $(target).parents('div.editcomment').attr('id').split('-')[1];
		$.get( '<?php echo $root_path;?>ajax/delete_comment.php?id='+id,
		function( data ) {
			if (data.error!='') {
				message=data.error;
				alert_style='alert-error';
			}
			else {
			   
				$(target).parents('.comment').first().remove();
					
				
			}
		   
		}, "json");
	}
	
	function edit_comment(event) {
		var target = event.currentTarget;
		var target_parent =  $(target).parents('.comment').first();
		var id = $(target).parents('div.editcomment').attr('id').split('-')[1];
		
		var text = $( target_parent).find('.textcomment').html();
		
		$(target_parent).find('.textcomment').html('<textarea  class="textareacomment" style="width:100%;">'+text+'</textarea><br /><button type="submit" class="btn btn-lg btn-primary save-editcomment" id="savecomment-'+id+'"><?php echo $labels['savecomment'];?></button>');
		$(target).parent('.editcomment').remove();
		
	}
	
	function save_comment(event) {
		var target = event.currentTarget;
		var id = $(target).attr('id').split('-')[1];
		var text = $(target).parents('.comment').find('.textareacomment').val();
		$.post( "<?php echo $root_path;?>ajax/edit_comment.php", { text: text, id: id }, function( data ) {
			$( target ).parents('.textcomment').first().html(text).parents('.comment').children().first().before('<div style=\"font-size:18px;text-align:right;float:right;\" class=\"editcomment\" id=\"editcomment-'+id+'\"><a href=\"#\" class=\"edit-comment\"  title=\"<?php echo $labels['edit_comment'];?>\"><span class=\"glyphicon glyphicon-pencil\"></span></a>&nbsp;&nbsp;<a href=\"#\" class=\"delete-comment\"  title=\"<?php echo $labels['delete_comment']; ?>\"><span class=\"glyphicon glyphicon-remove-circle\" style=\"color:red;\"></span></a></div>');
			
			
		});
		
	}
	
	function change_status(event) {
		var target = event.currentTarget;
		var id = $(target).attr('id').split('-')[1];
		
		$.get( "<?php echo $root_path;?>ajax/change_status.php?id="+id, function( data ) {
			location.reload();
		});
		
	}
	
	function delete_article(event) {
		var target = event.currentTarget;
		
		var id = $(target).attr('id').split('-')[1];
		
		$.get( '<?php echo $root_path;?>ajax/delete_article.php?id='+id,
		function( data ) {
			if (data.error!='') {
				message=data.error;
				alert_style='alert-error';
			}
			else {
			   
				$(target).parents('.draft').first().remove();
					
				
			}
		   
		}, "json");
	}
	
	
	function change_views(event) {
		var target = event.currentTarget;
		
		var id = $(target).parents('div.article').first().attr('id').split('-')[1];
		$.get( '<?php echo $root_path;?>ajax/views.php?id='+id,
		function( data ) {
			if (data.error!='') {
				message=data.error;
				alert_style='alert-error';
				$('body').find('.vote-message').removeClass('alert-success').removeClass('alert-error').addClass(alert_style).html(message).show().fadeOut(1600*4);
			}
			else {
			  //Поменять глазик с закрытого на открытый
			  if (data.viewed==1) {
			  
				$( "#article-"+id +" a.viewed span.glyphicon").removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open');
				 //Если просмотрел, то можно видеть автора.
				var title = data.lastname + ' ' + data.name + '<br><?php echo $labels['author_rating'];?> '+ data.rating +'<br><?php echo $labels['of_articles'];?> ' + data.articles;
				
				$( "#article-"+id +" span.author")
						.html('<a href="/author/' + data.author_id + '">' + data.nickname + '</a>')
						.attr('title',title)
						.attr('data-original-title',title);
			   }
			  else  {
				$( "#article-"+id +" a.viewed span.glyphicon").removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close');
				 //Если развидел, то убрать автора. Учесть, что если голосовал или сам автор, то 
				 //то можно не убирать.
				 //Если проголосовал, то увидел. Можно ли развидеть проголосованный пирожок?
				/*var title = '<?php echo $labels['vote_to_see_who_favoured_this'];?>';*/
				var title = '<?php echo $labels['authors_restricted_author_name']; ?>';
				$( "#article-"+id +" span.author")
						.html('———————')
						.attr('title',title)
						.attr('data-original-title',title);
			 }
			  //Поменять в типе количество просмотров
			  var title = data.views+' '+'<?php echo $labels['of_views']; ?>';
			  $( "#article-"+id +" a.viewed span.of_views").attr('title',title).attr('data-original-title',title);
			  
			 
						
					
				
				
			}
		   
		}, "json");
		
	}

	$("body").delegate("a.vote-up","click",function(e){transact_vote(e);/*if($("#checked").val()!='0') $('.row').last().load('?'+$("form.filtr").serialize()+' .pagecontent', function() {			
				$('.more').first().contents().unwrap();
				$('.pagecontent .pagecontent').first().contents().unwrap();
			});*/return false;});

	$("body").delegate("a.vote-down","click",function(e){transact_vote(e);
	/*if($("#checked").val()!='0') $('.row').last().load('?'+$("form.filtr").serialize()+' .pagecontent', function() {			
				$('.more').first().contents().unwrap();
				$('.pagecontent .pagecontent').first().contents().unwrap();
			});*/return false;});
	
	$("body").delegate("a.add_fav","click",function(e){add_fav(e);return false;});
	
	$("body").delegate("a.remove_fav","click",function(e){remove_fav(e);return false;});
	
	$("body").delegate("a.edit-comment","click",function(e){edit_comment(e);return false;});
	
	$("body").delegate("a.delete-comment","click",function(e){delete_comment(e);return false;});
	
	$("body").delegate("button.save-editcomment","click",function(e){save_comment(e);return false;});
	 
	$("body").delegate("a.change-status","click",function(e){change_status(e);return false;});
	
	$("body").delegate("a.delete-article","click",function(e){delete_article(e);return false;});
	
	$("body").delegate("a.viewed.viewer_voted","click",function(e){$('body').find('.vote-message').removeClass('alert-success').addClass('alert-error').html('<?php echo $labels['no_right_to_unview_voted'];?>').show().fadeOut(1600*4);return false;});
	
	$("body").delegate(" a.viewed.viewer_is_author","click",function(e){$('body').find('.vote-message').removeClass('alert-success').addClass('alert-error').html('<?php echo $labels['no_right_to_unview_own'];?>').show().fadeOut(1600*4);return false;});

	
	$("body").delegate("a.viewed:not(.viewer_voted,.viewer_is_author)","click",function(e){change_views(e);/*$('.row').last().load('?'+$("form.filtr").serialize()+' .pagecontent', function() {			
				$('.more').first().contents().unwrap();
				$('.pagecontent .pagecontent').first().contents().unwrap();
			});*/return false;});
   
});

$(function(){
function initToolbarBootstrapBindings() {
  var fonts = ['Serif', 'Sans', 'Arial', 'Arial Black', 'Courier', 
		'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande', 'Lucida Sans', 'Tahoma', 'Times',
		'Times New Roman', 'Verdana'],
		fontTarget = $('[title=Шрифт]').siblings('.dropdown-menu');
  $.each(fonts, function (idx, fontName) {
	  fontTarget.append($('<li><a data-edit="fontName ' + fontName +'" style="font-family:\''+ fontName +'\'">'+fontName + '</a></li>'));
  });
	$('.dropdown-menu input').click(function() {return false;})
		.change(function () {$(this).parent('.dropdown-menu').siblings('.dropdown-toggle').dropdown('toggle');})
	.keydown('esc', function () {this.value='';$(this).change();});

  $('[data-role=magic-overlay]').each(function () { 
	var overlay = $(this), target = $(overlay.data('target')); 
	overlay.css('opacity', 0).css('position', 'absolute').offset(target.offset()).width(target.outerWidth()).height(target.outerHeight());
  });
  if ("onwebkitspeechchange"  in document.createElement("input")) {
	var editorOffset = $('#editor').offset();
	$('#voiceBtn').css('position','absolute').offset({top: editorOffset.top, left: editorOffset.left+$('#editor').innerWidth()-35});
  } else {
	$('#voiceBtn').hide();
  }
}; 
function showErrorAlert (reason, detail) {
	var msg='';
	if (reason==='unsupported-file-type') { msg = "Unsupported format " +detail; }
	else {
		console.log("error uploading file", reason, detail);
	}
	$('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>'+ 
	 '<strong>File upload error</strong> '+msg+' </div>').prependTo('#alerts');
	};
	initToolbarBootstrapBindings();  
	$('#editor').wysiwyg({ fileUploadError: showErrorAlert} );
	window.prettyPrint && prettyPrint();
	$('#editor').cleanHtml();
	$('.postform').submit(function() {
		$('#editortext').val($('#editor').cleanHtml())
	});
});
  
$("#categselect").change(function(){$("#categselect option:selected").each(function(){
	var cat = $(this).val(); 
	//alert(cat);
	$("#subcategselect").load('<?php echo $root_path;?>ajax/subcat.php?cat='+cat); 

});});

var config = {
  '.chosen-select'           : {},
  '.chosen-select-deselect'  : {allow_single_deselect:true},
  '.chosen-select-no-single' : {disable_search_threshold:10},
  '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
  '.chosen-select-width'     : {width:"95%"}
}

for (var selector in config) {
  $(selector).chosen(config[selector]);
}

$(".rating").delegate(".rating_add_param a","click",function(){
	var letter = $(this).closest('table').find('tr.rating_param').length;
	function letter_name(n) {
		var s = "";
		while(n >= 0) {
			s = String.fromCharCode(n % 26 + 97) + s;
			n = Math.floor(n / 26) - 1;
		}
		return s;
	}
	letter = letter_name(letter);
	$(this).closest('tr').after('<tr class="rating_param"> <td valign="top" width="400"><label>где '+letter+' = </label></td> <td> <select name="func_'+letter+'"> <option value="Sum_R" >Сумма рейтинга</option> <option value="count" >Количество</option> <option value="mean_R" selected>Средний рейтинг</option> </select> <select type="text" name="'+letter+' "> <option value="articles" >Статей</option> <option value="authors" selected>Авторов</option> <option value="comments">Комментариев</option> <option value="votes">Оценок</option> <option value="cats">Категории</option> </select> </td> </tr> <tr> <td valign="top" width="400" colspan="2"><label>, отфильтрованных по:</label></td> </tr> <tr> <td valign="top" width="400"><input type="checkbox" name="'+letter+'_filter_author" checked /><label>автору c условием</label></td> <td><input type="'+letter+'_filter_author_cond" value="пишущий автор" /></td> </tr> <tr> <td valign="top" width="400"><input type="checkbox" name="'+letter+'_filter_date" checked /><label>дате публикации c условием не старше</label></td> <td><input type="'+letter+'_filter_date_cond" value="90" />дней</td> </tr> <tr> <td valign="top" width="400"> <input type="checkbox" name="'+letter+'с_filter_rating" checked /><label>рейтингу c условием не меньше</label></td> <td><input type="'+letter+'_filter_rating_cond" value="top 33%" /></td> </tr> <tr> <td valign="top" width="400"><input type="checkbox" name="'+letter+'_filter_cat" /><label>категории c условием</label></td> <td><input type="'+letter+'_filter_cat_cond" value="все категории" /></td> </tr><tr><td colspan="2"><hr/></td></tr> <tr><td colspan="2"><div class="rating_add_param"><a href="#">Добавить параметр</a></div></td></tr>').remove();
	return false;});

	$('#poroshok').delegate('button[name=publish]','click',function(){
		function num_vowels(text) {
		
			var total_vowels=0;
			for (var i = 0; i < text.length; i++) {
				if (text.charAt(i).match(/[А-яЁё]/) != null) {
					if (text.charAt(i).match(/[аоуыэяёюиеАОУЫЭЯЁЮИЕ]/)) {
					
						total_vowels++;
					}
				}
			}
			return total_vowels;
		}
		var flag=true;
		$('#poroshok .poroshok').each(function(index){
				var text =$(this).val().trim().split("\n");
				var rules = [9,8,9,2];
				for (i = 0; i < text.length; ++i) {
						//alert(text[i]);
					if (num_vowels(text[i])!=rules[i]) {
					alert ('<?php echo $labels['wrong_syllables'];?> '+(i+1) +" ("+ num_vowels(text[i]) +", а <?php echo $labels['mustbe'];?> "+rules[i]+")");
					flag=false; return false;
			}
				}

		});
		return flag;
		
	});
	
