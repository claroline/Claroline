import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'

import {getComponent} from '#/plugin/exo/items/item-types'
import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata'

const ItemStats = props =>
  <div className="quiz-item item-paper card mb-3">
    <div className="card-body">
      <ItemMetadata
        item={props.item}
        showTitle={props.showTitle}
        numbering={props.numbering}
      />

      <hr className="item-content-separator my-4" />

      {createElement(getComponent(props.item.type, 'stats'), {
        item: props.item,
        stats: props.stats || {}
      })}
    </div>
  </div>

ItemStats.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    type: T.string.isRequired,
    title: T.string,
    description: T.string.isRequired,
    content: T.string.isRequired,
    hints: T.array
  }).isRequired,
  showTitle: T.bool,
  numbering: T.string,
  stats: T.object
}

export {
  ItemStats
}
