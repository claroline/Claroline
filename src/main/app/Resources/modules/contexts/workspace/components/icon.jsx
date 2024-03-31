import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {asset} from '#/main/app/config'

const WorkspaceIcon = (props) =>
  <div
    className={classes('workspace-icon context-icon', `context-icon-${props.size}`, props.className)}
    role="presentation"
    style={get(props.workspace, 'thumbnail') ? {
      backgroundImage: `url(${asset(props.workspace.thumbnail)})`,
      backgroundSize: 'cover',
      backgroundPosition: 'center'
    } : undefined}
  >
    {!get(props.workspace, 'thumbnail') &&
      get(props.workspace, 'name').charAt(0)
    }
  </div>

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
