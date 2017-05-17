
export function countPages(totalResults, pageSize) {
  if (-1 === pageSize) {
    return 1
  }

  const rest = totalResults % pageSize
  const nbPages = (totalResults - rest) / pageSize

  return nbPages + (rest > 0 ? 1 : 0)
}
