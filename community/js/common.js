/**
 * Common functions for animations/transitions etc
 */

$(document).ready(function() {
    $("div.topBarItem").click(function(event) {
        var category = "";
        $("div.topBarItemSelected").attr("class", "topBarItem");
        switch($(event.currentTarget).attr("id"))
        {
            case "topBarItemJuegos":
                category = "Juegos";
                break;
            case "topBarItemForos":
                category = "Foros";
                break;
            case "topBarItemComunidad":
                category = "Comunidad";
                break;
            case "topBarItemServidores":
                category = "Servidores";
                break;
            default:
                break;
        }
        if ($("div.subMenuWrapper").is(":hidden"))
        {
            $("div#topBarSubMenu" + category).show();
            $("div.subMenuWrapper").slideDown();
            $("div#topBarItem" + category).attr("class", "topBarItemSelected");
        }
        else
        {
            if ($("div#topBarSubMenu" + category).is(":hidden"))
            {
                $("div.topBarSubMenu").fadeOut(200);
                setTimeout(function() {
                    $("div#topBarSubMenu" + category).fadeIn(200);
                }, 205);
                $("div#topBarItem" + category).attr("class", "topBarItemSelected");
            }
            else
            {
                $("div.subMenuWrapper").slideUp(function() {
                    $("div#topBarSubMenu" + category).hide();
                });
            }
        }
    });
    // Auto-update all timestamps in the page.
    UpdateTimestamps();
});

function UpdateTimestamps() 
{
    $(".timestamp").each(function() {
        var timestamp = $(this).attr("data-timestamp");
        
        if (!timestamp)
            return;
        
        var timePassed = Math.round(new Date().getTime() / 1000) - timestamp;
        // just now or XX minutes ago
        if (timePassed < 3600)
        {
            timePassed = Math.round(timePassed / 60);
            if (timePassed < 1)
                $(this).text("justo ahora");
            else if (timePassed == 1)
                $(this).text("hace 1 minuto");
            else
                $(this).text("hace " + timePassed + " minutos ");
        }
        // More than XX hours ago
        else if (timePassed < 86400)
        {
            timePassed = Math.round(timePassed / 3600);
            if (timePassed == 1)
                $(this).html("hace m&aacute;s de una hora");
            else
                $(this).html("hace m&aacute;s de " + timePassed + " horas");
        }
        // XX days ago
        else if (timePassed < 2592000)
        {
            timePassed = Math.round(timePassed / 86400);
            if (timePassed == 1)
                $(this).html("hace 1 d&iacute;a");
            else
                $(this).html("hace " + timePassed + " d&iacute;as");
        }
        // XX months ago
        else
        {
            timePassed = Math.round(timePassed / 2592000);
            if (timePassed == 1)
                $(this).text("hace un mes");
            else
                $(this).html("hace m&aacute;s de " + timePassed + " meses");
        }
    });
    setTimeout(function() {
        UpdateTimestamps();
    }, 30000);
}