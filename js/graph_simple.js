var lineChart;

function updateChart(chart, channel, subjects){
    
           
           $.ajax({
                url: 'chart_data_api.php',
                //dataType: 'json',
                type: 'POST',
                indexValue: [chart, channel, subjects],
                //contentType: 'text/json',
                data: {
                    channel: channel,
                    from: subjects
                },
                success: function( data, textStatus, jQxhr ){
                    console.log(data);
                    console.log(nameMapping[this.indexValue[1]]);
                    console.log(this.indexValue[2]);
                    var rawData = data;
                    
                    this.indexValue[0].data.datasets = []; //TODO: check how to properly do this. I don't even know if it works'

                    for(var i = 0; i < this.indexValue[2].length; i++)
                    {

                        this.indexValue[0].data.datasets[i] = rawData.data[this.indexValue[1]][i];
                        this.indexValue[0].data.datasets[i].data = Object.values(this.indexValue[0].data.datasets[i].data); //TODO: not sure if values are ordered as intended. Will do for a demo. But should be investigated
                        console.log(rawData.data[this.indexValue[1]][i]);
                    }
                    this.indexValue[0].options.scales.yAxes[0].ticks = $.extend(this.indexValue[0].options.scales.yAxes[0].ticks,rawData.yScaleTicks);
                    this.indexValue[0].update();
                },
                error: function( jqXhr, textStatus, errorThrown ){
                    console.log(textStatus);
                
                    return;
                }
            });

}


$(document).ready(function() {
    
Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#292b2c';

// Area Chart Example
var ctx = document.getElementById("chartArea");
lineChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: ["Novembre", "Décembre", "Janvier", "Février", "Mars", "Avril", "Mai"],
    datasets: [{
      label: "Nous",
      lineTension: 0.3,
      backgroundColor: "rgba(138,138,138,0.2)",
      borderColor: "rgba(0,0,0,1)",
      pointRadius: 5,
      pointBackgroundColor: "rgba(2,117,216,1)",
      pointBorderColor: "rgba(255,255,255,0.8)",
      pointHoverRadius: 5,
      pointHoverBackgroundColor: "rgba(2,117,216,1)",
      pointHitRadius: 50,
      pointBorderWidth: 2,
    }, {
        label: "Moyenne du défi",
        fillColor: "rgba(220,220,220,0.5)",
        strokeColor: "rgba(220,220,220,0.8)",
        highlightFill: "rgba(220,220,220,0.75)",
        highlightStroke: "rgba(220,220,220,1)",
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

updateChart(lineChart, nameMapping[$("#dataTypeSelector").val()], [$("#dataTypeSelector").attr("data-idFamille"), "*AVERAGE*"]);
});

$("#dataTypeSelector").on("change", function() {

    console.log("triggered");
    //console.log(lineChart.options.scales.yAxes[0].ticks);
    
    updateChart(lineChart, [nameMapping[$(this).val()]], [$(this).attr("data-idFamille"), "*AVERAGE*"]);
    
    /*
    $(".payeLink").attr("href", "#"); //supprime le lien pour ceux qui n'ont pas javascript
    
    $(".payeLink").on("click", function() {
        
           
           
           $.ajax({
                url: 'admin_membres.html',
                dataType: 'json',
                type: 'post',
                contentType: 'text/json',
                data: {
                    id: $(this).attr("data-id"),
                    token: $(this).attr("data-token")
                },
                success: function( data, textStatus, jQxhr ){
                    //use data
                    $(this).text(data["val"]);
                },
                error: function( jqXhr, textStatus, errorThrown ){
                    console.log( errorThrown );
                }
            });
    });

var data = {
    labels: ["Novembre", "Décembre", "Janvier", "Février", "Mars", "Avril", "Mai", "Juin"],
    datasets: [{
      label: "Nous",
      lineTension: 0.3,
      backgroundColor: "rgba(138,138,138,0.2)",
      borderColor: "rgba(0,0,0,1)",
      pointRadius: 5,
      pointBackgroundColor: "rgba(2,117,216,1)",
      pointBorderColor: "rgba(255,255,255,0.8)",
      pointHoverRadius: 5,
      pointHoverBackgroundColor: "rgba(2,117,216,1)",
      pointHitRadius: 50,
      pointBorderWidth: 2,
      data: [30, 40, 26, 42, 34, 33, 28, 31],
    }, {
        label: "Moyenne du défi",
        fillColor: "rgba(220,220,220,0.5)",
        strokeColor: "rgba(220,220,220,0.8)",
        highlightFill: "rgba(220,220,220,0.75)",
        highlightStroke: "rgba(220,220,220,1)",
        data: [35, 60, 56, 42, 37, 33, 24, 32, 31, 49, 32, 37, 35]
    }],
  };
*/
    
  
});
