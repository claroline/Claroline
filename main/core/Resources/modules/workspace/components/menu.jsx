import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {number} from '#/main/app/intl'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'

import {MenuMain} from '#/main/app/layout/menu/containers/main'
import {ToolMenu} from '#/main/core/tool/containers/menu'

import {getActions} from '#/main/core/workspace/utils'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

const WorkspaceMenu = (props) =>
  <MenuMain
    title={!isEmpty(props.workspace) ? props.workspace.name : trans('workspace')}
    backAction={{
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-angle-double-left',
      label: trans('workspaces'),
      target: '/desktop/workspaces',
      exact: true
    }}

    tools={props.tools.map(tool => ({
      name: tool.name,
      icon: tool.icon,
      path: `/desktop/workspaces/open/${props.workspace.id}/${tool.name}`
    }))}
    actions={!isEmpty(props.workspace) ? getActions([props.workspace], {

    }, props.basePath, props.currentUser) : []}
  >
    <section className="user-progression">
      <h2 className="sr-only">
        Ma progression
      </h2>

      <LiquidGauge
        id="workspace-progression"
        type="user"
        value={50}
        displayValue={(value) => number(value) + '%'}
        width={70}
        height={70}
      />

      <div className="user-progression-info">
        <h3 className="h4">Collaborateur</h3>
        {trans('Vous n\'avez pas terminé toutes les activités disponibles.')}
      </div>
    </section>

    <ToolMenu
      opened={'tool' === props.section}
      toggle={() => props.changeSection('tool')}
    />
  </MenuMain>

WorkspaceMenu.propTypes = {
  basePath: T.string,
  section: T.string,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ),
  currentUser: T.shape({
    // TODO
  }),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired
  })),
  changeSection: T.func.isRequired,
  startWalkthrough: T.func.isRequired
}

WorkspaceMenu.defaultProps = {
  workspace: {}
}

export {
  WorkspaceMenu
}
