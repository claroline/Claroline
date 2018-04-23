import React from 'react'
import {PropTypes as T} from 'prop-types'

import {HtmlText} from '#/main/core/layout/components/html-text'
import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata'

const ItemFeedback = props => {
  const tmp = document.createElement('div')
  tmp.innerHTML = props.item.feedback
  const displayFeedback = (/\S/.test(tmp.textContent)) && props.item.feedback

  return (
    <div className="quiz-item quiz-item-feedback">
      {props.item.title &&
        <h3 className="item-title">{props.item.title}</h3>
      }

      <ItemMetadata item={props.item} numbering={props.numbering}/>

      <hr className="item-content-separator" />

      {props.children}

      {displayFeedback &&
        <div className="item-feedback">
          <span className="fa fa-comment" />
          <HtmlText>{props.item.feedback}</HtmlText>
        </div>
      }
    </div>
  )
}

ItemFeedback.propTypes = {
  item: T.shape({
    title: T.string,
    description: T.string.isRequired,
    content: T.string.isRequired,
    hints: T.array,
    feedback: T.string
  }).isRequired,
  usedHints: T.array.isRequired,
  children: T.node.isRequired,
  numbering: T.string
}

export {ItemFeedback}
