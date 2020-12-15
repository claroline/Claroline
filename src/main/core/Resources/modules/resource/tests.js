import freeze from 'deep-freeze'

function resourceNodeFixture() {
  return freeze({
    rights: {
      current: {edit: true}
    },
    meta: {
      published: false
    },
    display: {
      fullscreen: false
    }
  })
}

export {
  resourceNodeFixture
}