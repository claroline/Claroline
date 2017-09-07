import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

const FontSize = props =>
  <FormGroup
    controlId={props.controlId}
    label={trans('font_size', {}, 'theme')}
  >
    <div>
      <button type="button" className="btn btn-sm btn-default" style={{marginRight: '5px'}}>
        small
      </button>

      <button type="button" className="btn btn-sm btn-primary" style={{marginRight: '5px'}}>
        normal
      </button>

      <button type="button" className="btn btn-sm btn-default" style={{marginRight: '5px'}}>
        large
      </button>

      <button type="button" className="btn btn-sm btn-default">
        x-large
      </button>
    </div>
  </FormGroup>

FontSize.propTypes = {
  controlId: T.string.isRequired
}

export {
  FontSize
}
