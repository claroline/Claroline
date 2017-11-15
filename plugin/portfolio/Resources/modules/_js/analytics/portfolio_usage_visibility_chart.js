import $ from 'jquery'

var pieChartData = window.pieChartData


var bg_color = 'transparent'
if (navigator.userAgent.match(/msie/i) && navigator.userAgent.match(/8/)) {
  bg_color = '#fff'
}

$(document).ready(function (){
  if (pieChartData.length > 0) {
    $.jqplot(
      'resources-pie-chart',
      [pieChartData],
      {
        seriesColors: ['#d9534f', '#5cb85c', '#ed9c28', '#428bca'],
        title: {show: false},
        grid: {
          drawBorder: false,
          shadow: false,
          background: bg_color,
          useNegativeColors: false
        },
        highlighter: {
          show: false
        },
        cursor: {
          show: false,
          zoom: false,
          showTooltip: false
        },
        seriesDefaults: {
          showMarker: true,
          renderer: $.jqplot.PieRenderer,
          rendererOptions: {
            showDataLabels: true,
            dataLabelThreshold: 2,
            dataLabels: 'percent',
            sliceMargin: 0.3,
            dataLabelFormatString: '%.1f%%',
            highlightMouseOver: false
          },
          shadow: false
        },
        legend:{
          location: 's',
          border: 'none',
          renderer: $.jqplot.CavasTextRenderer,
          show: true,
          showMarker: true,
          rendererOptions: {
            numberRows: 4
          },
          backgroundColor: bg_color,
          placement: 'outsideGrid'
        }
      }
        )
  }
})
