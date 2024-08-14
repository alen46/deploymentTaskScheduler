$(document).ready(function(){

    $('#addportal').on('submit', function(e) {
    {
        e.preventDefault(e); 
        let formData = new FormData();
        formData.append('portal_name', $("#portal_name").val());
        formData.append('portal_url', $("#portal_url").val());
        formData.append('portal_version', $("#portal_version").val());
        formData.append('portal_features', $("#portal_features").val());
        formData.append('function',"addportal");
        console.log(formData);
        $.ajax({
            type: "POST",
            url: "main.php",
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