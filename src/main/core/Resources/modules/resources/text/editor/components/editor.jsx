import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Form} from '#/main/app/content/form/containers/form'
import {Tinymce} from '#/main/app/input/components/tinymce'

import {selectors} from '#/main/core/resources/text/editor/store'
import {Text as TextTypes} from '#/main/core/resources/text/prop-types'
import {EditorPlaceholders} from '#/main/core/resources/text/editor/components/placeholders'

const Editor = (props) =>
  <Form
    className="row"
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
    <Tinymce
      id={props.text.id}
      mode="full"
      value={props.value}
      onChange={(newValue) => props.updateProp('raw', newValue)}
    />
    {false &&
    <EditorPlaceholders availablePlaceholders={props.availablePlaceholders} />
    }

  </Form>

Editor.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  text: T.shape(
    TextTypes.propTypes
  ).isRequired,
  availablePlaceholders: T.arrayOf(T.string),
  updateProp: T.func.isRequired
}

export {
  Editor
}
