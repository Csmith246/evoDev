
if(jQuery('#sq_settings').length>0){sq_blocksettings();}else{jQuery(document).ready(function(){sq_blocksettings();});}
function sq_blocksettings(){var snippet_timeout;jQuery("#sq_goto_dashboard").bind('click',function(){location.href="?page=sq_dashboard";});jQuery("#sq_goto_settings").bind('click',function(){location.href="?page=squirrly";});jQuery('#sq_automatically').bind('click',function(){jQuery('#sq_customize_settings').hide();jQuery('#sq_snippet_disclaimer').hide();jQuery('#sq_title_description_keywords').removeClass('sq_custom_title');});jQuery('#sq_settings_form').find('input[type=radio]').bind('click',function(){sq_submitSettings();sq_getSnippet();});jQuery('#sq_customize').bind('click',function(){jQuery('#sq_customize_settings').show();jQuery('#sq_snippet_disclaimer').show();jQuery('#sq_title_description_keywords').addClass('sq_custom_title');});jQuery('#sq_settings').find('input[name=sq_fp_title]').bind('keyup',function(){if(snippet_timeout){clearTimeout(snippet_timeout);}
snippet_timeout=setTimeout(function(){sq_submitSettings();sq_getSnippet();},1000);sq_trackLength(jQuery(this),'title');});jQuery('#sq_settings').find('textarea[name=sq_fp_description]').bind('keyup',function(){if(snippet_timeout){clearTimeout(snippet_timeout);}
snippet_timeout=setTimeout(function(){sq_submitSettings();sq_getSnippet();},1000);sq_trackLength(jQuery(this),'description');});if(jQuery('#sq_settings').find('input[name=sq_auto_seo]').length>0){sq_getSnippet();}
jQuery('#sq_auto_favicon1').bind('click',function(){jQuery('#sq_favicon').slideDown('fast');});jQuery('#sq_auto_favicon0').bind('click',function(){jQuery('#sq_favicon').slideUp('fast');});jQuery('#sq_use_on').bind('click',function(){jQuery('#sq_settings .sq_seo_switch_condition').show();jQuery('#sq_title_description_keywords').slideDown('fast');jQuery('#sq_social_media').slideDown('fast');jQuery('#sq_favicon').slideDown('fast');if(parseInt(jQuery('.sq_count').html())>0){var notif=(parseInt(jQuery('.sq_count').html())-1);if(notif>0){jQuery('.sq_count').html(notif);}else{jQuery('.sq_count').html(notif);jQuery('.sq_count').hide();}}
jQuery('#sq_fix_auto').slideUp('show');});jQuery('#sq_use_off').bind('click',function(){jQuery('#sq_settings .sq_seo_switch_condition').hide();jQuery('#sq_title_description_keywords').slideUp('fast');jQuery('#sq_social_media').slideUp('fast');jQuery('#sq_favicon').slideUp('fast');if(parseInt(jQuery('.sq_count').html())>=0){var notif=(parseInt(jQuery('.sq_count').html())+1);if(notif>0){jQuery('.sq_count').html(notif).show();}}
jQuery('#sq_fix_auto').slideDown('show');});jQuery('#sq_google_index1').bind('click',function(){if(parseInt(jQuery('.sq_count').html())>0){var notif=(parseInt(jQuery('.sq_count').html())-1);if(notif>0){jQuery('.sq_count').html(notif);}else{jQuery('.sq_count').html(notif);jQuery('.sq_count').hide();}}
jQuery('#sq_fix_private').slideUp('show');});jQuery('#sq_google_index0').bind('click',function(){if(parseInt(jQuery('.sq_count').html())>=0){var notif=(parseInt(jQuery('.sq_count').html())+1);if(notif>0){jQuery('.sq_count').html(notif).show();}}
jQuery('#sq_fix_private').slideDown('show');});jQuery('#sq_auto_twitter1').bind('click',function(){jQuery('#sq_twitter_account').show();});jQuery('#sq_auto_twitter0').bind('click',function(){jQuery('#sq_twitter_account').hide();});jQuery('.sq_login_link').bind('click',function(){var previewtop=jQuery('#sq_settings_login').offset().top-100;jQuery('html,body').animate({scrollTop:previewtop},1000);});}
function sq_showSaved(){}
function sq_trackLength(field,type){var min=0;var max=0;if(typeof field==='undefined')
return;if(type==='title'||type==='wp_title'){min=10;max=75;}else
if(type==='description'){min=70;max=165;}
if(min>0&&min>field.val().length)
jQuery('#sq_'+type+'_info').html(__snippetshort);else
if(max>0&&max<field.val().length)
jQuery('#sq_'+type+'_info').html(__snippetlong);else
if(max>0){jQuery('#sq_'+type+'_info').html(field.val().length+'/'+max);}}
function sq_getSnippet(url,show_url){if(typeof url==='undefined')
url='';if(typeof sq_blogurl!=='undefined')
url=sq_blogurl;if(typeof show_url==='undefined')
show_url='';jQuery('#sq_snippet_ul').addClass('sq_minloading');jQuery('#sq_snippet_title').html('');jQuery('#sq_snippet_url').html('');jQuery('#sq_snippet_description').html('');jQuery('#sq_snippet_keywords').hide();jQuery('#sq_snippet').show();jQuery('#sq_snippet_update').hide();jQuery('#sq_snippet_customize').hide();jQuery('#ogimage_preview').hide();setTimeout(function(){jQuery.getJSON(sqQuery.ajaxurl,{action:'sq_get_snippet',url:url,nonce:sqQuery.nonce}).success(function(response){jQuery('#sq_snippet_ul').removeClass('sq_minloading');jQuery('#sq_snippet_update').show();jQuery('#sq_snippet_customize').show();jQuery('#sq_snippet_keywords').show();jQuery('#ogimage_preview').show();if(response){jQuery('#sq_snippet_title').html(response.title);if(show_url!=='')
jQuery('#sq_snippet_url').html('<a href="'+url+'" target="_blank">'+show_url+'</a>');else
jQuery('#sq_snippet_url').html(response.url);jQuery('#sq_snippet_description').html(response.description);}}).error(function(){jQuery('#sq_snippet_ul').removeClass('sq_minloading');jQuery('#sq_snippet_update').show();}).complete(function(){jQuery('#sq_snippet_ul').removeClass('sq_minloading');jQuery('#sq_snippet_update').show();});},500);}
function sq_submitSettings(){jQuery.getJSON(sqQuery.ajaxurl,{action:'sq_settings_update',sq_use:jQuery('#sq_settings').find('input[name=sq_use]:checked').val(),sq_auto_title:jQuery('#sq_settings').find('input[name=sq_auto_title]:checked').val(),sq_auto_description:jQuery('#sq_settings').find('input[name=sq_auto_description]:checked').val(),sq_auto_canonical:jQuery('#sq_settings').find('input[name=sq_auto_canonical]:checked').val(),sq_auto_sitemap:jQuery('#sq_settings').find('input[name=sq_auto_sitemap]:checked').val(),sq_auto_meta:jQuery('#sq_settings').find('input[name=sq_auto_meta]:checked').val(),sq_auto_favicon:jQuery('#sq_settings').find('input[name=sq_auto_favicon]:checked').val(),sq_auto_facebook:jQuery('#sq_settings').find('input[name=sq_auto_facebook]:checked').val(),sq_auto_twitter:jQuery('#sq_settings').find('input[name=sq_auto_twitter]:checked').val(),sq_twitter_account:jQuery('#sq_settings').find('input[name=sq_twitter_account]').val(),sq_auto_seo:jQuery('#sq_settings').find('input[name=sq_auto_seo]:checked').val(),sq_fp_title:jQuery('#sq_settings').find('input[name=sq_fp_title]').val(),sq_fp_description:jQuery('#sq_settings').find('textarea[name=sq_fp_description]').val(),sq_fp_keywords:jQuery('#sq_settings').find('input[name=sq_fp_keywords]').val(),ignore_warn:jQuery('#sq_settings').find('input[name=ignore_warn]:checked').val(),sq_keyword_help:jQuery('#sq_settings').find('input[name=sq_keyword_help]:checked').val(),sq_keyword_information:jQuery('#sq_settings').find('input[name=sq_keyword_information]:checked').val(),sq_google_country:jQuery('#sq_settings').find('select[name=sq_google_country] option:selected').val(),sq_google_country_strict:jQuery('#sq_settings').find('input[name=sq_google_country_strict]:checked').val(),sq_google_plus:jQuery('#sq_settings').find('input[name=sq_google_plus]').val(),sq_google_wt:jQuery('#sq_settings').find('input[name=sq_google_wt]').val(),sq_google_analytics:jQuery('#sq_settings').find('input[name=sq_google_analytics]').val(),sq_facebook_insights:jQuery('#sq_settings').find('input[name=sq_facebook_insights]').val(),sq_bing_wt:jQuery('#sq_settings').find('input[name=sq_bing_wt]').val(),sq_pinterest:jQuery('#sq_settings').find('input[name=sq_pinterest]').val(),sq_alexa:jQuery('#sq_settings').find('input[name=sq_alexa]').val(),sq_sla:jQuery('#sq_settings').find('input[name=sq_sla]:checked').val(),sq_keywordtag:jQuery('#sq_settings').find('input[name=sq_keywordtag]:checked').val(),sq_local_images:jQuery('#sq_settings').find('input[name=sq_local_images]:checked').val(),nonce:sqQuery.nonce});}
function sq_getUserStatus(api_url,token){jQuery('#sq_userinfo').addClass('sq_loading');jQuery('#sq_userstatus').addClass('sq_loading');jQuery.getJSON(api_url+'sq/user/status/?callback=?',{token:token,lang:(document.getElementsByTagName("html")[0].getAttribute("lang")||window.navigator.language)}).success(function(response){if(typeof response.error!=='undefined')
if(response.error==='invalid_token'){jQuery.getJSON(sqQuery.ajaxurl,{action:'sq_reset',nonce:sqQuery.nonce}).success(function(response){if(typeof response.reset!=='undefined')
if(response.reset==='success')
location.href="?page=sq_howto";});}
jQuery('#sq_userinfo').removeClass('sq_loading').removeClass('sq_error');jQuery('#sq_userstatus').removeClass('sq_loading').removeClass('sq_error');if(typeof response.info!=='undefined'&&response.info!==''){jQuery('#sq_userinfo').html(response.info);}
if(typeof response.stats!=='undefined'&&response.stats!==''){jQuery('#sq_userstatus').html(response.stats);}
if(typeof response.data!=='undefined'&&typeof response.data.user_registered_date!=='undefined'){var currentDate=new Date();var day=currentDate.getDate();if(day.toString().length===1)
day='0'+day.toString();var month=currentDate.getMonth()+1;if(month.toString().length===1)
month='0'+month.toString();var year=currentDate.getFullYear();var currDate=year+'-'+month+'-'+day;var passed=((new Date(currDate).getTime()-new Date(response.data.user_registered_date).getTime())/(24*60*60*1000));;if(passed<=3&&jQuery('#sq_survey').length>0)
jQuery('#sq_survey').show();}}).error(function(){jQuery('#sq_userinfo').html('');jQuery('#sq_userstatus').html('');});}
function sq_recheckRank(post_id){jQuery('.sq_rank_column_button_recheck').hide();jQuery('#sq_rank_value'+post_id).html('').addClass('sq_loading');jQuery.getJSON(sqQuery.ajaxurl,{action:'sq_recheck',post_id:post_id,nonce:sqQuery.nonce}).success(function(response){if(typeof response.rank!=='undefined'){jQuery('#sq_rank_value'+post_id).html(response.rank).removeClass('sq_loading');}else{jQuery('#sq_rank_value'+post_id).html('Error').removeClass('sq_loading');}
setTimeout(function(){jQuery('.sq_rank_column_button_recheck').show();},10000)}).error(function(){jQuery('#sq_rank_value'+post_id).html('Error').removeClass('sq_loading');jQuery('.sq_rank_column_button_recheck').show();});}