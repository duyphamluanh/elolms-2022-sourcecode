define(["jquery","core/ajax","core/config","block_navigation/ajax_response_renderer"],function(a,b,c,d){var e=71,f=c.wwwroot+"/lib/ajax/getsiteadminbranch.php";return{load:function(b){b=a(b);var g=a.Deferred(),h={type:e,sesskey:c.sesskey},i={type:"POST",dataType:"json",data:h};return a.ajax(f,i).done(function(a){d.render(b,a),g.resolve()}),g}}});