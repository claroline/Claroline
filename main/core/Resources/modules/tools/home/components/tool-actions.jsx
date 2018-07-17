import React from 'react'
import {PropTypes as T} from 'prop-types'

import {matchPath, withRouter} from '#/main/app/router'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions'
import {PageGroupActions} from '#/main/core/layout/page'

const ToolActionsComponent = props =>
  <PageGroupActions>
    <FormPageActionsContainer
      formName="editor"
      target={['apiv2_home_update']}
      opened={!!matchPath(props.location.pathname, {path: '/edit'})}
      open={{
        type: 'link',
        target: '/edit'
      }}
      cancel={{
        type: 'link',
        target: '/',
        exact: true
      }}
    />
  </PageGroupActions>

ToolActionsComponent.propTypes = {
  location: T.shape({
    pathname: T.string
  }).isRequired
}

const ToolActions = withRouter(ToolActionsComponent)

export {
  ToolActions
}
