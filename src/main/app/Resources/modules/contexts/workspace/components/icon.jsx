import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {ThumbnailIcon} from '#/main/app/components/thumbnail-icon'

const WorkspaceIcon = (props) =>
  <ThumbnailIcon
    className={props.className}
    size={props.size}
    thumbnail={get(props.workspace, 'thumbnail')}
    name={get(props.workspace, 'name')}
  />

WorkspaceIcon.propTypes = {
  className: T.string,
  workspace: T.object,
  size: T.oneOf(['xs', 'sm', 'md', 'lg', 'xl'])
}

WorkspaceIcon.defaultProps = {
  size: 'md'
}

export {
  WorkspaceIcon
}
