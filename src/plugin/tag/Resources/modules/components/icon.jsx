import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

const TagIcon = (props) =>
  <div
    className={classes(props.className, 'tag-icon', `tag-icon-${props.size}`)}
    style={get(props.tag, 'color') ? {
      color: get(props.tag, 'color')
    } : undefined}
    role="presentation"
  >
    <span className="fa fa-tag" />
  </div>

TagIcon.propTypes = {
  className: T.string,
  tag: T.shape({

  }).isRequired,
  size: T.oneOf(['xs', 'sm', 'md', 'lg', 'xl'])
}

TagIcon.defaultProps = {
  size: 'md'
}

export {
  TagIcon
}
