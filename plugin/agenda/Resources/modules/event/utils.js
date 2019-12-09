import moment from 'moment'

import {trans} from '#/main/app/intl/translation'

function eventDuration(event) {
  if (event.allDay) {
    return trans('all_day', {}, 'agenda')
  }

  return moment(event.start).format('LT')
}

function sortEvents(events) {
  return events.sort((a, b) => {
    if (a.allDay && !b.allDay) {
      return 1
    } else if (!a.allDay && b.allDay) {
      return -1
    }

    if (a.start < b.start) {
      return 1
    } else if (a.start > b.start) {
      return -1
    }

    return 0
  })
}

export {
  sortEvents,
  eventDuration
}