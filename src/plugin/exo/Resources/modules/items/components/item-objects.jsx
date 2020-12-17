import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentThumbnail} from '#/plugin/exo/contents/components/content-thumbnail'

const ObjectsPlayer = props =>
  <div className="item-object-thumbnail-box">
    {props.item.objects.map((object, index) =>
      <ContentThumbnail
        id={object.id}
        index={index}
        key={`item-object-${object.id}-thumbnail`}
        data={object.data || object.url}
        type={object.type}
      />
    )}
  </div>

ObjectsPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    objects: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      url: T.string,
      data: T.string
    })).isRequired
  }).isRequired
}

export {
  ObjectsPlayer
}
