import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Thumbnail} from '#/main/app/components/thumbnail'

const TagIcon = (props) =>
  <Thumbnail
    className={props.className}
    size={props.size}
    name={get(props.tag, 'name')}
    color={get(props.tag, 'color')}
    square={true}
  >
    <span className="fa fa-tag" />
  </Thumbnail>

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
