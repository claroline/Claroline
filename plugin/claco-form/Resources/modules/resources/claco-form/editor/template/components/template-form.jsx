import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'

import {select} from '#/plugin/claco-form/resources/claco-form/selectors'
import {Field as FieldType} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {generateFieldKey} from '#/plugin/claco-form/resources/claco-form/utils'
import {actions as clacoFormActions} from '#/plugin/claco-form/resources/claco-form/actions'
import {actions} from '#/plugin/claco-form/resources/claco-form/editor/template/actions'
import {Message} from '#/plugin/claco-form/resources/claco-form/components/message.jsx'

class TemplateFormComponent extends Component {
  constructor(props) {
    super(props)
    this.state = {
      template: props.template || '',
      useTemplate: props.useTemplate !== undefined ? props.useTemplate : false
    }
  }

  updateTemplate(value) {
    const useTemplate = value ? this.state.useTemplate : false
    this.setState({template: value, useTemplate: useTemplate})
  }

  updateUseTemplate(value) {
    this.setState({useTemplate: value})
  }

  validateTemplate() {
    const requiredErrors = []
    const duplicatedErrors = []

    if (this.state.template) {
      const titleRegex = new RegExp('%clacoform_entry_title%', 'g')
      const titleMatches = this.state.template.match(titleRegex)

      if (titleMatches === null) {
        requiredErrors.push('%clacoform_entry_title%')
      } else if (titleMatches.length > 1) {
        duplicatedErrors.push('%clacoform_entry_title%')
      }
      this.props.fields.forEach(f => {
        if (!f.restrictions.hidden) {
          const fieldKey = generateFieldKey(f.autoId)
          const regex = new RegExp(fieldKey, 'g')
          const matches = this.state.template.match(regex)

          if (f.required && matches === null) {
            requiredErrors.push(fieldKey)
          } else if (matches !== null && matches.length > 1) {
            duplicatedErrors.push(fieldKey)
          }
        }
      })
    }
    if (requiredErrors.length > 0 || duplicatedErrors.length > 0) {
      this.generateErrorMessage(requiredErrors, duplicatedErrors)
    } else {
      this.props.saveTemplate(this.state.template, this.state.useTemplate)
    }
  }

  generateErrorMessage(requiredErrors, duplicatedErrors) {
    let message = '<div className="alert alert-danger">'

    if (requiredErrors.length > 0) {
      message += `
        ${trans('template_missing_mandatory_variables_message', {}, 'clacoform')}
        <ul>
      `
      requiredErrors.forEach(e => {
        message += `
          <li>${e}</li>
        `
      })
      message += '</ul>'
    }
    if (duplicatedErrors.length > 0) {
      message += `
        ${trans('template_duplicated_variables_message', {}, 'clacoform')}
        <ul>
      `
      duplicatedErrors.forEach(e => {
        message += `
          <li>${e}</li>
        `
      })
      message += '</ul>'
    }
    message += '</div>'
    this.props.updateMessage(message, 'danger')
  }

  render() {
    return (
      <div>
        <h2>{trans('template_management', {}, 'clacoform')}</h2>
        <br/>
        <div>
          <div className="alert alert-warning">
            <button type="button" className="close" data-dismiss="alert" aria-hidden="true">
              &times;
            </button>
            {trans('template_variables_message', {}, 'clacoform')}
            <hr/>
            <div>
              <h4>
                {trans('mandatory', {}, 'clacoform')}
                &nbsp;
                <small>({trans('template_mandatory_variables_message', {}, 'clacoform')})</small>
              </h4>
              <ul>
                <li>
                  <b>%clacoform_entry_title%</b> : {trans('entry_title_info', {}, 'clacoform')}
                </li>
                {this.props.fields.map(f => {
                  if (f.required && !f.restrictions.hidden) {
                    return (
                      <li key={`required-${f.autoId}`}>
                        <b>{generateFieldKey(f.autoId)}</b> : {f.name} [{trans(f.type)}]
                      </li>
                    )
                  }
                })}
              </ul>
            </div>
            {this.props.fields.filter(f => !f.required && !f.restrictions.hidden).length > 0 &&
              <div>
                <hr/>
                <h4>{trans('optional', {}, 'clacoform')}</h4>
                <ul>
                  {this.props.fields.map(f => {
                    if (!f.required && !f.restrictions.hidden) {
                      return (
                        <li key={`optional-${f.autoId}`}>
                          <b>{generateFieldKey(f.autoId)}</b> : {f.name} [{trans(f.type)}]
                        </li>
                      )
                    }
                  })}
                </ul>
              </div>
            }
          </div>
          <Message/>
          <Textarea
            id="clacoform-template"
            value={this.state.template}
            onChange={value => this.updateTemplate(value)}
          />
          <CheckGroup
            id="use-template"
            disabled={!this.state.template}
            value={this.state.useTemplate}
            label={trans('use_template', {}, 'clacoform')}
            onChange={checked => this.updateUseTemplate(checked)}
          />
          <div className="template-buttons">
            <button className="btn btn-primary" onClick={() => this.validateTemplate()}>
              {trans('ok')}
            </button>
            <a className="btn btn-default" href="#/">
              {trans('cancel')}
            </a>
          </div>
        </div>
      </div>
    )
  }
}

TemplateFormComponent.propTypes = {
  template: T.string,
  fields: T.arrayOf(T.shape(FieldType.propTypes)),
  useTemplate: T.bool,
  saveTemplate: T.func.isRequired,
  updateMessage: T.func.isRequired
}

const TemplateForm = connect(
  (state) => ({
    template: select.template(state),
    fields: select.fields(state).filter(f => f.type !== 11),
    useTemplate: select.useTemplate(state)
  }),
  (dispatch) => ({
    saveTemplate(template, useTemplate) {
      dispatch(actions.saveTemplate(template, useTemplate))
    },
    updateMessage(message, status) {
      dispatch(clacoFormActions.updateMessage(message, status))
    }
  })
)(TemplateFormComponent)

export {
  TemplateForm
}