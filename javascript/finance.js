$( document ).ready(function() {
    $('.shipTo').click(function(){
        checkShipToAddress(this);
    });
    $('.secOwner').click(function(){
        showSecondOwner(this);
    });
    $('#submitFinancing').click(function(){
       submitForm();
    });
});

function submitForm()
{
    alert('submitting');
    //formValidation();
    $('#financeForm').submit();
}

function showSecondOwner(obj)
{
    var type = $(obj).attr('id');
    if(type == "yes")
    {
        $('.secondOwner').show();
    }else if(type == "no"){
        $('.secondOwner').hide();
    }
}

function checkShipToAddress(obj)
{
    var type = $(obj).attr('id');
    if(type == "other")
    {
        $('.shipToOther').show();
    }else if(type == "same"){
        $('.shipToOther').hide();
    }
}

function hideShowTerms(action)
{
    if(action == 1)
    {
        $('#termsWrapper').show();
    }else{
        $('#termsWrapper').hide();
    }
}