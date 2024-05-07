import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {ContentRights} from '#/main/app/content/components/rights'
import {EditorPage} from '#/main/app/editor'
import {trans} from '#/main/app/intl'

const ToolEditorPermissions = (props) => {
  useEffect(() => {
    props.load(props.name, props.contextType, get(props.contextData, 'id'))
  }, [props.name, props.contextType, get(props.contextData, 'id')])

  return (
    <EditorPage
      title={trans('permissions')}
      help={trans('Gérez les différents droits d\'accès et de modifications de vos utilisateurs.')}
      managerOnly={true}
    >
      {props.rights &&
        <ContentRights
          workspace={props.contextData}
          rights={props.rights}
          updateRights={props.updateRights}
        />
      }
    </EditorPage>
  )
}

ToolEditorPermissions.propTypes = {
  name: T.string.isRequired,
  contextType: T.string.isRequired,
  contextData: T.object,
  load: T.func.isRequired,
  updateRights: T.func.isRequired
}

export {
  ToolEditorPermissions
}
