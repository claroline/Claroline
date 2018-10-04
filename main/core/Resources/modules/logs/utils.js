const convertTimestampToString = (timestamp) => {
  let result = null
  let duration = timestamp

  if (duration !== null) {
    const hours = Math.floor(duration / 3600)
    duration %= 3600
    const minutes = Math.floor(duration / 60)
    const seconds = duration % 60

    result = `${hours}:`
    result += 10 > minutes ? `0${minutes}:` : `${minutes}:`
    result += 10 > seconds ? `0${seconds}` : `${seconds}`
  }

  return result
}

export {
  convertTimestampToString
}
