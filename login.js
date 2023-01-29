$(window).on("load", function() 
{
    $("input[type=checkbox]").on("change", function()
    {
        var checked = $(this).prop("checked");
        $("input[type=checkbox]").prop("checked", false);
        if (checked) {
            $(this).prop("checked", true);
        }
    });
});