import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'

import {isHtmlEmpty} from '#/main/app/data/types/html/validators'
import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata'
import {Html} from '#/main/app/components/html'
import {getComponent} from '#/plugin/exo/items/item-types'

const ItemFeedback = props =>
  <div className="quiz-item quiz-item-feedback">
    <ItemMetadata
      showTitle={props.showTitle}
      item={props.item}
      numbering={props.numbering}
    />

    <hr className="item-content-separator my-4" />

    {createElement(getComponent(props.item.type, 'feedback'), {
      item: props.item,
      answer: props.answer && props.answer.data ? props.answer.data : undefined,
      showScore: false
    })}

    {(props.item.feedback && !isHtmlEmpty(props.item.feedback)) &&
      <div className="item-feedback">
        <span className="fa fa-comment" />
        <Html>{props.item.feedback}</Html>
      </div>
    }
  </div>

ItemFeedback.propTypes = {
  item: T.shape({
    title: T.string,
    description: T.string.isRequired,
    content: T.string.isRequired,
    hints: T.array,
    feedback: T.string
  }).isRequired,
  answer: T.shape({
    tries: T.number,
    data: T.any
  }),
  showTitle: T.bool,
  usedHints: T.array.isRequired,
  numbering: T.string
}

export {
  ItemFeedback
}
