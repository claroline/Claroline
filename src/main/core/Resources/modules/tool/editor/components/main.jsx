import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {Routes} from '#/main/app/router'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/components/page'

import {EditorParameters} from '#/main/core/tool/editor/containers/parameters'
import {EditorRights} from '#/main/core/tool/editor/containers/rights'
import {EditorHistory} from '#/main/core/tool/editor/components/history'

const ToolEditor = (props) => {
  useEffect(() => {
    props.openEditor(props.name, props.contextType, props.contextId, props.tool)
  }, [props.contextType, props.contextId, props.name])

  return (
    <ToolPage
      title={trans('parameters')}
      actions={[
        {
          name: 'edit-poster',
          type: CALLBACK_BUTTON,
          label: trans('Modifier la couverture'),
          callback: () => true
        }
      ]}
      menu={{
        nav: [
          {
            name: 'overview',
            label: trans('about'),
            type: LINK_BUTTON,
            target: props.path+'/edit',
            exact: true
          }, {
            name: 'permissions',
            label: trans('permissions'),
            type: LINK_BUTTON,
            target: props.path+'/edit/permissions',
            displayed: hasPermission('administrate', props.tool)
          }, {
            name: 'history',
            label: trans('history'),
            type: LINK_BUTTON,
            target: props.path+'/edit/history'
          }
        ],
        actions: [
          {
            name: 'close',
            label: trans('close'),
            icon: 'fa far fa-fw fa-times-circle',
            type: LINK_BUTTON,
            target: props.path,
            exact: true
          }
        ]
      }}
    >
      <Routes
        path={props.path+'/edit'}
        routes={[
          {
            path: '/',
            exact: true,
            component: EditorParameters
          }, {
            path: '/permissions',
            component: EditorRights,
            disabled: !hasPermission('administrate', props.tool)
          }, {
            path: '/history',
            component: EditorHistory
          }
        ]}
      />
    </ToolPage>
  )
}

ToolEditor.propTypes = {
  open: T.func,

  // from store
  path: T.string.isRequired,
  name: T.string,
  tool: T.object,
  contextType: T.string.isRequired,
  contextId: T.string,
  openEditor: T.func.isRequired,
  refresh: T.func.isRequired
}

export {
  ToolEditor
}
