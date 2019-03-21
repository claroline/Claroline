import {PropTypes as T} from 'prop-types'

import {constants} from '#/plugin/exo/items/selection/constants'

const SelectionItem = {
  propTypes: {
    solutions: T.arrayOf(T.shape({
      selectionId: T.string.isRequired,
      score: T.number
    })),
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
    tries: T.number,
    _selectionPopover: T.bool,
    _text: T.string,
    _selectionId: T.string
  },
  defaultProps: {
    text: '',
    mode: constants.MODE_SELECT,
    globalScore: false,
    solutions: [],
    _selectionPopover: false,
    _text: '',
    penalty: 0
  }
}

export {
  SelectionItem
}