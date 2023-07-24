import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON} from '#/main/app/buttons'
import {Form} from '#/main/app/content/form/containers/form'
import {TinymceEditor} from '#/main/app/input/tinymce/components/editor'

import {selectors} from '#/main/core/resources/text/editor/store'
import {Text as TextTypes} from '#/main/core/resources/text/prop-types'

const Editor = (props) =>
  <div className="row flex-fill text-resource-editor">
    <Form
      flush={true}
      className="g-0"
      name={selectors.FORM_NAME}
      target={['apiv2_resource_text_update', {id: props.text.id}]}
      buttons={true}
      cancel={{
        type: LINK_BUTTON,
        target: props.path,
        exact: true
      }}
      lock={{
        id: props.text.id,
        className: 'Claroline\\CoreBundle\\Entity\\Resource\\Text',
        autoUnlock: true
      }}
    >
      <TinymceEditor
        id={props.text.id}
        mode="full"
        value={props.text.raw}
        onChange={(newValue) => props.updateProp('raw', newValue)}
        config={{
          plugins: ['placeholders'],
          placeholders: props.availablePlaceholders
        }}
      />
    </Form>
  </div>

Editor.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  text: T.shape(
    TextTypes.propTypes
  ).isRequired,
  originalText: T.shape(
    TextTypes.propTypes
  ).isRequired,
  availablePlaceholders: T.arrayOf(T.string),
  updateProp: T.func.isRequired
}

export {
  Editor
}
