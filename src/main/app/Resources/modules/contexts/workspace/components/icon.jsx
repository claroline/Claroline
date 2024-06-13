import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Thumbnail} from '#/main/app/components/thumbnail'

const WorkspaceIcon = (props) =>
  <Thumbnail
    className={props.className}
    size={props.size}
    name={get(props.workspace, 'name')}
    thumbnail={get(props.workspace, 'thumbnail')}
    square={true}
  >
    <span className="fa fa-book" aria-hidden={true} />
  </Thumbnail>

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
