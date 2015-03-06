$(function(){
	if(typeof html_load == 'function'){
		html_load();
	}
})
function ajax_post(url,data,fn){
	return $.ajax({
		type:"POST",url:url,data:data,cache:false,dataType:'json',
		success:fn
	})
}
function ajax_get(url,data,fn){
	return $.ajax({
		type:"GET",url:url,data:data,cache:false,dataType:'json',
		success:fn
	})
}
function format_string(str,param){
	var reg = /{([^{}]+)}/gm;
	str=str.replace(reg, function(match, name) {
		return param[name];
	})
	return str;
}
function get_random(min,max){
	return Math.ceil(Math.rand()*min)+max;
}
function test_reg(val,reg){
	var pat=reg;
	if(reg=='tel'){
		pat=/^1(\d{10})$/;
	}else if(reg=='mail'){
		pat=/^([\w-_]+(?:\.[\w-_]+)*)@((?:[a-z0-9]+(?:-[a-zA-Z0-9]+)*)+\.[a-z]{2,6})$/i;
	}	
	return pat.test(val);
}