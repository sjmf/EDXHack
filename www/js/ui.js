
window.Game.util = {};
var util = window.Game.util;
var $ = jQuery;

util.achieve = function(msg, type) {
    var type = type || 'info';
    var $msg = jQuery('<div class="alert alert-'+type+' game-alert" role="alert"><p>'+msg+'</p></div>');
    jQuery('#game').append($msg);
    setTimeout(function(){$msg.addClass('in')},200);
    setTimeout(function(){$msg.addClass('out');setTimeout(function(){$msg.remove()},2000)}, 3000);
};

//setTimeout(function(){window.Game.util.achieve('Play!','success');},3000);
//setTimeout(function(){window.Game.util.achieve('Oh noes!','success');},5000);



setInterval(function(){
    // random things!
    
    var msgs = [
        {m:'Game on!',t:'success'},
        {m:'Oh noes, <insert city here> has just been consumed by smog!',t:'danger'},
        {m:'They\'re coming.',t:'warning'}
    ];
    
    var r = Math.floor(Math.random()*msgs.length);
    
    util.achieve(msgs[r].m, msgs[r].t);
    
}, 5000);
