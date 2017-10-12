import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans, t} from '#/main/core/translation'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {Message} from '../../../components/message.jsx'
import {actions} from '../actions'
import {actions as clacoFormActions} from '../../../actions'
import {selectors} from '../../../selectors'
import {generateFieldKey, getFieldType} from '../../../utils'
import {select as resourceSelect} from '#/main/core/layout/resource/selectors'

class TemplateForm extends Component {
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
        if (!f.hidden) {
          const fieldKey = generateFieldKey(f.id)
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
        {this.props.canEdit ?
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
                    if (f.required && !f.hidden) {
                      return (
                        <li key={`required-${f.id}`}>
                          <b>{generateFieldKey(f.id)}</b> : {f.name} [{getFieldType(f.type).label}]
                        </li>
                      )
                    }
                  })}
                </ul>
              </div>
              {this.props.fields.filter(f => !f.required && !f.hidden).length > 0 &&
                <div>
                  <hr/>
                  <h4>{trans('optional', {}, 'clacoform')}</h4>
                  <ul>
                    {this.props.fields.map(f => {
                      if (!f.required && !f.hidden) {
                        return (
                          <li key={`optional-${f.id}`}>
                            <b>{generateFieldKey(f.id)}</b> : {f.name} [{getFieldType(f.type).label}]
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
              title="clacoform-template"
              content={this.state.template}
              onChange={value => this.updateTemplate(value)}
            />
            <CheckGroup
              checkId="use-template"
              disabled={!this.state.template}
              checked={this.state.useTemplate}
              label={trans('use_template', {}, 'clacoform')}
              onChange={checked => this.updateUseTemplate(checked)}
            />
            <div className="template-buttons">
              <button className="btn btn-primary" onClick={() => this.validateTemplate()}>
                {t('ok')}
              </button>
              <a className="btn btn-default" href="#/">
                {t('cancel')}
              </a>
            </div>
          </div> :
          <div className="alert alert-danger">
            {t('unauthorized')}
          </div>
        }
      </div>
    )
  }
}

TemplateForm.propTypes = {
  canEdit: T.bool.isRequired,
  template: T.string,
  fields: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    type: T.number.isRequired,
    required: T.bool.isRequired
  })),
  useTemplate: T.bool,
  saveTemplate: T.func.isRequired,
  updateMessage: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    canEdit: resourceSelect.editable(state),
    template: selectors.template(state),
    fields: state.fields.filter(f => f.type !== 11),
    useTemplate: selectors.useTemplate(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    saveTemplate: (template, useTemplate) => dispatch(actions.saveTemplate(template, useTemplate)),
    updateMessage: (message, status) => dispatch(clacoFormActions.updateMessage(message, status))
  }
}

const ConnectedTemplateForm = connect(mapStateToProps, mapDispatchToProps)(TemplateForm)

export {ConnectedTemplateForm as TemplateForm}