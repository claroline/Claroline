import moment from 'moment'

function route(basePath, view, date) {
  return `${basePath}/${view}/${moment(date).format('YYYY/MM/DD')}`
}

export {
  route
}
