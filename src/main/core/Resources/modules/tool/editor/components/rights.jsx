import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import {ContentRights} from '#/main/app/content/components/rights'
import {Form} from '#/main/app/content/form/containers/form'
import {selectors} from '#/main/core/tool/editor/store'

const EditorRights = (props) =>
  <Form
    className="my-3"
    name={selectors.STORE_NAME}
    target={['apiv2_tool_configure', {
      name: props.name,
      context: props.contextType,
      contextId: props.contextId
    }]}
    onSave={(savedData) => props.refresh(props.name, savedData, props.contextType)}
    buttons={true}
  >
    {props.rights &&
      <ContentRights
        workspace={props.contextData}
        rights={props.rights}
        updateRights={props.updateRights}
      />
    }
  </Form>

EditorRights.propTypes = {

}

export {
  EditorRights
}
