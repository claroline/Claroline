import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Template as TemplateTypes} from '#/main/core/data/types/template/prop-types'

const TemplateCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-file-alt"
    title={props.data.name}
    subtitle={trans(props.data.type.name, {}, 'template')}
    contentText={props.data.content}
    footer={
      <span>
        {props.data.lang}
      </span>
    }
  />

TemplateCard.propTypes = {
  data: T.shape(
    TemplateTypes.propTypes
  ).isRequired
}

export {
  TemplateCard
}
