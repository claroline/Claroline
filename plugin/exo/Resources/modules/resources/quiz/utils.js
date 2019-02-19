import {constants} from '#/plugin/exo/resources/quiz/constants'

function getNumbering(type, idx) {
  switch (type) {
    /**
     * The numbering label is a number.
     */
    case constants.NUMBERING_NUMERIC:
      return idx + 1

    /**
     * The numbering label is a letter.
     */
    case constants.NUMBERING_LITERAL:
      return 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[idx]

    /**
     * The numbering feature is disabled.
     */
    default:
      return null
  }
}

export {
  getNumbering
}
