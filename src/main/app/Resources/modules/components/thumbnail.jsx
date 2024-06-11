import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {asset} from '#/main/app/config'

/**
 * A square visual representation of an entity.
 *
 * It displays :
 * - A color
 * - A thumbnail image
 * - OR The first letter of the entity name
 * - OR A generic icon of the entity type
 *
 * Common usages :
 * - In a data card
 */
const Thumbnail = (props) => {
  let styles = {}
  if (props.thumbnail) {
    styles = {
      backgroundImage: `url(${asset(props.thumbnail)})`,
      backgroundColor: 'transparent'
    }
  } else if (props.color) {
    styles = {
      color: props.color
    }
  }

  return (
    <div className={classes('thumbnail ratio ratio-thumbnail', `thumbnail-${props.size}`)} style={!isEmpty(styles) ? styles : undefined}>
      {!props.thumbnail && props.name &&
        props.name.charAt(0)
      }

      {!props.thumbnail && !props.name &&
        props.children
      }
    </div>
  )
}

Thumbnail.propTypes = {
  className: T.string,
  size: T.oneOf(['xs', 'sm', 'md', 'lg', 'xl']),
  thumbnail: T.string,
  name: T.string,
  color: T.string,
  children: T.node
}

Thumbnail.defaultProps = {
  size: 'md'
}

export {
  Thumbnail
}
