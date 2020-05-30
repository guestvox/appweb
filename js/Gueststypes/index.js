'use strict';

$(document).ready(function()
{
    var id = null;
    var edit = false;

    $('[data-modal="new_guest_type"]').modal().onCancel(function()
    {
        id = null;
        edit = false;

        $('[data-modal="new_guest_type"]').find('form')[0].reset();
        $('[data-modal="new_guest_type"]').find('label.error').removeClass('error');
        $('[data-modal="new_guest_type"]').find('p.error').remove();
    });

    $('form[name="new_guest_type"]').on('submit', function(e)
    {
        e.preventDefault();

        var form = $(this);

        if (edit == false)
            var data = '&action=new_guest_type';
        else if (edit == true)
            var data = '&id=' + id + '&action=edit_guest_type';

        $.ajax({
            type: 'POST',
            data: form.serialize() + data,
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                    show_modal_success(response.message, 1500);
                else if (response.status == 'error')
                    show_form_errors(form, response);
            }
        });
    });

    $('[data-action="edit_guest_type"]').on('click', function()
    {
        id = $(this).data('id');
        edit = true;

        $.ajax({
            type: 'POST',
            data: 'id=' + id + '&action=get_guest_type',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    $('[data-modal="new_guest_type"]').addClass('view');

                    $('[data-modal="new_guest_type"]').find('[name="name"]').val(response.data.name);

                    required_focus($('[data-modal="new_guest_type"]').find('form'), true);
                }
                else if (response.status == 'error')
                    show_modal_error(response.message);
            }
        });
    });

    $('[data-action="deactivate_guest_type"]').on('click', function()
    {
        id = $(this).data('id');

        $('[data-modal="deactivate_guest_type"]').addClass('view');
    });

    $('[data-modal="deactivate_guest_type"]').modal().onSuccess(function()
    {
        $.ajax({
            type: 'POST',
            data: 'id=' + id + '&action=deactivate_guest_type',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                    show_modal_success(response.message, 1500);
                else if (response.status == 'error')
                    show_modal_error(response.message);
            }
        });
    });

    $('[data-action="activate_guest_type"]').on('click', function()
    {
        id = $(this).data('id');

        $('[data-modal="activate_guest_type"]').addClass('view');
    });

    $('[data-modal="activate_guest_type"]').modal().onSuccess(function()
    {
        $.ajax({
            type: 'POST',
            data: 'id=' + id + '&action=activate_guest_type',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                    show_modal_success(response.message, 1500);
                else if (response.status == 'error')
                    show_modal_error(response.message);
            }
        });
    });

    $('[data-action="delete_guest_type"]').on('click', function()
    {
        id = $(this).data('id');

        $('[data-modal="delete_guest_type"]').addClass('view');
    });

    $('[data-modal="delete_guest_type"]').modal().onSuccess(function()
    {
        $.ajax({
            type: 'POST',
            data: 'id=' + id + '&action=delete_guest_type',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                    show_modal_success(response.message, 1500);
                else if (response.status == 'error')
                    show_modal_error(response.message);
            }
        });
    });
});