import moment from 'moment'

// generator for very simple action creators (see redux doc)
export function formatDate(date) {
  return moment(date).format('YYYY-MM-DDThh:mm:ss')
}
