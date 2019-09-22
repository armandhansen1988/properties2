function confirmDelete()
{
    var uuid = $('#delete_uuid').val();
    $.get('/properties/delete/'+uuid, {}, function (data) {
        if (data.error == 1) {
            window.location = '/properties/view/0/0';
        } else {
            alert("There was a problem removing the property. Please try again");
        }
    }, 'json');
}