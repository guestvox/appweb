'use strict';

$(document).ready(function()
{
    $(document).on('change', '[important] [name]', function()
    {
        if ($(this).val() != '')
        {
            $(this).parents('label').addClass('success');
            $(this).parents('label.error').removeClass('error');
            $(this).parents('label').find('p.error').remove();
        }
        else
            $(this).parents('label').removeClass('success');
    });

    $('[name="room"]').on('change', function()
    {
        $.ajax({
            type: 'POST',
            data: 'room=' + $(this).val() + '&action=get_room',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {

                }
            }
        });
    });

    $('[name="table"]').on('change', function()
    {
        $.ajax({
            type: 'POST',
            data: 'table=' + $(this).val() + '&action=get_table',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {

                }
            }
        });
    });

    $('[name="client"]').on('change', function()
    {
        $.ajax({
            type: 'POST',
            data: 'client=' + $(this).val() + '&action=get_client',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {

                }
            }
        });
    });

    $('[name="opportunity_area"]').on('change', function()
    {
        $.ajax({
            type: 'POST',
            data: 'opportunity_area=' + $(this).val() + '&type=' + $(this).data('type') + '&action=get_opt_opportunity_types',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    $('[name="opportunity_type"]').html(response.data);
                    $('[name="opportunity_type"]').attr('disabled', false);
                }
            }
        });
    });

    $('[data-modal="new_request"]').modal().onCancel(function()
    {
        $('[data-modal="new_request"]').find('form')[0].reset();
        $('[data-modal="new_request"]').find('label.error').removeClass('error');
        $('[data-modal="new_request"]').find('p.error').remove();
    });

    $('[data-modal="new_request"]').modal().onSuccess(function()
    {
        $('[data-modal="new_request"]').find('form').submit();
    });

    $('form[name="new_request"]').on('submit', function(e)
    {
        e.preventDefault();

        var form = $(this);

        $.ajax({
            type: 'POST',
            data: form.serialize() + '&action=new_request',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    $('[data-modal="success"]').addClass('view');
                    $('[data-modal="success"]').find('main > p').html(response.message);
                    setTimeout(function() { location.reload(); }, 8000);
                }
                else if (response.status == 'error')
                {
                    if (response.labels)
                    {
                        form.find('label.error').removeClass('error');
                        form.find('p.error').remove();

                        $.each(response.labels, function(i, label)
                        {
                            if (label[1].length > 0)
                                form.find('[name="' + label[0] + '"]').parents('label').addClass('error').append('<p class="error">' + label[1] + '</p>');
                            else
                                form.find('[name="' + label[0] + '"]').parents('label').addClass('error');
                        });

                        form.find('label.error [name]')[0].focus();
                    }
                    else if (response.message)
                    {
                        $('[data-modal="error"]').addClass('view');
                        $('[data-modal="error"]').find('main > p').html(response.message);
                    }
                }
            }
        });
    });

    $('[data-modal="new_incident"]').modal().onCancel(function()
    {
        $('[data-modal="new_incident"]').find('form')[0].reset();
        $('[data-modal="new_incident"]').find('label.error').removeClass('error');
        $('[data-modal="new_incident"]').find('p.error').remove();
    });

    $('[data-modal="new_incident"]').modal().onSuccess(function()
    {
        $('[data-modal="new_incident"]').find('form').submit();
    });

    $('form[name="new_incident"]').on('submit', function(e)
    {
        e.preventDefault();

        var form = $(this);

        $.ajax({
            type: 'POST',
            data: form.serialize() + '&action=new_incident',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    $('[data-modal="success"]').addClass('view');
                    $('[data-modal="success"]').find('main > p').html(response.message);
                    setTimeout(function() { location.reload(); }, 8000);
                }
                else if (response.status == 'error')
                {
                    if (response.labels)
                    {
                        form.find('label.error').removeClass('error');
                        form.find('p.error').remove();

                        $.each(response.labels, function(i, label)
                        {
                            if (label[1].length > 0)
                                form.find('[name="' + label[0] + '"]').parents('label').addClass('error').append('<p class="error">' + label[1] + '</p>');
                            else
                                form.find('[name="' + label[0] + '"]').parents('label').addClass('error');
                        });

                        form.find('label.error [name]')[0].focus();
                    }
                    else if (response.message)
                    {
                        $('[data-modal="error"]').addClass('view');
                        $('[data-modal="error"]').find('main > p').html(response.message);
                    }
                }
            }
        });
    });

    $('[data-action="open_subquestion"]').on('change', function()
    {
        var name = $(this).attr('name');

        if ($(this).val() == '1' || $(this).val() == '2' || $(this).val() == '3' || $(this).val() == 'yes')
            $('#' + name).removeClass('hidden');
        else
        {
            $('#' + name).addClass('hidden');

            $('#' + name).find(':input').each(function() {
                if (this.type == 'text')
                    $(this).val('');
                else if (this.type == 'radio')
                    this.checked = false;

            });
        }
    });

    $('[data-action="open_subquestion_sub"]').on('change', function()
    {
        var name = $(this).attr('name');

        if ($(this).val() == '1' || $(this).val() == '2' || $(this).val() == '3' || $(this).val() == 'yes')
            $('#' + name).removeClass('hidden');
        else
        {
            $('#' + name).addClass('hidden');

            $('#' + name).find(':input').each(function() {
                if (this.type == 'text')
                    $(this).val('');
                else if (this.type == 'radio')
                    this.checked = false;

            });
        }
    });

    $('[data-modal="new_survey_answer"]').modal().onCancel(function()
    {
        $('[data-modal="new_survey_answer"]').find('form')[0].reset();
        $('[data-modal="new_survey_answer"]').find('label.error').removeClass('error');
        $('[data-modal="new_survey_answer"]').find('p.error').remove();
    });

    $('[data-modal="new_survey_answer"]').modal().onSuccess(function()
    {
        $('[data-modal="new_survey_answer"]').find('form').submit();
    });

    $('form[name="new_survey_answer"]').on('submit', function(e)
    {
        e.preventDefault();

        var form = $(this);

        $.ajax({
            type: 'POST',
            data: form.serialize() + '&action=new_survey_answer',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    if (response.data.length != 0)
                    {
                        $('[data-modal="success"]').addClass('view');
                        $('[data-modal="success"]').find('main > p').html(response.message);

                        if (response.data.rate >= 4 && response.data.rate <= 5)
                        {
                            setTimeout(function() {
                                $('[data-modal="success"]').removeClass('view');
                                $('[data-modal="new_survey_answer"]').removeClass('view');
                                $('[data-modal="new_survey_answer"]').find('form')[0].reset();
                                $('[data-modal="new_survey_answer"]').find('label.error').removeClass('error');
                                $('[data-modal="new_survey_answer"]').find('p.error').remove();
                                $('[data-modal="tripadvisor"]').addClass('view');
                            }, 5000);
                        }
                        else
                            setTimeout(function() { location.reload(); }, 8000);
                    }
                    else
                    {
                        $('[data-modal="success"]').addClass('view');
                        $('[data-modal="success"]').find('main > p').html(response.message);
                        setTimeout(function() { location.reload(); }, 8000);
                    }
                }
                else if (response.status == 'error')
                {
                    if (response.labels)
                    {
                        form.find('label.error').removeClass('error');
                        form.find('p.error').remove();

                        $.each(response.labels, function(i, label)
                        {
                            if (label[1].length > 0)
                                form.find('[name="' + label[0] + '"]').parents('label').addClass('error').append('<p class="error">' + label[1] + '</p>');
                            else
                                form.find('[name="' + label[0] + '"]').parents('label').addClass('error');
                        });

                        form.find('label.error [name]')[0].focus();
                    }
                    else if (response.message)
                    {
                        $('[data-modal="error"]').addClass('view');
                        $('[data-modal="error"]').find('main > p').html(response.message);
                    }
                }
            }
        });
    });
});
