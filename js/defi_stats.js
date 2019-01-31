var lineChart;

// Call the dataTables jQuery plugin
$(document).ready(function() {
    $('#dataTable').DataTable({
                "language": DTLang

    });


    // Set new default font family and font color to mimic Bootstrap's default styling
    Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.global.defaultFontColor = '#292b2c';

    var ctx = document.getElementById("myAreaChart");
    lineChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ["Novembre", "Décembre", "Janvier", "Février", "Mars", "Avril", "Mai"],
        datasets: [{
        label: "Vous (Kgs/Pers)",
        lineTension: 0.3,
        backgroundColor: "rgba(2,117,216,0.1)",
        borderColor: "rgba(2,117,216,1)",
        pointRadius: 5,
        pointBackgroundColor: "rgba(2,117,216,1)",
        pointBorderColor: "rgba(255,255,255,0.8)",
        pointHoverRadius: 5,
        pointHoverBackgroundColor: "rgba(2,117,216,1)",
        pointHitRadius: 50,
        pointBorderWidth: 2,
        //data: [30, 40, 26, 42, 34, 33, 28, 31],
        }, {
        label: "Famille Bricole (Kgs/Pers)",
        lineTension: 0.3,
        backgroundColor: "rgba(255,128,0,0.2)",
        borderColor: "rgba(255,128,4,1)",
        pointRadius: 5,
        pointBackgroundColor: "rgba(255,128,0,1)",
        pointBorderColor: "rgba(255,255,255,0.8)",
        pointHoverRadius: 5,
        pointHoverBackgroundColor: "rgba(255,128,0,1)",
        pointHitRadius: 50,
        pointBorderWidth: 2,
        //data: [31, 40, 26, 54, 34, 13, 18, 33]
        },{
        label: "Moyenne du défi (Kgs/Pers)",
        lineTension: 0.3,
        backgroundColor: "rgba(117,117,117,0.2)",
        borderColor: "rgba(200,200,200,1)",
        pointRadius: 5,
        pointBackgroundColor: "rgba(117,117,117,1)",
        pointBorderColor: "rgba(255,255,255,0.8)",
        pointHoverRadius: 5,
        pointHoverBackgroundColor: "rgba(2,117,216,1)",
        pointHitRadius: 50,
        pointBorderWidth: 2,
        //data: [35, 60, 56, 42, 37, 33, 24, 32]
        }],
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

    updateChart(lineChart, $("#dataTypeSelector").val(), [$("#dataTypeSelector").attr("data-idFamille"), "*AVERAGE*"]);
});


function updateChart(chart, channel, subjects){
    
    console.log("updateChart()");
    $.ajax({
        url: 'chart_data_api.php', //should use session vars to check we are a logged in user. Should return data in the same order as requested
        dataType: 'json',
        type: 'POST',
        contentType: 'text/json',
        indexValue: [chart, channel, subjects], //pass the variables to the ajax call context
        data: {
            channel: channel,
            from: subjects
        },
        success: function( data, textStatus, jQxhr ){
            console.log("requestSucess");
            var rawData = data;
            console.log(rawData);
            for(var i = 0; i < this.indexValue[2].length; i++)
            {
                console.log("Loop Index: "+i);
                //console.log(this.indexValue[1]); //NOTE: seems like because we can't have an array in an array, the indexvalue[1] get converted to simple value because there is only one value in the array. this may cause a problem and will probably need to be fixed
                this.indexValue[0].data.datasets[i].data = Object.values(rawData.data[nameMapping[this.indexValue[1]/*[0]*/]][i]); //FIXME: does not work because data[i] do not exist, we must specify the channel : data["channel"][i]
                //this.indexValue[0].data.datasets[i].label = rawData.label[i];
                
            }
            this.indexValue[0].options.scales.yAxes[0].ticks = rawData.yScaleTicks;
            this.indexValue[0].update();
        },
        error: function( jqXhr, textStatus, errorThrown ){
            console.log("requestError: " + textStatus);
            console.log( errorThrown );
            
            return;
        },
        done: function() {
            console.log("requestDone");
        }
    });

}

//$("#dataTypeSelector").on("change", function() {
$(".familleSelector").on("change", function() {

    //console.log("triggered");
    //console.log(lineChart.options.scales.yAxes[0].ticks);
    var selected = [];
    $(".familleSelector").each(function() {
        if($(this).is(":checked")) //for each checkboxes, we check if they are selected and then if yes, collect the family id.
            selected.push($(this).attr("data-idFamille"));
    });

    selected.unshift($("#dataTypeSelector").attr("data-idFamille")); //the current family id
    selected.push("*AVERAGE*"); //special marker, tell the server to compute and return the average
    
    //console.log(selected);
    updateChart(lineChart, [$("#dataTypeSelector").val()], selected); //this .val contains the current channel, because the selector triggered this event
    
});
