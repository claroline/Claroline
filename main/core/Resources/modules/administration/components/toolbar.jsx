import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Toolbar} from '#/main/app/overlay/toolbar/components/toolbar'

import {selectors} from '#/main/core/administration/selectors'

// TODO : add impersonation action (see Workspaces)
// TODO : add about modal (see Workspaces)

const AdministrationToolbarComponent = props =>
  <Toolbar
    active={props.openedTool}
    tools={props.tools}
  />

AdministrationToolbarComponent.propTypes = {
  openedTool: T.string,
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    open: T.oneOfType([T.array, T.string])
  }))
}

const AdministrationToolbar = connect(
  (state) => ({
    tools: selectors.tools(state),
    openedTool: selectors.openedTool(state)
  })
)(AdministrationToolbarComponent)

export {
  AdministrationToolbar
}
