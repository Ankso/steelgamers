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
    // Get status of the different servers in the network
    GetWowTbcServerStatus();
    GetTs3ServerStatus();
    GetMitracraftServerStatus();
    GetArma2ServerStatus();
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

function GetWowTbcServerStatus()
{
    $.ajax({
        dataType: "jsonp",
        data: "",
        url: "http://cdn.steelgamers.es/ajax/wow_tbc_server_status.php?callback=?",
        success: function(status) {
            if (status.error)
            {
                $("div#wowServerStatusLabel").text("Desconocido");
            }
            else
            {
                if (status.isOnline)
                {
                    $("div#wowServerStatusLabel").text("Online");
                    $("div#wowServerStatusLabel").attr("class", "serverStatus online");
                    $("div#wowServerGamersOnlineLabel").text("Gamers conectados: " + status.currentOnline + "/" + status.maxOnline);
                }
                else
                {
                    $("div#wowServerStatusLabel").text("Offline");
                    $("div#wowServerStatusLabel").attr("class", "serverStatus offline");
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $("div#wowServerStatusLabel").text("Sin respuesta");
        },
        timeout: 5000
    });
}

function GetTs3ServerStatus()
{
    $.ajax({
        dataType: "jsonp",
        data: "",
        url: "http://cdn.steelgamers.es/ajax/ts3_server_status.php?callback=?",
        success: function(status) {
            if (status.error)
            {
                $("div#ts3ServerStatusLabel").text("Desconocido");
            }
            else
            {
                if (status.isOnline)
                {
                    $("div#ts3ServerStatusLabel").text("Online");
                    $("div#ts3ServerStatusLabel").attr("class", "serverStatus online");
                    $("div#ts3ServerGamersOnlineLabel").text("Gamers conectados: " + status.currentOnline + "/" + status.maxOnline);
                }
                else
                {
                    $("div#ts3ServerStatusLabel").text("Offline");
                    $("div#ts3ServerStatusLabel").attr("class", "serverStatus offline");
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $("div#ts3ServerStatusLabel").text("Sin respuesta");
        },
        timeout: 5000
    });
}

function GetMitracraftServerStatus()
{
    $.ajax({
        dataType: "jsonp",
        data: "",
        url: "http://cdn.steelgamers.es/ajax/mitracraft_server_status.php?callback=?",
        success: function(status) {
            if (status.error)
            {
                $("div#mitracraftServerStatusLabel").text("Desconocido");
            }
            else
            {
                if (status.isOnline)
                {
                    $("div#mitracraftServerStatusLabel").text("Online");
                    $("div#mitracraftServerStatusLabel").attr("class", "serverStatus online");
                    $("div#mitracraftServerGamersOnlineLabel").text("Gamers conectados: " + status.currentOnline + "/" + status.maxOnline);
                }
                else
                {
                    $("div#mitracraftServerStatusLabel").text("Offline");
                    $("div#mitracraftServerStatusLabel").attr("class", "serverStatus offline");
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $("div#mitracraftServerStatusLabel").text("Sin respuesta");
        },
        timeout: 5000
    });
}

function GetArma2ServerStatus()
{
    $.ajax({
        dataType: "jsonp",
        data: "",
        url: "http://cdn.steelgamers.es/ajax/arma2_server_status.php?callback=?",
        success: function(status) {
            if (status.wasteland.error)
            {
                $("div#arma2ServerStatusLabelWasteland").text("Wasteland");
            }
            else
            {
                if (status.wasteland.isOnline)
                {
                    $("div#arma2ServerStatusLabelWasteland").text("Wasteland");
                    $("div#arma2ServerStatusLabelWasteland").attr("class", "serverStatus online");
                    $("div#arma2ServerGamersOnlineLabelWasteland").text("Gamers conectados: " + status.wasteland.currentOnline + "/" + status.wasteland.maxOnline);
                    $("div#arma2ServerMapLabelWasteland").text("Mapa: " + status.wasteland.map.substring(0, 1).toUpperCase() + status.wasteland.map.substring(1));
                }
                else
                {
                    $("div#arma2ServerStatusLabelWasteland").text("Wasteland");
                    $("div#arma2ServerStatusLabelWasteland").attr("class", "serverStatus offline");
                }
                
            }
            if (status.warfare.error)
            {
                $("div#arma2ServerStatusLabelWarfare").text("Warfare");
            }
            else
            {
                if (status.warfare.isOnline)
                {
                    $("div#arma2ServerStatusLabelWarfare").text("Warfare BE");
                    $("div#arma2ServerStatusLabelWarfare").attr("class", "serverStatus online");
                    $("div#arma2ServerGamersOnlineLabelWarfare").text("Gamers conectados: " + status.warfare.currentOnline + "/" + status.warfare.maxOnline);
                    $("div#arma2ServerMapLabelWarfare").text("Mapa: " + status.warfare.map.substring(0, 1).toUpperCase() + status.warfare.map.substring(1));
                }
                else
                {
                    $("div#arma2ServerStatusLabelWarfare").text("Warfare BE");
                    $("div#arma2ServerStatusLabelWarfare").attr("class", "serverStatus offline");
                }
                
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $("div#arma2ServerStatusLabelWasteland").text("Wasteland");
            $("div#arma2ServerStatusLabelWarfare").text("Warfare BE");
        },
        timeout: 5000
    });
}
