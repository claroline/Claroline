import {constants} from '#/plugin/exo/resources/quiz/constants'

// TODO : deprecated. remove me

export function getNumbering(numberingType, idx) {
  switch (numberingType) {
    case constants.NUMBERING_NUMERIC: return idx + 1
    case constants.NUMBERING_LITTERAL: return 'abcdefghijklmnopqrstuvwxyz'[idx]
    default: return null
  }
}
