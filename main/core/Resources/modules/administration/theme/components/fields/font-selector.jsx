import React from 'react'
import {PropTypes as T} from 'prop-types'

import InputGroup from 'react-bootstrap/lib/InputGroup'
import DropdownButton from 'react-bootstrap/lib/DropdownButton'
import MenuItem from 'react-bootstrap/lib/MenuItem'

import {trans} from '#/main/core/translation'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

const FontSelector = props =>
  <FormGroup
    controlId={props.controlId}
    label={trans('font', {}, 'theme')}
  >
    <div>
      <InputGroup>
        <input type="text" className="form-control" value={props.value} onChange={(e) => props.onChange(e.target.value)}/>
        <InputGroup.Button>
          <DropdownButton
            id={`${props.controlId}-dropdown`}
            title={<span className="fa fa-fw fa-caret-down" />}
            noCaret={true}
            pullRight={true}
          >
            <MenuItem key="1">Arial</MenuItem>
            <MenuItem key="2">Times new Roman</MenuItem>
          </DropdownButton>

          <button type="button" className="btn btn-default">
            <span className="fa fa-fw fa-download" />
            <span className="sr-only">upload font</span>
          </button>
        </InputGroup.Button>
      </InputGroup>
    </div>
  </FormGroup>

FontSelector.propTypes = {
  controlId: T.string.isRequired,
  value: T.string,
  onChange: T.func.isRequired
}

FontSelector.defaultProps = {
  value: ''
}

export {
  FontSelector
}
