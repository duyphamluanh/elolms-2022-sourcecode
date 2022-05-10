
$('#blocksliderbutton').click(function(){
    var blockslideropen = localStorage.getItem('blockslideropen');
    
    if (blockslideropen == 1) {
        localStorage.setItem('blockslideropen', 0);
    }
    else {
        localStorage.setItem('blockslideropen', 1);        
    }

});

if (localStorage.getItem('blockslideropen') == 1) {
    $('#blockslider').addClass('show');
}

// Luu trang thai collapse 
$('#amycourses').click(function(){
    var collapsecourseopen = localStorage.getItem('collapsecourse');
    
    if (collapsecourseopen == 1) {
        localStorage.setItem('collapsecourse', 0);
    }
    else {
        localStorage.setItem('collapsecourse', 1);        
    }

});

if (localStorage.getItem('collapsecourse') == 1) {
    $('#collapsemycourses').addClass('show');
}
