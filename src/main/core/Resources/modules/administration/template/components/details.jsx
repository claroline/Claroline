import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router/components/routes'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {TemplateType as TemplateTypeTypes} from '#/main/core/data/types/template-type/prop-types'
import {TemplatePage} from '#/main/core/administration/template/containers/page'
import {TemplateForm} from '#/main/core/administration/template/containers/form'

const TemplateDetails = (props) =>
  <TemplatePage
    templateType={props.templateType}
  >
    {!isEmpty(props.templateType) &&
      <Fragment>
        <div className="row" style={{marginTop: 20}}>
          <div className="col-md-3">
            <Vertical
              basePath={props.path + '/' + props.templateType.type + '/' + props.templateType.id}
              tabs={props.templates.map(template => ({
                id: template.id,
                title: (
                  <Fragment>
                    {template.name}
                    {template.name === props.templateType.defaultTemplate &&
                      <small>
                        &nbsp;({trans('default')})
                      </small>
                    }
                  </Fragment>
                ),
                path: `/${template.id}`,
                actions: [
                  {
                    name: 'delete',
                    type: CALLBACK_BUTTON,
                    icon: 'fa fa-fw fa-trash-o',
                    label: trans('delete', {}, 'actions'),
                    displayed: !template.system,
                    callback: () => props.deleteTemplate(props.templateType.id, template.id),
                    confirm: {
                      title: trans('template_delete_confirm', {}, 'template'),
                      message: trans('template_delete_confirm_message', {}, 'template')
                    },
                    dangerous: true
                  }
                ]
              }))}
            />
          </div>

          <div className="col-md-9">
            <Routes
              path={props.path + '/' + props.templateType.type + '/' + props.templateType.id}
              redirect={[
                {from: '/', exact: true, to: '/'+props.templates[0].id, disabled: isEmpty(props.templates)}
              ]}
              routes={[
                {
                  path: '/:id',
                  component: TemplateForm,
                  onEnter: (params) => props.openForm(props.templateType, props.defaultLocale, params.id || null),
                  onLeave: () => props.resetForm(props.templateType, props.defaultLocale)
                }
              ]}
            />
          </div>
        </div>
      </Fragment>
    }
  </TemplatePage>

TemplateDetails.propTypes = {
  path: T.string.isRequired,
  templateType: T.shape(
    TemplateTypeTypes.propTypes
  ),
  templates: T.array,

  defaultLocale: T.string.isRequired,
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired,
  deleteTemplate: T.func.isRequired
}

export {
  TemplateDetails
}
