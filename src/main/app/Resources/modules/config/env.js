/* global process */

function env() {
  return process.env.NODE_ENV // this is set by webpack
}

export {
  env
}
