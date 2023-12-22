import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {asset} from '#/main/app/config'

const BadgeImage = (props) =>
  <div
    className={classes('badge-image', props.className)}
  >
    {get(props.badge, 'image') &&
      <img
        src={asset(get(props.badge, 'image'))}
        style={get(props.badge, 'color') ? {
          backgroundColor: get(props.badge, 'color')
        } : undefined}
      />
    }

    {!get(props.badge, 'image') &&
      <span className="fa fa-trophy" style={get(props.badge, 'color') ? {
        backgroundColor: get(props.badge, 'color')
      } : undefined} />
    }
  </div>

BadgeImage.propTypes = {
  className: T.string,
  badge: T.object
}

export {
  BadgeImage
}
