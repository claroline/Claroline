export function camelToSnake(text) {
  return text.replace(/([A-Z])/g, $1 => { return '_'+$1.toLowerCase() })
}