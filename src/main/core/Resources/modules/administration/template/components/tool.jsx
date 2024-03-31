import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {TemplateList} from '#/main/core/administration/template/containers/list'
import {TemplateDetails} from '#/main/core/administration/template/containers/details'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const TemplateTool = (props) =>
  <Tool
    {...props}
    redirect={[
      {from: '/', exact: true, to: '/email'}
    ]}
    menu={[
      {
        name: 'email',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-at',
        label: trans('email'),
        target: `${props.path}/email`
      }, {
        name: 'pdf',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-file-pdf',
        label: trans('pdf'),
        target: `${props.path}/pdf`
      }, {
        name: 'other',
        type: LINK_BUTTON,
        label: trans('other'),
        target: `${props.path}/other`
      }, {
        name: 'sms',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-sms',
        label: trans('sms'),
        target: `${props.path}/sms`,
        displayed: false
      }
    ]}
    pages={[
      {
        path: '/:type',
        exact: true,
        onEnter: () => props.invalidateList(),
        render: (routerProps) => (
          <TemplateList
            type={routerProps.match.params.type}
          />
        )
      }, {
        path: '/:type/:id',
        onEnter: (params) => props.open(params.id || null),
        component: TemplateDetails
      }
    ]}
  />

TemplateTool.propTypes = {
  path: T.string.isRequired,
  open: T.func.isRequired,
  invalidateList: T.func.isRequired
}

export {
  TemplateTool
}
