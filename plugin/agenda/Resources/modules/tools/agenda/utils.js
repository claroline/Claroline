import moment from 'moment'

function calendarUrl(basePath, view, date) {
  return `${basePath}/${view}/${moment(date).format('YYYY/MM/DD')}`
}

export {
  calendarUrl
}
