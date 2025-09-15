import React from 'react'
import {PropTypes as T} from 'prop-types'

import {MediaThumbnail} from '#/plugin/exo/data/types/medias/components/thumbnail'

const MediasDisplay = (props) =>
  <div className="d-flex flex-row flex-wrap gap-2">
    {props.data.map((object) =>
      <MediaThumbnail
        id={object.id}
        key={`item-object-${object.id}-thumbnail`}
        data={object.data || object.url}
        type={object.type}
      />
    )}
  </div>

MediasDisplay.propTypes = {
  data: T.arrayOf(T.shape({
    id: T.string.isRequired,
    type: T.string.isRequired,
    url: T.string,
    data: T.string
  })).isRequired
}

export {
  MediasDisplay
}
