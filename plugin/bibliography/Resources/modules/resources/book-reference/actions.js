
import { makeActionCreator } from '#/main/core/scaffolding/actions'

export const BOOK_REFERENCE_SET = 'BOOK_REFERENCE_SET'

export const actions = {
  setBookReference: makeActionCreator(BOOK_REFERENCE_SET, 'bookReference')
}