import React from 'react'
import {PropTypes as T} from 'prop-types'

import {matchPath, withRouter} from '#/main/app/router'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'
import {PageActions} from '#/main/core/layout/page'


const ToolActionsComponent = props =>

  <PageActions>
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
  </PageActions>


ToolActionsComponent.propTypes = {
  location: T.shape({
    pathname: T.string
  }).isRequired
}

const ToolActions = withRouter(ToolActionsComponent)

export {
  ToolActions
}
