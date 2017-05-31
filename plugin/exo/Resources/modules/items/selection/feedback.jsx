import React from 'react'
import {PropTypes as T} from 'prop-types'

import {SelectionText} from './utils/selection-text.jsx'
import {getReactAnswerSelections} from './utils/selection-answer.jsx'

export const SelectionFeedback = (props) => {
  const elements = props.item.mode === 'find' ? props.item.solutions: props.item.selections

  return (<SelectionText
     anchorPrefix="selection-element-feedback"
     className="selection-feedback"
     text={props.item.text}
     elements={elements}
     selections={getReactAnswerSelections(props.item, props.answer, false)}
  />)
}

SelectionFeedback.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    mode: T.string.isRequired,
    text: T.string.isRequired,
    solutions: T.arrayOf(T.object),
    selections: T.arrayOf(T.object)
  }).isRequired,
  answer: T.oneOfType([
    T.shape({
      selections: T.arrayOf(
        T.string
      ),
      mode: T.string.isRequired
    }),
    T.shape({
      highlights: T.arrayOf(
        T.shape({
          selectionId: T.string.isRequired,
          colorId: T.string.isRequired
        })
      ),
      mode: T.string.isRequired
    }),
    T.shape({
      tries: T.number.isRequired,
      positions: T.arrayOf(
        T.number
      ),
      mode: T.string.isRequired
    })
  ]).isRequired
}
