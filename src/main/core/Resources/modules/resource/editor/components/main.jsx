import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {Editor} from '#/main/app/editor/components/main'

import {selectors} from '#/main/core/resource/store'
import {EditorOverview} from '#/main/core/resource/editor/components/overview'
import {EditorRights} from '#/main/core/resource/editor/components/rights'
import {EditorHistory} from '#/main/core/resource/editor/components/history'

const ResourceEditor = (props) => {
  const resourcePath = useSelector(selectors.path)
  const resourceType = useSelector(selectors.resourceType)

  useEffect(() => {
    if (props.loaded) {
      props.load(props.resourceNode)
    }
  }, [get(props.resourceNode, 'id'), props.loaded])

  return (
    <Editor
      path={resourcePath+'/edit'}
      title={resourceType ? trans(resourceType, {}, 'resource') : trans('resource')}
      name={selectors.EDITOR_NAME}
      target={['claro_resource_action', {
        action: 'configure',
        id: get(props.resourceNode, 'id')
      }]}
      close={resourcePath}
      onSave={(savedData) => props.refresh(savedData)}
      defaultPage={props.defaultPage}
      overview={props.overview}
      pages={[
        {
          name: 'permissions',
          title: trans('permissions'),
          component: EditorRights,
          disabled: !hasPermission('administrate', props.resourceNode || {})
        }, {
          name: 'history',
          title: trans('history'),
          component: EditorHistory
        }
      ].concat(props.pages || [])}
    />
  )
}

ResourceEditor.propTypes = {
  overview: T.elementType,
  defaultPage: T.string
}

ResourceEditor.defaultProps = {
  overview: EditorOverview
}

export {
  ResourceEditor
}
