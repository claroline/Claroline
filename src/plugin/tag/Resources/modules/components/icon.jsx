import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {ThumbnailIcon} from '#/main/app/components/thumbnail-icon'

const TagIcon = (props) =>
  <ThumbnailIcon
    className={props.className}
    color={get(props.tag, 'color')}
    size={props.size}
  >
    <span className="fa fa-tag" />
  </ThumbnailIcon>

TagIcon.propTypes = {
  className: T.string,
  tag: T.shape({
    color: T.string
  }).isRequired,
  size: T.oneOf(['xs', 'sm', 'md', 'lg', 'xl'])
}

TagIcon.defaultProps = {
  size: 'md'
}

export {
  TagIcon
}
