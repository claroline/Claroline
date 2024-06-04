import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

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
 * - in the primary column of the table component (size MUST be "xs")
 * - as a data card icon (size MUST be linked to the card size)
 * - as a page icon (size MUST be "xl" for details page or "md" for any other page)
 */
const ThumbnailIcon = (props) => {
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
    <div className={classes('thumbnail-icon', props.className, `thumbnail-icon-${props.size}`)} style={styles}>
      {!props.thumbnail && props.name &&
        props.name.charAt(0)
      }

      {!props.thumbnail && !props.name &&
        props.children
      }
    </div>
  )
}

ThumbnailIcon.propTypes = {
  className: T.string,
  size: T.oneOf(['xs', 'sm', 'md', 'lg', 'xl']),
  thumbnail: T.string,
  name: T.string,
  color: T.string,
  children: T.node
}

ThumbnailIcon.defaultProps = {
  size: 'md'
}

export {
  ThumbnailIcon
}
