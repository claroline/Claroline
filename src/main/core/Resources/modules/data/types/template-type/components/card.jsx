import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {TemplateType as TemplateTypeTypes} from '#/main/core/data/types/template-type/prop-types'

const TemplateTypeCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-file-alt"
    title={trans(props.data.name, {}, 'template')}
    subtitle={trans(`${props.data.name}_desc`, {}, 'template')}
  />

TemplateTypeCard.propTypes = {
  data: T.shape(
    TemplateTypeTypes.propTypes
  ).isRequired
}

export {
  TemplateTypeCard
}
