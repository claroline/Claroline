import {PropTypes as T} from 'prop-types'

import {constants} from '#/plugin/exo/items/selection/constants'

const SelectionItem = {
  propTypes: {
    solutions: T.arrayOf(T.oneOfType([
      // select
      T.shape({
        selectionId: T.string.isRequired,
        score: T.number,
        feedback: T.string
      }),
      // find
      T.shape({
        selectionId: T.string.isRequired,
        begin: T.number,
        end: T.number,
        score: T.number,
        feedback: T.string
      }),
      // highlight
      T.shape({
        selectionId: T.string.isRequired,
        answers: T.arrayOf(T.shape({
          colorId: T.string,
          score: T.number,
          feedback: T.string
        }))
      })
    ])),
    selections: T.arrayOf(T.shape({
      id: T.string.isRequired,
      begin: T.number.isRequired,
      end: T.number.isRequired,
      displayedBegin: T.number,
      displayedEnd: T.number
    })),
    colors: T.arrayOf(T.shape({
      id: T.string.isRequired,
      code: T.string,
      _autoOpen: T.bool
    })),
    text: T.string.isRequired,
    globalScore: T.bool.isRequired,
    mode: T.string.isRequired,
    penalty: T.number.isRequired,
    tries: T.number
  },
  defaultProps: {
    text: '',
    mode: constants.MODE_SELECT,
    globalScore: false,
    solutions: [],
    penalty: 0
  }
}

export {
  SelectionItem
}