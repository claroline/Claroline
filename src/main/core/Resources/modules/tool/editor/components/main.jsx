import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {Editor} from '#/main/app/editor'

import {selectors} from '#/main/core/tool/editor/store'
import {ToolEditorPermissions} from '#/main/core/tool/editor/containers/permissions'
import {ToolEditorHistory} from '#/main/core/tool/editor/components/history'
import {ToolEditorAppearance} from '#/main/core/tool/editor/components/appearance'
import {ToolEditorOverview} from '#/main/core/tool/editor/components/overview'
import {ToolEditorActions} from '#/main/core/tool/editor/components/actions'

const ToolEditor = (props) => {
  useEffect(() => {
    if (props.loaded) {
      const initialData = Object.assign({}, props.additionalData() || {}, {data: props.tool})

      props.load(initialData)
    }
  }, [props.contextType, props.contextId, props.name, props.loaded])

  return (
    <Editor
      path={props.path+'/edit'}
      title={trans(props.name, {}, 'tools')}
      name={selectors.STORE_NAME}
      target={['claro_tool_configure', {
        name: props.name,
        context: props.contextType,
        contextId: props.contextId
      }]}
      close={props.path}
      onSave={(savedData) => props.refresh(props.name, savedData, props.contextType)}
      defaultPage={props.defaultPage}
      canAdministrate={hasPermission('administrate', props.tool)}
      overviewPage={props.children ? (() => props.children) : props.overviewPage}
      appearancePage={props.appearancePage}
      historyPage={props.historyPage}
      permissionsPage={props.permissionsPage}
      actionsPage={props.actionsPage}
      pages={props.pages || []}
    />
  )
}

ToolEditor.propTypes = {
  defaultPage: T.string,
  // standard pages
  overviewPage: T.elementType,
  appearancePage: T.elementType,
  historyPage: T.elementType,
  permissionsPage: T.elementType,
  actionsPage: T.elementType,
  // custom pages
  pages: T.arrayOf(T.shape({

  })),

  /**
   * A func that returns some data to add to the Editor store on initialization.
   */
  additionalData: T.func,

  // from store
  loaded: T.bool.isRequired,
  path: T.string.isRequired,
  name: T.string,
  tool: T.object,
  contextType: T.string.isRequired,
  contextId: T.string,
  load: T.func.isRequired,
  refresh: T.func.isRequired
}

ToolEditor.defaultProps = {
  menu: [],
  overviewPage: ToolEditorOverview,
  appearancePage: ToolEditorAppearance,
  historyPage: ToolEditorHistory,
  permissionsPage: ToolEditorPermissions,
  actionsPage: ToolEditorActions,
  defaultPage: 'overview',
  additionalData: () => ({})
}

export {
  ToolEditor
}
