import React from 'react'
import {PropTypes as T} from 'prop-types'

import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'

const HtmlGroup = props =>
  <FormGroup
    {...props}
  >
    <Textarea
      id={props.controlId}
      content={props.content}
      minRows={props.minRows}
      disabled={props.disabled}
      onChange={props.onChange}
      onClick={props.onClick}
      onSelect={props.onSelect}
      onChangeMode={props.onChangeMode}
    />
  </FormGroup>

HtmlGroup.propTypes = {
  controlId: T.string.isRequired,
  content: T.string,
  minRows: T.number,
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired,
  onSelect: T.func,
  onClick: T.func,
  onChangeMode: T.func
}

HtmlGroup.defaultProps = {
  content: '',
  minRows: 2,
  disabled: false,
  onClick: () => {},
  onSelect: () => {},
  onChangeMode: () => {}
}

export {
  HtmlGroup
}
