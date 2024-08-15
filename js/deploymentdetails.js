$(document).ready(function(){
    
    // $('#deployment_date').click(function(){
    //     if($("#num_days").val()){
    //         const today = new Date() //.toISOString().split('T')[0];
    //         console.log(today);
    //         const numDays = parseInt($("#num_days").val(), 10);
    //         console.log(numDays)
    //         today.setDate(today.getDate() + numDays+1)
            

    //         $.ajax({
    //             url: 'main.php?function=retdate',
    //             type: 'GET',
    //             dataType: 'json',
    //             success: function(data) {
    //                 const ddate = new Date(data.mindate);
    //                 const ddate2 = new Date(data.mindate);
    //                 ddate2.setDate(ddate2.getDate() + data.required_days);
    //                 console.log(today);
    //                 console.log(ddate);
    //                 console.log(ddate2);
    //                 if(ddate > today){
    //                     $("#deployment_date").attr('min', today);
    //                     $("#deployment_date").attr('max', today);
    //                     console.log(today);
    //                 }else{
    //                     console.log(data.mindate);
    //                 }
    //             },
    //             error: function(xhr, status, error) {
    //                 console.error('Error fetching data:', error);
    //             }
    //         });
    //     }else{
    //         alert("please select number of days")
    //     }
    // });

    $.ajax({
        url: 'main.php?function=fetchportal&from=deploymentdetails',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var $select = $('#portal_url');
            $.each(data, function(index, item) {
                $select.append($('<option>', {
                    value: item.pid,
                    text: item.purl
                }));
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });

    $('#addportal').on('submit', function(e) {
    {
        e.preventDefault(e); 
        let formData = new FormData(this);
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

    });
});