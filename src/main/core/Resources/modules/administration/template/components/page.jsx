import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {PageFull} from '#/main/app/page/components/full'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'

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
    <PageFull
      showBreadcrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
      path={[].concat(getToolBreadcrumb('templates', props.currentContext.type, props.currentContext.data), [
        {
          type: LINK_BUTTON,
          label: trans(get(props.templateType, 'type')),
          target: props.path + '/' + get(props.templateType, 'type')
        }, {
          type: LINK_BUTTON,
          label: trans(get(props.templateType, 'name'), {}, 'template'),
          target: ''
        }
      ])}
      title={trans('templates', {}, 'tools')}
      subtitle={trans(get(props.templateType, 'name'), {}, 'template')}
      meta={{
        title: `${trans('templates', {}, 'tools')} - ${trans(get(props.templateType, 'name'), {}, 'template')}`,
        description: trans(get(props.templateType, 'name')+'_desc', {}, 'template')
      }}

      toolbar="add | fullscreen more"
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
    </PageFull>
  )
}

TemplatePage.propTypes = {
  path: T.string.isRequired,
  currentContext: T.shape({
    type: T.oneOf(['administration', 'desktop', 'workspace']),
    data: T.object
  }).isRequired,
  templateType: T.shape(
    TemplateTypeTypes.propTypes
  ),
  children: T.any
}

export {
  TemplatePage
}
