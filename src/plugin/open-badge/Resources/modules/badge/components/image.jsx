import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {asset} from '#/main/app/config'
import {ThumbnailIcon} from '#/main/app/components/thumbnail-icon'

const BadgeImage = (props) =>
  <ThumbnailIcon
    className={props.className}
    size={props.size}
    color={get(props.badge, 'color')}
    thumbnail={get(props.badge, 'image') ? asset(get(props.badge, 'image')) : null}
    name={get(props.badge, 'name')}
  >
    <span className="fa fa-trophy" aria-hidden={true} />
  </ThumbnailIcon>

BadgeImage.propTypes = {
  className: T.string,
  badge: T.object,
  size: T.oneOf(['xs', 'sm', 'md', 'lg', 'xl'])
}

BadgeImage.defaultProps = {
  size: 'md'
}

export {
  BadgeImage
}
