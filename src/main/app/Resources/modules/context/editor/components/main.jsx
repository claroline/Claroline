import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Editor} from '#/main/app/editor/components/main'

import {selectors} from '#/main/app/context/editor/store'
import {ContextEditorTools} from '#/main/app/context/editor/components/tools'

const ContextEditor = (props) => {
  useEffect(() => {
    props.getAvailableTools(props.contextName, props.contextId)
    props.openEditor(props.contextData, props.tools)
  }, [props.contextName, props.contextId])

  return (
    <Editor
      path={props.path+'/edit'}
      title={trans(props.contextName, {}, 'context') || props.title}
      name={selectors.FORM_NAME}
      onSave={(savedData) => props.refresh(props.name, savedData, props.contextType)}
      target={['claro_context_configure', {
        context: props.contextName,
        contextId: props.contextId
      }]}
      canAdministrate={true}
      close={props.path}
      overviewPage={props.overviewPage}
      appearancePage={props.appearancePage}
      historyPage={props.historyPage}
      actionsPage={props.actionsPage}
      defaultPage="overview"
      pages={[
        {
          name: 'tools',
          title: trans('tools'),
          help: trans('Choisissez et configurez les outils à activer en fonction des fonctionnalités dont vous avez besoin.'),
          component: ContextEditorTools
        }
      ].concat(props.pages || [])}
    />
  )
}

ContextEditor.propTypes = {
  path: T.string.isRequired,
  contextName: T.string.isRequired,
  contextId: T.string,
  tools: T.arrayOf(T.shape({

  })).isRequired,
  getAvailableTools: T.func.isRequired,
  openEditor: T.func.isRequired,
  refresh: T.func.isRequired
}

export {
  ContextEditor
}
