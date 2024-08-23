$(document).ready(function(){
    $.ajax({
        url: 'main.php?function=checklogin',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.type == '102'){
                $("#card5, #card6, #card7, #card8, #card10").hide();
            }
            if(response.type == '101' ){
                $("#card1, #card2, #card3, #card4, #card5, #card8, #card7, #card10").hide();
                $("#card6").show();
            }
            if(response.response == "logout"){
                $("#login").text("Logout");
                $("#login").off('click').click(function() {
                    $.ajax({
                        url: 'main.php?function=logout',
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if(response.response == "login"){
                                alert(response.response)
                                window.location.href = 'index.html';   
                            }else{
                                $("#login").text("Login").attr("href", "login.html");
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching data:', error);
                        }
                    });
                });     
            }else{
                $("#login").text("Login").attr("href", "login.html");
                $("#notificationimg").hide();
                $("#cards").hide()
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
    $.ajax({
        url: 'main.php?function=message',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if(data.length > 0 ){
                let arr = [];
                console.log(data);
                let msgContent = '';
                data.forEach(element => {
                    let x= `${element.portalname} (${element.purl}) deployment date is changed to ${element.new_date} : ${element.info}  `
                    msgContent += x + '<hr>';
                    if(element.view == 0){
                        arr.push(element.log_id);
                    }
                });
                if(arr.length > 0){
                    $("#notificationimg").attr('src','img/notification.png')
                }
                function updatemsg(){
                    arr.forEach(id=>{
                        $.ajax({
                            url: `main.php?function=readmessage&id=${id}`,
                            type: 'GET',
                            dataType: 'json',
                            success: function(data){
                            }
                        })
                    })
                }
                
                $("#msg").html(msgContent);
                notificationimg.addEventListener("click", function(){
                    myPopup.classList.add("show");
                    $("#notificationimg").attr('src','img/bell.png');
                    updatemsg();
                });
                closePopup.addEventListener("click",function () {
                    myPopup.classList.remove( "show");
                });
                window.addEventListener("click",function(event) {
                    if (event.target == myPopup) {
                        myPopup.classList.remove("show");
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
    notificationimg.addEventListener("click", function(){
        myPopup.classList.add("show");
        $("#notificationimg").attr('src','img/bell.png');
    });
    closePopup.addEventListener("click",function(){
        myPopup.classList.remove("show");
    });
    window.addEventListener("click", function(event){
        if (event.target == myPopup) {
            myPopup.classList.remove("show");
        }
    });
});