import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {asset} from '#/main/app/config'

const Poster = (props) =>
  <div
    className={classes(props.className, 'poster ratio ratio-poster')}
    role="presentation"
    style={{
      backgroundImage: `url("${asset(props.url)}")`
    }}
    aria-hidden={true}
  />

Poster.propTypes = {
  className: T.string,
  url: T.string.isRequired
}

export {
  Poster
}
