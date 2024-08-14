$(document).ready(function(){

    $.ajax({
        url: 'main.php?function=fetchportal',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var $select = $('#portal_url');
            $.each(data, function(index, item) {
                $select.append($('<option>', {
                    value: item.pid,
                    text: item.portalname
                }));
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
});


/*
    $('#addportal').on('submit', function(e) {
    {
        e.preventDefault(e); 
        let formData = new FormData(this);
        formData.append('portal_url', $("#portal_url").val());
        formData.append('portal_version', $("#portal_version").val());
        formData.append('deployment_date', $("#deployment_date").val());
        formData.append('num_days', $("#num_days").val());
        formData.append('deployment_note', $("#deployment_note").val());
        formData.append('function',"adddeployment");
        let fileInput = $('#deployment_plan')[0].files[0];
            if (fileInput) {
                formData.append('deployment_plan', fileInput);
            } 
        formData.append('function',"adddeployment");
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

}); */
});