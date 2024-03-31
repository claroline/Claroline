import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {asset} from '#/main/app/config'

const BadgeImage = (props) =>
  <div
    className={classes('badge-image', `badge-image-${props.size}`, props.className)}
    role="presentation"
    style={get(props.badge, 'color') ? {
      backgroundColor: get(props.badge, 'color')
    } : undefined}
  >
    {get(props.badge, 'image') &&
      <img src={asset(get(props.badge, 'image'))} />
    }

    {!get(props.badge, 'image') &&
      <span className="fa fa-trophy" />
    }
  </div>

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
