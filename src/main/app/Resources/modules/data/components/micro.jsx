import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Thumbnail} from '#/main/app/components/thumbnail'

const DataMicro = (props) =>
  <div className={classes('d-flex flex-row gap-3 align-items-center text-nowrap text-truncate', props.className)} role="presentation">
    <Thumbnail
      loaded={props.loaded}
      thumbnail={props.object.thumbnail || props.object.poster}
      name={props.object.name}
      size="xs"
      square={true}
      color={props.color}
    />
    <span className={classes('text-truncate', !props.loaded && 'placeholder rounded-1 w-25')} role="presentation">{props.object.name}</span>
  </div>

DataMicro.propTypes = {
  loaded: T.bool,
  className: T.string,
  color: T.string,
  object: T.shape({
    name: T.string.isRequired,
    thumbnail: T.string,
    poster: T.string
  })
}

DataMicro.defaultProps = {
  loaded: true
}

export {
  DataMicro
}
