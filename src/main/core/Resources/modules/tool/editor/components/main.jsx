import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {Editor} from '#/main/app/editor'

import {EditorRights} from '#/main/core/tool/editor/containers/rights'
import {EditorHistory} from '#/main/core/tool/editor/components/history'
import {selectors} from '#/main/core/tool/editor/store'

const ToolEditor = (props) => {
  useEffect(() => {
    if (props.loaded) {
      props.load(props.tool)
    }
  }, [props.contextType, props.contextId, props.name, props.loaded])

  return (
    <Editor
      path={props.path+'/edit'}
      title={trans(props.name, {}, 'tools')}
      name={selectors.STORE_NAME}
      target={['apiv2_tool_configure', {
        name: props.name,
        context: props.contextType,
        contextId: props.contextId
      }]}
      close={props.path}
      onSave={(savedData) => {
        console.log('onSave')
        console.log(savedData)
        props.refresh(props.name, savedData, props.contextType)}
      }
      defaultPage={props.defaultPage}
      overview={props.overview || (() => props.children)}
      pages={[
        {
          name: 'permissions',
          title: trans('permissions'),
          component: EditorRights,
          disabled: !hasPermission('administrate', props.tool)
        }, {
          name: 'history',
          title: trans('history'),
          component: EditorHistory
        }
      ].concat(props.pages || [])}
    />
  )
}

ToolEditor.propTypes = {
  pages: T.arrayOf(T.shape({

  })),

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
  menu: []
}

export {
  ToolEditor
}
