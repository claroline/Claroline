import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {PageFull} from '#/main/app/page/components/full'

import {ToolIcon} from '#/main/core/tool/components/icon'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'
import {MODAL_TOOL_RIGHTS} from '#/main/core/tool/modals/rights'
import {MODAL_TOOL_PARAMETERS} from '#/main/core/tool/modals/parameters'

const ToolPage = props => {
  const baseActions = [
    {
      name: 'configure',
      type: MODAL_BUTTON,
      icon: 'fa fa-fw fa-cog',
      label: trans('configure', {}, 'actions'),
      modal: [MODAL_TOOL_PARAMETERS, {
        toolName: props.name,
        currentContext: props.currentContext,
        data: props.toolData,
        onSave: (updatedData) => props.update(updatedData, props.currentContext)
      }],
      displayed: 'administration' !== props.currentContext.type && hasPermission('edit', props.toolData),
      group: trans('management')
    }, {
      name: 'rights',
      type: MODAL_BUTTON,
      icon: 'fa fa-fw fa-lock',
      label: trans('edit-rights', {}, 'actions'),
      modal: [MODAL_TOOL_RIGHTS, {
        toolName: props.name,
        currentContext: props.currentContext
      }],
      displayed: 'administration' !== props.currentContext.type && hasPermission('administrate', props.toolData),
      group: trans('management')
    }
  ]

  let actions
  if (props.actions instanceof Promise) {
    actions = props.actions.then(promisedActions => promisedActions.concat(baseActions))
  } else {
    actions = (props.actions || []).concat(baseActions)
  }

  let toolbar = 'edit rights'
  if (props.primaryAction) {
    toolbar = props.primaryAction + ' | ' + toolbar
  }
  toolbar += ' | fullscreen more'

  return (
    <PageFull
      className={`${props.name}-page`}
      title={trans(props.name, {}, 'tools')}
      showBreadcrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
      path={[].concat(getToolBreadcrumb(props.name, props.currentContext.type, props.currentContext.data), props.path)}
      poster={get(props.toolData, 'poster.url')}
      icon={get(props.toolData, 'display.showIcon') ?
        <ToolIcon type={get(props.toolData, 'icon')} />
        :
        undefined
      }
      meta={{
        title: `${trans(props.name, {}, 'tools')} - ${'workspace' === props.currentContext.type ? props.currentContext.data.code : trans(props.currentContext.type)}`,
        description: get(props.currentContext.data, 'meta.description')
      }}

      {...omit(props, 'name', 'currentContext', 'path', 'toolData', 'update')}
      actions={actions}
      toolbar={toolbar}
    >
      {props.children}
    </PageFull>
  )
}

ToolPage.propTypes = {
  // tool props
  name: T.string.isRequired,
  toolData: T.shape({
    icon: T.string,
    display: T.shape({
      showIcon: T.bool
    }),
    poster: T.shape({
      url: T.string.isRequired
    }),
    permissions: T.object.isRequired
  }),
  currentContext: T.shape({
    type: T.oneOf(['administration', 'desktop', 'workspace']),
    data: T.object
  }).isRequired,
  // the name of the primary action of the tool (if we want to override the default one).
  // it can contain more than one action name
  primaryAction: T.string,

  // from store
  update: T.func.isRequired,

  // page props
  subtitle: T.node,
  actions: T.any,
  path: T.arrayOf(T.object),
  children: T.any
}

ToolPage.defaultProps = {
  path: []
}

export {
  ToolPage
}
