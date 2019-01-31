var myLineChart;

$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": DTLang
        
    });
    
    // Set new default font family and font color to mimic Bootstrap's default styling
    Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.global.defaultFontColor = '#292b2c';

    // Area Chart Example
    var ctx = document.getElementById("myAreaChart");
    myLineChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ["Novembre", "Décembre", "Janvier", "Février", "Mars", "Avril", "Mai"],
        datasets: [{
            label: "Ordures ménagères",
            backgroundColor: "rgba(23,32,42,0.1)",
            borderColor: "rgba(23,32,42,1)",
            //data: [49, 47, 35, 31, 31, 30, 25]
        },{
            label: "Tri sélectif",
            borderColor: "rgba(219,181,0,1)",
            backgroundColor: "rgba(219,181,0,0.05)",
            //data: [31, 24, 25, 19, 21, 21, 20]
        },{
            label: "Verre",
            borderColor: "rgba(54,153,199,1)",
            backgroundColor: "rgba(54,153,199,0.05)",
            //data: [26, 23, 26, 15, 21, 18, 20]
        },{
            label: "Compost",
            borderColor: "rgba(39, 174, 96,1)",
            backgroundColor: "rgba(46, 204, 113, 0.05)",
            //data: [26, 28, 32, 25, 32, 32, 32]
        } ],
    },
    options: {
        scales: {
        xAxes: [{
            time: {
            unit: 'date'
            },
            gridLines: {
            display: false
            },
            ticks: {
            maxTicksLimit: 7
            }
        }],
        yAxes: [{
            ticks: {
            min: 0,
            max: 60,
            maxTicksLimit: 5
            },
            gridLines: {
            color: "rgba(0, 0, 0, .125)",
            }
        }],
        },
        legend: {
            display: true
        }
    }
    });
    
    //fetch data
    $.ajax({
        url: 'chart_data_api.php',
        dataType: 'json',
        type: 'post',
        contentType: 'text/json',
        data: {
           channel: ["*ALL*"],//TODO
           from: [$(".myAreaChart").attr("data-idFamille")]
        },
        success: function( data, textStatus, jQxhr ){
            
            $("").text(data["val"]);
            var i = 0;
            for(var e in data.data){
                myLineChart.data.datasets[i].data = Object.values(data.data[e][0].data); //get the "e" channel for the first subject (and we requested only one) //IMPORTANT:We only want data as color is fixed on this page
                i++;
            }
            myLineChart.options.scales.yAxes[0].ticks = $.extend(myLineChart.options.scales.yAxes[0].ticks,data.yScaleTicks);
            myLineChart.update();
        },
        error: function( jqXhr, textStatus, errorThrown ){
            console.log( errorThrown );
        }
    });
    
});
