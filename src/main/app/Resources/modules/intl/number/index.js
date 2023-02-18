import {trans} from '#/main/app/intl/translation'

// todo add parse display value

// todo configurable precision
// todo configurable M separator
// todo configurable decimal separator

function precision(num, decimals) {
  return Math.round(num * Math.pow(10, decimals)) / Math.pow(10, decimals)
}

function humanize(num = 0, base) {
  const roundSteps = {
    1: trans('unit_kilo'),
    2: trans('unit_mega'),
    3: trans('unit_giga'),
    4: trans('unit_tera')
  }

  let unit = ''
  let rounder = 1

  const steps = Object.keys(roundSteps)
  for (let i = 1; i < steps.length; i++) {
    const limit = Math.pow(base, i)
    if (num >= limit) {
      rounder = limit
      unit = roundSteps[i]
    }
  }

  return precision(num / rounder, 1) + unit
}

function number(num, short = false) {
  if (short) {
    return humanize(num, 1000)
  }

  return precision(num, 1)
}

function fileSize(num, short = true) {
  if (short) {
    return humanize(num, 1000)
  }

  return precision(num, 1)
}

function percent(value, total) {
  if (!value) {
    return 0
  }

  return number((value / total) * 100)
}

export {
  number,
  fileSize,
  precision,
  percent
}
