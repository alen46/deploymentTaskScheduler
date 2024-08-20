$(document).ready(function(){

    $(".hamburger").click(function(){
        $(this).toggleClass("active");
        $(".nav-menu").toggleClass("active");
    });

        $(".nav-link").click(function(){
        $(".hamburger").removeClass("active");
        $(".nav-menu").removeClass("active");
    });

    $.ajax({
        url: 'main.php?function=fetchportal&from=usr',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var $select = $('#usrsel');
            $.each(data, function(index, item) {
                $select.append($('<option>', {
                    value: item.userid,
                    text: item.username
                }));
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
    $.ajax({
        url: 'main.php?function=fetchoptions',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var $select = $('#typesel');
            $.each(data, function(index, item) {
                $select.append($('<option>', {
                    value: item.typeid,
                    text: item.typename
                }));
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
     });

    $("#usrbtn").click(()=>{
        let usrdata ={
            'usr':$("#usrsel").val(),
            'function' : 'fetchportal',
            'from': 'usr2',
        };
        $.ajax({
            url: `main.php`,
            type: 'POST',
            dataType: 'json',
            data: usrdata,
            success: function(data) {
                console.log(data[0]);
                $("#usrname").val(data[0]['username']);
                $("#usremail").val(data[0]['email']);
                $("#usrphone").val(data[0]['phone']);
                $("#typesel").val(data[0]['type']);
                $("#id").val(data[0]['userid']);
            },
            error: function(xhr, textStatus, errorThrown){
                alert("An error occurred: " + xhr.status + " " + xhr.statusText);
                console.error("Error:", xhr, textStatus, errorThrown);
            }

        });
    });

    $('#editusr').on('submit', function(e) {
                e.preventDefault(e); 
                let formData = new FormData(this);
                formData.append('function',"edituser");
                console.log(formData);
                $.ajax({
                    type: "POST",
                    url: "main.php",
                    data: formData,
                    dataType: "json",
                    processData: false, 
                    contentType: false, 
                    success: function(response) { 
                        window.alert(response.message); 
                        console.log(response);
                        window.location.href = 'index.html';
                    },
                    error: function(xhr, textStatus, errorThrown){
                        alert("An error occurred: " + xhr.status + " " + xhr.statusText);
                        console.error("Error:", xhr, textStatus, errorThrown);
                    }
                });
                return false;
    });

});