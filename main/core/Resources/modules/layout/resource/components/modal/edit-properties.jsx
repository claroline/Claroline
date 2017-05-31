import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import set from 'lodash/set'
import isEmpty from 'lodash/isEmpty'
import cloneDeep from 'lodash/cloneDeep'

import Modal      from 'react-bootstrap/lib/Modal'
import Panel      from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'

import {formatDate}   from '#/main/core/date'
import {t}            from '#/main/core/translation'
import {t_res}        from '#/main/core/layout/resource/translation'
import {BaseModal}    from '#/main/core/layout/modal/components/base.jsx'
import {FormGroup}    from '#/main/core/layout/form/components/form-group.jsx'
import {Textarea}     from '#/main/core/layout/form/components/textarea.jsx'
import {DatePicker}   from '#/main/core/layout/form/components/date-picker.jsx'
import {validate}     from '#/main/core/layout/resource/validator'
import {closeTargets} from '#/main/core/layout/resource/enums'

export const MODAL_RESOURCE_PROPERTIES = 'MODAL_RESOURCE_PROPERTIES'

const MetaPanel = props =>
  <fieldset>
    <FormGroup
      controlId="resource-description"
      label={t_res('resource_description')}
    >
      <Textarea
        id="resource-description"
        content={props.meta.description || ''}
        onChange={description => props.updateParameter('meta.description', description)}
      />
    </FormGroup>

    <div className="checkbox">
      <label htmlFor="resource-published">
        <input
          id="resource-published"
          type="checkbox"
          checked={props.meta.published}
          onChange={() => props.updateParameter('meta.published', !props.meta.published)}
        />
        {props.meta.published ?
          t_res('resource_published') :
          t_res('resource_not_published')
        }
      </label>
    </div>

    <div className="checkbox">
      <label htmlFor="resource-portal">
        <input
          id="resource-portal"
          type="checkbox"
          checked={props.meta.portal}
          onChange={() => props.updateParameter('meta.portal', !props.meta.portal)}
        />
        {props.meta.portal ?
          t_res('resource_portal_published') :
          t_res('resource_portal_not_published')
        }
      </label>
      <p className="help-block">
        <span className="fa fa-info-circle" />
        {t_res('resource_portal_help')}
      </p>
    </div>
  </fieldset>

MetaPanel.propTypes = {
  meta: T.shape({
    description: T.string,
    published: T.bool.isRequired,
    portal: T.bool.isRequired
  }).isRequired,
  updateParameter: T.func.isRequired,
  validating: T.bool.isRequired,
  errors: T.object
}

const AccessibilityDatesPanel = props =>
  <fieldset>
    <FormGroup
      controlId="resource-accessible-from"
      label={t_res('resource_accessible_from')}
    >
      <DatePicker
        id="resource-accessible-from"
        name="resource-accessible-from"
        value={props.parameters.accessibleFrom || ''}
        onChange={date => props.updateParameter('parameters.accessibleFrom', formatDate(date))}
      />
    </FormGroup>

    <FormGroup
      controlId="resource-accessible-until"
      label={t_res('resource_accessible_until')}
    >
      <DatePicker
        id="resource-accessible-until"
        name="resource-accessible-until"
        value={props.parameters.accessibleUntil || ''}
        onChange={date => props.updateParameter('parameters.accessibleUntil', formatDate(date))}
      />
    </FormGroup>
  </fieldset>

AccessibilityDatesPanel.propTypes = {
  parameters: T.shape({
    accessibleFrom: T.string,
    accessibleUntil: T.string
  }).isRequired,
  updateParameter: T.func.isRequired,
  validating: T.bool.isRequired,
  errors: T.object
}

const DisplayPanel = props =>
  <fieldset>
    <div className="checkbox">
      <label htmlFor="resource-fullscreen">
        <input
          id="resource-fullscreen"
          type="checkbox"
          checked={props.parameters.fullscreen}
          onChange={() => props.updateParameter('parameters.fullscreen', !props.parameters.fullscreen)}
        />
        {t_res('resource_fullscreen')}
      </label>
    </div>

    <div className="checkbox">
      <label htmlFor="resource-closable">
        <input
          id="resource-closable"
          type="checkbox"
          checked={props.parameters.closable}
          onChange={() => props.updateParameter('parameters.closable', !props.parameters.closable)}
        />
        {t_res('resource_closable')}
      </label>
    </div>

    <FormGroup
      controlId="resource-close-target"
      label={t_res('resource_close_target')}
      warnOnly={!props.validating}
      error={get(props, 'errors.parameters.closeTarget')}
    >
      <select
        id="resource-close-target"
        value={props.parameters.closeTarget}
        className="form-control"
        onChange={(e) => props.updateParameter('parameters.closeTarget', e.target.value)}
      >
        {closeTargets.map(target =>
          <option key={target[0]} value={target[0]}>{t_res(target[1])}</option>
        )}
      </select>
    </FormGroup>
  </fieldset>

DisplayPanel.propTypes = {
  parameters: T.shape({
    fullscreen: T.bool.isRequired,
    closable: T.bool.isRequired,
    closeTarget: T.number.isRequired
  }).isRequired,
  updateParameter: T.func.isRequired,
  validating: T.bool.isRequired,
  errors: T.object
}

const LicensePanel = props =>
  <fieldset>
    <FormGroup
      controlId="resource-authors"
      label={t_res('resource_authors')}
    >
      <input
        id="resource-authors"
        type="text"
        className="form-control"
        value={props.meta.authors || ''}
        onChange={(e) => props.updateParameter('meta.authors', e.target.value)}
      />
    </FormGroup>

    <FormGroup
      controlId="resource-license"
      label={t_res('resource_license')}
    >
      <input
        id="resource-license"
        type="text"
        className="form-control"
        value={props.meta.license || ''}
        onChange={(e) => props.updateParameter('meta.license', e.target.value)}
      />
    </FormGroup>
  </fieldset>

LicensePanel.propTypes = {
  meta: T.shape({
    authors: T.string,
    license: T.string
  }).isRequired,
  updateParameter: T.func.isRequired,
  validating: T.bool.isRequired,
  errors: T.object
}

class EditPropertiesModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      resourceNode: Object.assign({}, props.resourceNode),
      pendingChanges: false,
      validating: false,
      errors: {}
    }

    this.save = this.save.bind(this)
  }

  /**
   * Updates a property in the resource node.
   *
   * @param {string} parameter - the path of the parameter in the node (eg. 'meta.published')
   * @param value
   */
  updateProperty(parameter, value) {
    // Update state and validate new resourceNode data
    this.setState((prevState) => {
      const newNode = cloneDeep(prevState.resourceNode)
      set(newNode, parameter, value)

      return {
        resourceNode: newNode,
        pendingChanges: true,
        validating: false,
        errors: validate(newNode)
      }
    })
  }

  /**
   * Saves the resource node updates if valid.
   */
  save() {
    const errors = validate(this.state.resourceNode)

    this.setState({
      validating: true,
      errors: errors
    })

    if (isEmpty(errors)) {
      this.props.save(this.state.resourceNode)
      this.props.fadeModal()
    }
  }

  makePanel(id, icon, title, content) {
    return (
      <Panel
        eventKey={id}
        header={
          <h5 className={classes('panel-title', {opened: id === this.state.openedPanel})}>
            <span className={classes('fa fa-fw', icon)} style={{marginRight: 10}} />
            {title}
          </h5>
        }
      >
        {content}
      </Panel>
    )
  }

  render() {
    return (
      <BaseModal
        icon="fa fa-fw fa-pencil"
        title={t_res('edit-properties')}
        className="resource-edit-properties-modal"
        {...this.props}
      >
        <Modal.Body>
          <FormGroup
            controlId="resource-name"
            label={t_res('resource_name')}
            warnOnly={!this.state.validating}
            error={get(this.state, 'errors.name')}
          >
            <input
              id="resource-name"
              type="text"
              className="form-control"
              value={this.state.resourceNode.name}
              onChange={(e) => this.updateProperty('name', e.target.value)}
            />
          </FormGroup>
        </Modal.Body>

        <PanelGroup
          accordion
          activeKey={this.state.openedPanel}
          onSelect={(activeKey) => this.setState({openedPanel: activeKey !== this.state.openedPanel ? activeKey : null})}
        >
          {this.makePanel(
            'resource-meta',
            'fa-info',
            'Information',
            <MetaPanel
              meta={this.state.resourceNode.meta}
              updateParameter={this.updateProperty.bind(this)}
              validating={this.state.validating}
              errors={this.state.errors}
            />
          )}

          {this.makePanel(
            'resource-dates',
            'fa-calendar',
            'Accessibility dates',
            <AccessibilityDatesPanel
              parameters={this.state.resourceNode.parameters}
              updateParameter={this.updateProperty.bind(this)}
              validating={this.state.validating}
              errors={this.state.errors}
            />
          )}

          {this.makePanel(
            'resource-display',
            'fa-desktop',
            'Display parameters',
            <DisplayPanel
              parameters={this.state.resourceNode.parameters}
              updateParameter={this.updateProperty.bind(this)}
              validating={this.state.validating}
              errors={this.state.errors}
            />
          )}

          {this.makePanel(
            'resource-license',
            'fa-copyright',
            'Authors & License',
            <LicensePanel
              meta={this.state.resourceNode.meta}
              updateParameter={this.updateProperty.bind(this)}
              validating={this.state.validating}
              errors={this.state.errors}
            />
          )}
        </PanelGroup>

        <button
          className="modal-btn btn btn-primary"
          disabled={!this.state.pendingChanges || (this.state.validating && !isEmpty(this.state.errors))}
          onClick={this.save}
        >
          {t('save')}
        </button>
      </BaseModal>
    )
  }
}

EditPropertiesModal.propTypes = {
  resourceNode: T.shape({
    name: T.string.isRequired,
    parameters: T.object.isRequired,
    meta: T.object.isRequired
  }),
  fadeModal: T.func.isRequired,
  save: T.func.isRequired
}

export {EditPropertiesModal}
