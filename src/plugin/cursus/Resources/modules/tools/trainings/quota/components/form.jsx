import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Quota as QuotaTypes} from '#/plugin/cursus/prop-types'
import {QuotaPage} from '#/plugin/cursus/quota/components/page'
import {QuotaForm as QuotaFormContainer} from '#/plugin/cursus/quota/containers/form'

import {selectors} from '#/plugin/cursus/tools/trainings/quota/store'

const QuotaForm = (props) => {
  if (!props.isNew) {
    return (
      <QuotaPage
        basePath={props.path}
        path={[
          {
            type: LINK_BUTTON,
            label: trans('quotas', {}, 'cursus'),
            target: props.path
          }, {
            label: props.quota.id
          }
        ]}
        currentContext={props.currentContext}
        quota={props.quota}
      >
        <QuotaFormContainer
          path={props.path}
          name={selectors.FORM_NAME}
        />
      </QuotaPage>
    )
  }

  return (
    <ToolPage
      path={[
        {
          type: LINK_BUTTON,
          label: trans('quota', {}, 'cursus'),
          target: props.path
        }, {
          label: trans('new_quota', {}, 'cursus')
        }
      ]}
      title={trans('trainings', {}, 'tools')}
      subtitle={trans('new_quota', {}, 'cursus')}
      primaryAction="add"
      actions={[
        {
          name: 'add',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('add_quota', {}, 'cursus'),
          target: `${props.path}/new`,
          group: trans('management'),
          primary: true
        }
      ]}
    >
      <QuotaFormContainer
        path={props.path}
        name={selectors.FORM_NAME}
      />
    </ToolPage>
  )
}

QuotaForm.propTypes = {
  path: T.string.isRequired,
  currentContext: T.shape({
    type: T.oneOf(['desktop']),
    data: T.object
  }).isRequired,
  isNew: T.bool.isRequired,
  quota: T.shape(
    QuotaTypes.propTypes
  )
}

export {
  QuotaForm
}