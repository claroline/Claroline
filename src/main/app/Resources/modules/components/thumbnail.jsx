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
 * - in the primary column of the table component (size MUST be "xs" and square=true)
 * - as a data card icon (size MUST be linked to the card size)
 * - as a page icon (size MUST be "xl" for details page or "md" for any other page and square=true)
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
    <div
      style={!isEmpty(styles) ? styles : undefined}
      className={classes('thumbnail', props.className, {
        [`thumbnail-${props.size} ratio ratio-thumbnail`]: !props.square,
        [`thumbnail-icon thumbnail-icon-${props.size}`]: props.square
      })}
      aria-hidden={true}
    >
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
  children: T.node,
  square: T.bool
}

Thumbnail.defaultProps = {
  size: 'md',
  square: false
}

export {
  Thumbnail
}
