import React from 'react'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'
import {TextGroup} from '#/main/core/layout/form/components/group/text-group.jsx'
import {HtmlGroup} from '#/main/core/layout/form/components/group/html-group.jsx'

const StepForm = props =>
  <fieldset>
    <TextGroup
      id={`step-${props.id}-title`}
      label={t('title')}
      value={props.title}
      onChange={text => props.onChange({title: text})}
    />

    <HtmlGroup
      id={`step-${props.id}-description`}
      label={t('description')}
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
