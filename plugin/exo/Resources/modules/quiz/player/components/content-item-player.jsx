import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Metadata as ItemMetadata} from './../../../items/components/metadata.jsx'

const ContentItemPlayer = props =>
  <div className="item-player">
    {props.item.title &&
      <h3 className="item-title">{props.item.title}</h3>
    }
    <ItemMetadata item={props.item} isContentItem={true}/>
    {(props.item.title || props.item.description) &&
      <hr/>
    }
    {props.children}
  </div>

ContentItemPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string
  }).isRequired,
  children: T.node.isRequired
}

export {ContentItemPlayer}
