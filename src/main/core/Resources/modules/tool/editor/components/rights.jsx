import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {ContentRights} from '#/main/app/content/components/rights'

const EditorRights = (props) => {
  useEffect(() => {
    props.load(props.name, props.contextType, get(props.contextData, 'id'))
  }, [props.name, props.contextType, get(props.contextData, 'id')])

  return (
    <>
      {props.rights &&
        <ContentRights
          workspace={props.contextData}
          rights={props.rights}
          updateRights={props.updateRights}
        />
      }
    </>
  )
}

EditorRights.propTypes = {
  name: T.string.isRequired,
  contextType: T.string.isRequired,
  contextData: T.object,
  load: T.func.isRequired,
  updateRights: T.func.isRequired
}

export {
  EditorRights
}
