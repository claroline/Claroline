
/**
 * Normalizes a string to be used as a key (eg. id, react array `key`).
 *
 * @param text
 */
function toKey(text) {
  return text
    // removes multiple whitespaces, new lines & tabs by single whitespace
    .replace(/\s\s+/g, ' ')
    // removes all non alpha-numeric chars
    .replace(/[^a-zA-Z0-9 ]/g, '')
    // replaces whitespaces by hyphen
    .replace(' ', '-')
    // removes uppercase
    .toLowerCase()
    // shorten text
    .substring(0, 20)
}

export {
  toKey
}
