var hearings = 1;

function validateElement(el) {
    var valid;
    if (($(el).is('select') && ($(el).val() == '-')) || ($(el).is('input') && ($(el).val().trim() == ''))) {
        valid = false;
        $('#' + $(el).attr('data-error-msg')).html($(el).attr('data-error'));
        $(el).parent().addClass('has-error');
    } else {
        valid = true;
        $(el).parent().removeClass('has-error');
        $('#' + $(el).attr('data-error-msg')).html('');
    }
    return valid;    
}

function validate() {
    var valid = true;
    $('select').each(function() {
        if (!validateElement(this)) valid = false;
    });
    $('[data-required]').each(function() {
        if (!validateElement(this)) valid = false;
    });
    return valid;    
}

$(document).ready(function() {

    $('#orderform').submit(function(e) {
        if (validate()) {
            // form is valid - submit
            $('#form-submit').attr('disabled', true);
        } else {
            // form is not valid
            e.preventDefault();
            //alert('Form has errors - please correct');
            $('#form-errors').show();
        }
    });
    
    $('select').each(function() {
        $(this).change(function(el) {validateElement(el.target);});
    });

    $('[data-required]').each(function(el) {
        $(this).change(function(el) {validateElement(el.target);}); 
    }); 
    
    $('#addhearing').click(function(ev) {
        ev.preventDefault();
        hearings++;
        $('#hearings').val(hearings);
        $('#delhearing').removeAttr('disabled').removeClass('disabled');
        var rowHtml = '<tr id="line' + hearings + '">' + $('#line1').clone().html().replace(/\[1\]/g, '[' + hearings + ']') + '</tr>';
        $('tbody#hearingsbody').append(rowHtml);
    });

    $('#delhearing').click(function(ev) {
        ev.preventDefault();
        if (hearings > 1) { 
            $('#line' + hearings).remove();
            hearings--;
            $('#hearings').val(hearings);
            if (hearings == 1) $('#delhearing').attr('disabled', true).addClass('disabled');
        }
    });
    
    $('#delhearing').attr('disabled', true).addClass('disabled');
    $('#form-errors').hide();
    
});