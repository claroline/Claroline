import moment from 'moment'

export function formatDate(date) {
  return moment(date).format('YYYY-MM-DDThh:mm:ss')
}
