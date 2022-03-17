import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolPage} from '#/main/core/tool/containers/page'

import {TemplateType as TemplateTypeTypes} from '#/main/core/data/types/template-type/prop-types'

const TemplatePage = (props) => {
  if (isEmpty(props.templateType)) {
    return (
      <ContentLoader
        size="lg"
        description={trans('loading', {}, 'template')}
      />
    )
  }

  return (
    <ToolPage
      path={[
        {
          type: LINK_BUTTON,
          label: trans(get(props.templateType, 'type')),
          target: props.path + '/' + get(props.templateType, 'type')
        }, {
          type: LINK_BUTTON,
          label: trans(get(props.templateType, 'name'), {}, 'template'),
          target: ''
        }
      ]}
      subtitle={trans(get(props.templateType, 'name'), {}, 'template')}
      meta={{
        title: `${trans('templates', {}, 'tools')} - ${trans(get(props.templateType, 'name'), {}, 'template')}`,
        description: trans(get(props.templateType, 'name')+'_desc', {}, 'template')
      }}

      primaryAction="add"
      actions={[
        {
          name: 'add',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('add_a_template'),
          target: `${props.path}/${props.templateType.type}/${props.templateType.id}/form`,
          primary: true,
          exact: true
        }
      ]}
    >
      {props.children}
    </ToolPage>
  )
}

TemplatePage.propTypes = {
  path: T.string.isRequired,
  templateType: T.shape(
    TemplateTypeTypes.propTypes
  ),
  children: T.any
}

export {
  TemplatePage
}
