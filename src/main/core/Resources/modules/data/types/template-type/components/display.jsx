import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {TemplateType as TemplateTypeTypes} from '#/main/core/data/types/template-type/prop-types'
import {TemplateTypeCard} from '#/main/core/data/types/template-type/components/card'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

const TemplateTypeDisplay = (props) => props.data ?
  <TemplateTypeCard
    data={props.data}
    size="xs"
  /> :
  <ContentPlaceholder
    icon="fa fa-file-alt"
    title={trans('no_template_type', {}, 'template')}
  />

TemplateTypeDisplay.propTypes = {
  data: T.shape(
    TemplateTypeTypes.propTypes
  )
}

export {
  TemplateTypeDisplay
}
