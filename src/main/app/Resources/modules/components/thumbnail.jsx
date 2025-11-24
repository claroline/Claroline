import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {asset} from '#/main/app/config'

function getInitials(name) {
  if (isEmpty(name)) {
    return null
  }

  let parts = name.split(' ')
  if (1 === parts.length) {
    parts = name.split('-')
  }

  let initials = parts[0].charAt(0)
  if (1 < parts.length) {
    initials += parts[parts.length - 1].charAt(0)
  }

  return initials
}

function getColor(str, s = 65, l = 40) {
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash);
  }

  let h = hash % 360;

  return 'hsl('+h+', '+s+'%, '+l+'%)';
}

const ThumbnailSkeleton = ({
  className,
  size = 'md',
  square = false
}) =>
  <div
    className={classes('placeholder thumbnail ratio z-0', className, {
      [`thumbnail-${size} ratio-thumbnail`]: !square,
      [`thumbnail-icon thumbnail-icon-${size} ratio-1x1`]: square
    })}
    aria-hidden={true}
  />

/**
 * A square visual representation of an entity.
 *
 * It displays:
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
const Thumbnail = ({
  className,
  thumbnail,
  name,
  color,
  children,
  size = 'md',
  square = false,
  border = false,
  loaded = true
}) => {
  let styles = {}
  if (loaded) {
    if (thumbnail) {
      styles = {
        backgroundImage: `url(${asset(thumbnail)})`,
        backgroundColor: 'transparent'
      }
    } else if (color) {
      styles = {
        color: color
      }
    } else if (name) {
      styles = {
        color: getColor(name)
      }
    }
  }

  const initials = getInitials(name)

  return (
    <div
      style={!isEmpty(styles) ? styles : undefined}
      className={classes('thumbnail ratio z-0', className, {
        'placeholder': !loaded,
        'thumbnail-icon-bordered': border,
        [`thumbnail-${size} ratio-thumbnail`]: !square,
        [`thumbnail-icon thumbnail-icon-${size} ratio-1x1`]: square,
      })}
      aria-hidden={true}
    >
      {!thumbnail && initials}

      {!thumbnail && !initials &&
        children
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
  square: T.bool,
  border: T.bool,
  loaded: T.bool
}

export {
  Thumbnail,
  ThumbnailSkeleton
}
