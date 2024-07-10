import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Thumbnail} from '#/main/app/components/thumbnail'

const BadgeImage = (props) =>
  <Thumbnail
    className={props.className}
    size={props.size}
    color={get(props.badge, 'color')}
    thumbnail={get(props.badge, 'image')}
    name={get(props.badge, 'name')}
    square={true}
  >
    <span className="fa fa-trophy" aria-hidden={true} />
  </Thumbnail>

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
