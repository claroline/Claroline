import {constants} from '#/main/core/layout/calendar/constants'

function calculateTime(time, max) {
  if (time >= 0 && time <= max) {
    return time
  } else if (time > max) {
    return max
  } else if (time < 0) {
    return 0
  }
}

function getNextView(currentView) {
  switch (currentView) {
    case constants.CALENDAR_VIEW_YEARS:
      return constants.CALENDAR_VIEW_DAYS
    case constants.CALENDAR_VIEW_MONTHS:
      return constants.CALENDAR_VIEW_YEARS
    case constants.CALENDAR_VIEW_DAYS:
      return constants.CALENDAR_VIEW_MONTHS
  }
}

function monthNum(quarter, month) {
  return (quarter*4) + month
}

function yearNum(row, year) {
  return (row*4) + year
}

export {
  calculateTime,
  getNextView,
  monthNum,
  yearNum
}
