import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {url} from '#/main/app/api'
import {trans, number} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'

import {constants as baseConstants} from '#/main/evaluation/constants'

import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {constants} from '#/main/core/workspace/constants'
import {WorkspaceEvaluation as WorkspaceEvaluationTypes} from '#/main/evaluation/workspace/prop-types'
import {constants as evalConstants} from '#/main/evaluation/workspace/constants'
import {ContextMenu} from '#/main/app/context/containers/menu'

const WorkspaceImpersonation = (props) =>
  <section className="app-menu-status app-menu-impersonation">
    <LiquidGauge
      id="workspace-impersonation"
      type="warning"
      value={0}
      displayValue={() => <tspan className="fa fa-mask">&#xf6fa;</tspan>}
      width={70}
      height={70}
      preFilled={true}
    />

    <div className="app-menu-status-info">
      <h3 className="h5">
        {!isEmpty(props.roles) ?
          props.roles.map(role => trans(role.translationKey)).join(', ') :
          trans('guest')
        }
      </h3>

      {trans('view_as_info', {}, 'workspace')}
    </div>

    <div className="app-menu-status-toolbar">
      <Button
        className="btn-link"
        type={URL_BUTTON}
        label={trans('exit', {}, 'actions')}
        target={url(['claro_index', {}], {view_as: 'exit'}) + '#' + workspaceRoute(props.workspace)}
      />
    </div>
  </section>

WorkspaceImpersonation.propTypes = {
  roles: T.arrayOf(T.shape({
    translationKey: T.string.isRequired
  })),
  workspace: T.object.isRequired
}

const WorkspaceProgression = (props) =>
  <section className="app-menu-status">
    <h2 className="sr-only">
      {trans('my_progression')}
    </h2>

    <LiquidGauge
      id="workspace-progression"
      type="user"
      value={get(props.userEvaluation, 'progression') || 0}
      displayValue={(value) => number(value) + '%'}
      width={70}
      height={70}
    />

    <div className="app-menu-status-info">
      <h3 className="h5">
        {!isEmpty(props.roles) ?
          props.roles.map(role => trans(role.translationKey)).join(', ') :
          trans('guest')
        }
      </h3>

      {evalConstants.EVALUATION_STATUSES[get(props.userEvaluation, 'status', baseConstants.EVALUATION_STATUS_UNKNOWN)]}
    </div>
  </section>

WorkspaceProgression.propTypes = {
  roles: T.arrayOf(T.shape({
    translationKey: T.string.isRequired
  })),
  userEvaluation: T.shape(
    WorkspaceEvaluationTypes.propTypes
  )
}

const WorkspaceMenu = (props) => {
  return (
    <ContextMenu
      title={
        <>
          {/*<span className={classes({
            'fa fa-fw fa-globe icon-with-text-right': get(props.workspace, 'registration.selfRegistration'),
            'fa fa-fw fa-stamp icon-with-text-right': get(props.workspace, 'meta.model'),
            'fa fa-fw fa-user icon-with-text-right': get(props.workspace, 'meta.personal')
          })} aria-hidden={true} />*/}

          {!isEmpty(props.workspace) ? props.workspace.name : trans('workspace')}
        </>
      }

      tools={props.tools
        // hide tools that can not be configured in models for now
        .filter(tool => !get(props.workspace, 'meta.model', false) || -1 !== constants.WORKSPACE_MODEL_TOOLS.indexOf(tool.name))
      }
    >
      {false && !props.impersonated && get(props.workspace, 'display.showProgression') &&
        <WorkspaceProgression
          roles={props.roles}
          userEvaluation={props.userEvaluation}
        />
      }

      {false && !isEmpty(props.workspace) && props.impersonated &&
        <WorkspaceImpersonation
          roles={props.roles}
          workspace={props.workspace}
        />
      }
    </ContextMenu>
  )
}

WorkspaceMenu.propTypes = {
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ),
  impersonated: T.bool.isRequired,
  userEvaluation: T.shape(
    WorkspaceEvaluationTypes.propTypes
  ),
  roles: T.arrayOf(T.shape({
    translationKey: T.string.isRequired
  })),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  }))
}

WorkspaceMenu.defaultProps = {
  workspace: {}
}

export {
  WorkspaceMenu
}
