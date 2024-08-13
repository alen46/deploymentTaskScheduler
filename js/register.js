$(document).ready(function(){
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
    $('#addform').on('submit', function(e) {
    {
        validateUsername();
        email.dispatchEvent(new Event('blur'));
        e.preventDefault(e); 
        let formData = new FormData();
        formData.append('name', $("#name").val());
        formData.append('email', $("#email").val());
        formData.append('phone', $("#phone").val());
        formData.append('password', $("#password").val());
        formData.append('usertype', $("#tyesel").val());
        formData.append('function',"adduser")


        // Append the file
        let fileInput = $('#imageUpload')[0].files[0];
        if (fileInput) {
            formData.append('image', fileInput);
        }
        $.ajax({
            type: "POST",
            url: "emp.php",
            data: formData,
            dataType: "json",
            processData: false, 
            contentType: false, 
            success: function(response) { 
                window.alert(response.response); 
                console.log(response);
                location.reload();
            },
            error: function(xhr, textStatus, errorThrown){
                alert("An error occurred: " + xhr.status + " " + xhr.statusText);
                console.error("Error:", xhr, textStatus, errorThrown);
            }
        });
        return false;
    }

});
});