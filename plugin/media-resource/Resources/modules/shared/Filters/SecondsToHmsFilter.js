function SecondsToHmsFilter (){
  return function(d) {
    d = Number(d)
    let result = ''
    if (d > 0) {

      var hours = Math.floor(d / 3600)
      var minutes = Math.floor(d % 3600 / 60)
      var seconds = Math.floor(d % 3600 % 60)

        // ms
      var str = d.toString()
      var substr = str.split('.')
      var ms = substr[1].substring(0, 2)

      if (hours < 10) {
        hours = '0' + hours
      }
      if (minutes < 10) {
        minutes = '0' + minutes
      }
      if (seconds < 10) {
        seconds = '0' + seconds
      }
       // var time = hours + ':' + minutes + ':' + seconds + ':' + ms;
      result = minutes + ':' + seconds + ':' + ms
    }
    else {

      result = '00:00:00'
    }
    return result
  }
}

export default SecondsToHmsFilter
