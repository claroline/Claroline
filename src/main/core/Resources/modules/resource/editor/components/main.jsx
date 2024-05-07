import React, {useCallback, useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {Editor} from '#/main/app/editor/components/main'
import {actions as formActions} from '#/main/app/content/form'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {ResourceEditorAppearance} from '#/main/core/resource/editor/components/appearance'
import {ResourceEditorOverview} from '#/main/core/resource/editor/components/overview'
import {ResourceEditorPermissions} from '#/main/core/resource/editor/containers/permissions'
import {ResourceEditorHistory} from '#/main/core/resource/editor/components/history'
import {ResourceEditorEvaluation} from '#/main/core/resource/editor/components/evaluation'
import {ResourceEditorActions} from '#/main/core/resource/editor/components/actions'

import {actions, selectors} from '#/main/core/resource/editor/store'

const ResourceEditor = (props) => {
  const resourcePath = useSelector(resourceSelectors.path)
  const resourceType = useSelector(resourceSelectors.resourceType)
  const resourceLoaded = useSelector(resourceSelectors.loaded)
  const resourceNode = useSelector(resourceSelectors.resourceNode)
  const editedNode = useSelector(selectors.resourceNode)

  const dispatch = useDispatch()
  const refresh = useCallback((resourceData) => dispatch(actions.refresh(resourceType, resourceData)), [resourceType])

  useEffect(() => {
    if (resourceLoaded) {
      const initialData = Object.assign({}, props.additionalData() || {}, {resourceNode: resourceNode})
      dispatch(formActions.reset(resourceSelectors.EDITOR_NAME, initialData))
    }
  }, [get(resourceNode, 'id'), resourceLoaded])

  return (
    <Editor
      path={resourcePath+'/edit'}
      title={get(editedNode, 'name') || (resourceType && trans(resourceType, {}, 'resource')) || trans('resource')}
      styles={props.styles}
      name={resourceSelectors.EDITOR_NAME}
      target={['claro_resource_action', {
        action: 'configure',
        id: get(resourceNode, 'id')
      }]}
      close={resourcePath}
      onSave={refresh}
      canAdministrate={hasPermission('administrate', resourceNode || {})}
      defaultPage={props.defaultPage}
      overviewPage={props.overviewPage}
      appearancePage={props.appearancePage}
      historyPage={props.historyPage}
      permissionsPage={props.permissionsPage}
      actionsPage={props.actionsPage}
      pages={[
        {
          name: 'evaluation',
          title: trans('evaluation'),
          help: trans('Activez le suivi pédagogique pour enregistrer et suivre la progression des utilisateurs.'),
          component: ResourceEditorEvaluation
        }
      ].concat(props.pages || [])}
    />
  )
}

ResourceEditor.propTypes = {
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
  styles: T.array
}

ResourceEditor.defaultProps = {
  defaultPage: 'overview',
  overviewPage: ResourceEditorOverview,
  appearancePage: ResourceEditorAppearance,
  historyPage: ResourceEditorHistory,
  permissionsPage: ResourceEditorPermissions,
  actionsPage: ResourceEditorActions,
  pages: [],
  actions: [],
  additionalData: () => ({})
}

export {
  ResourceEditor
}
