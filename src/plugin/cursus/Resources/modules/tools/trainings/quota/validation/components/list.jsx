import React from 'react'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ValidationList as ValidationDataList} from '#/plugin/cursus/quota/validation/components/list'
import {selectors} from '#/plugin/cursus/tools/trainings/quota/store/selectors'

const ValidationList = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('validation', {}, 'cursus'),
      target: props.path
    }]}
    subtitle={trans('validation', {}, 'cursus')}
    primaryAction="export"
    actions={[
      {
        name: 'export',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export_validation', {}, 'cursus'),
        target: `${props.path}/export`,
        group: trans('management'),
        primary: true
      }
    ]}
  >
    <ValidationDataList
      name={selectors.LIST_NAME}
      path={props.path}
    />
  </ToolPage>

ValidationList.propTypes = {
  path: T.string.isRequired
}

export {
  ValidationList
}
