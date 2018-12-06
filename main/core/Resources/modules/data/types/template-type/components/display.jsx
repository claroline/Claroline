import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {TemplateType as TemplateTypeType} from '#/main/core/administration/template/prop-types'
import {TemplateTypeCard} from '#/main/core/administration/template/data/components/template-type-card'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

const TemplateTypeDisplay = (props) => props.data ?
  <TemplateTypeCard
    data={props.data}
    size="sm"
    orientation="col"
  /> :
  <EmptyPlaceholder
    size="lg"
    icon="fa fa-file-alt"
    title={trans('no_template_type', {}, 'template')}
  />

TemplateTypeDisplay.propTypes = {
  data: T.shape(TemplateTypeType.propTypes)
}

export {
  TemplateTypeDisplay
}
