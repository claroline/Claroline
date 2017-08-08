import {
  NUMBERING_LITTERAL,
  NUMBERING_NUMERIC
} from './../quiz/enums'

export function getNumbering(numberingType, idx) {
  switch (numberingType) {
    case NUMBERING_NUMERIC: return idx + 1
    case NUMBERING_LITTERAL: return 'abcdefghijklmnopqrstuvwxyz'[idx]
    default: return null
  }
}
