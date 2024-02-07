import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata'

const ContentItemPlayer = props =>
  <div className="quiz-item item-player">
    <ItemMetadata
      showTitle={props.showTitle}
      item={props.item}
      isContentItem={true}
    />

    {(props.item.title || props.item.description) &&
      <hr className="item-content-separator" />
    }

    {props.children}
  </div>

ContentItemPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string
  }).isRequired,
  showTitle: T.bool,
  children: T.node.isRequired
}

export {
  ContentItemPlayer
}
