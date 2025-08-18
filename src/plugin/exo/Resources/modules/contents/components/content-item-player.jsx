import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata'
import {getContentDefinition} from '#/plugin/exo/contents/utils'

const ContentItemPlayer = props =>
  <div className="quiz-item item-player">
    <ItemMetadata
      showTitle={props.showTitle}
      item={props.item}
      isContentItem={true}
    />

    {(props.item.title || props.item.description) &&
      <hr className="item-content-separator my-4" />
    }

    {React.createElement(getContentDefinition(props.item.type).player, {
      item: props.item
    })}
  </div>

ContentItemPlayer.propTypes = {
  id: T.string.isRequired,
  item: T.shape({
    id: T.string.isRequired,
    type: T.string.isRequired,
    title: T.string,
    description: T.string
  }).isRequired,
  showTitle: T.bool
}

export {
  ContentItemPlayer
}
