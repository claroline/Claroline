import React, {PropTypes as T} from 'react'
import {Highlight} from './utils/highlight.jsx'
import {PaperTabs} from '../components/paper-tabs.jsx'

export const ClozePaper = (props) => {
  return (
    <PaperTabs
      item={props.item}
      answer={props.answer}
      id={props.item.id}
      yours={
        <Highlight
          item={props.item}
          answer={props.answer}
          showScore={true}
          displayTrueAnswer={false}
        />
      }
      expected={
        <Highlight
          item={props.item}
          answer={props.answer}
          showScore={true}
          displayTrueAnswer={true}
        />
      }
    />
  )
}

ClozePaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired,
    description: T.string.isRequired,
    solutions: T.arrayOf(T.object)
  }).isRequired,
  answer: T.array.isRequired
}

ClozePaper.defaultProps = {
  answer: []
}
