import React from 'react'
import {PropTypes as T} from 'prop-types'

import InputGroup from 'react-bootstrap/lib/InputGroup'
import DropdownButton from 'react-bootstrap/lib/DropdownButton'
import MenuItem from 'react-bootstrap/lib/MenuItem'

import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

const Size = props =>
  <FormGroup
    controlId={props.controlId}
    label={props.label}
  >
    <div style={{maxWidth: '159px'}}>
      <InputGroup>
        <input
          type="number"
          className="form-control"
          value={props.value}
          onChange={(e) => props.onChange(e.target.value)}
        />

        <InputGroup.Button>
          <DropdownButton
            id={`${props.controlId}-dropdown`}
            title="px"
            noCaret={true}
            pullRight={true}
          >
            <MenuItem key="1" className="text-right">px</MenuItem>
            <MenuItem key="2" className="text-right">%</MenuItem>
            <MenuItem key="3" className="text-right">em</MenuItem>
            <MenuItem key="4" className="text-right">rem</MenuItem>
          </DropdownButton>
        </InputGroup.Button>
      </InputGroup>
    </div>
  </FormGroup>

Size.propTypes = {
  controlId: T.string.isRequired,
  label: T.string.isRequired,
  value: T.number,
  onChange: T.func.isRequired
}

Size.defaultProps = {
  value: 0
}

export {
  Size
}
