import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {TextGroup} from '#/main/core/layout/form/components/group/text-group'
import {HtmlGroup} from '#/main/core/layout/form/components/group/html-group'

const StepForm = props =>
  <fieldset>
    <TextGroup
      id={`step-${props.id}-title`}
      label={trans('title')}
      value={props.title}
      onChange={text => props.onChange({title: text})}
    />

    <HtmlGroup
      id={`step-${props.id}-description`}
      label={trans('description')}
      value={props.description}
      onChange={text => props.onChange({description: text})}
    />
  </fieldset>

StepForm.propTypes = {
  id: T.string.isRequired,
  title: T.string.isRequired,
  description: T.string.isRequired,
  onChange: T.func.isRequired
}

export {
  StepForm
}
