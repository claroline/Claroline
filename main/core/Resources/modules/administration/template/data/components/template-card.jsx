import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DataCard} from '#/main/app/content/card/components/data'

import {Template as TemplateType} from '#/main/core/administration/template/prop-types'

const TemplateCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-file-alt"
    title={props.data.name}
    subtitle={props.data.type.name}
    contentText={props.data.content}
    footer={
      <span>
        {props.data.lang}
      </span>
    }
  />

TemplateCard.propTypes = {
  data: T.shape(TemplateType.propTypes).isRequired
}

export {
  TemplateCard
}
