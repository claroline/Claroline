import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {ResourcePage} from '#/main/core/resource/containers/page'
import {HtmlText} from '#/main/core/layout/components/html-text'

const CollecticielResource = props =>
  <ResourcePage>
    <div className="alert alert-warning">
      <p>{trans('replacement_msg', {}, 'collecticiel')}</p>
      <hr/>
      <p><b>{trans('current_instruction', {}, 'collecticiel')} :</b></p>
      <br/>
      <HtmlText>{props.dropzone.instruction}</HtmlText>
    </div>
  </ResourcePage>

CollecticielResource.propTypes = {
  dropzone: T.shape({
    instruction: T.string
  }).isRequired
}

export {
  CollecticielResource
}
